<?php

namespace Modules\WebsiteBase\app\Forms;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Form\app\Forms\Base\NativeObjectBase;
use Modules\SystemBase\app\Forms\Base\ModuleCoreConfigBase;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Models\CoreConfig as CoreConfigModel;

class Module extends NativeObjectBase
{
    /**
     * @var array|string[]
     */
    public array $liveUpdate = [
        'config' => [
            'store_id' => 2,
        ],
    ];

    /**
     * Relations commonly built in with(...)
     * * Also used for:
     * * - blacklist for properties to clean up the object if needed
     * * - onAfterUpdateItem() to sync relations
     *
     * @var array[]
     */
    protected array $objectRelations = [];

    /**
     * Singular
     *
     * @var string
     */
    protected string $objectFrontendLabel = 'Module';

    /**
     * Plural
     *
     * @var string
     */
    protected string $objectsFrontendLabel = 'Modules';

    /**
     * @var ModuleCoreConfigBase|null
     */
    protected ?ModuleCoreConfigBase $moduleConfigFormClass = null;

    /**
     * @var string
     */
    protected string $moduleDescription = '';

    /**
     * @param  mixed|null  $id
     *
     * @return JsonResource
     */
    public function getJsonResource(mixed $id = null): JsonResource
    {
        if ($this->jsonResource) {
            return $this->jsonResource;
        }

        if ($id) {
            /** @var SystemService $sys */
            $sys = app('system_base');
            // store id by form store id, default from settings
            $storeId = (int)data_get($this->liveUpdate, 'config.store_id', app('website_base_settings')->getStore()->getKey());
            /** @var ModuleService $moduleService */
            $moduleService = app('system_base_module');
            $moduleList = $moduleService->getItemInfoList(false);
            foreach ($moduleList as $module) {
                if ($id == $module['name']) {
                    $moduleSnakeName = $module['snake_name'];

                    //Log::debug(print_r($module, true));
                    $this->moduleDescription = data_get($module, 'module_json.description');

                    // Preload collection of configs used by module.
                    // No sort needed, config itself is sorted we run through.
                    $coreConfigModelCollection = CoreConfigModel::with([])->where('module', $moduleSnakeName)->where('store_id', $storeId)->get();
                    $coreConfig = app('website_base_config');
                    // config is sorted by position and path
                    $config = $coreConfig->getConfigTree($storeId);

                    data_set($module, 'config.store_id', $storeId);

                    // set jsonResource using config tree
                    app('system_base')->runThroughArray($config,
                        function (string $key, mixed $value, string $currentRoot, int $currentDeep) use (&$module, $coreConfigModelCollection, $moduleSnakeName) {
                            $configPath = ($currentRoot ? $currentRoot.'.' : '').$key;
                            if ($c = $coreConfigModelCollection->where('path', $configPath)->first()) {
                                $name = $this->getConfigElementName($configPath, $moduleSnakeName);
                                data_set($module, $name, $value);
                            }
                        });

                    //
                    $this->jsonResource = new JsonResource($module);

                    // prepare module specific form data if exists ...
                    if ($moduleConfigFormClass = $sys->findModuleClass('ModuleCoreConfig', 'model-forms', false, $module['studly_name'])) {
                        $this->moduleConfigFormClass = new $moduleConfigFormClass();
                        $this->moduleConfigFormClass->extendJsonResource($this->jsonResource);
                    }

                    break;
                }
            }
        }

        return $this->jsonResource;
    }

    /**
     *
     * @return array
     */
    public function getFormElements(): array
    {
        $parentFormData = parent::getFormElements();

        $defaultSettings = $this->getDefaultFormSettingsByPermission();

        $tabSettingsElements = [];
        //@todo: rename all modules paths in core config to 'module.system-base.' smth like that ...
        if ($this->jsonResource) {
            $moduleSnake = data_get($this->jsonResource, 'snake_name');
            $moduleStudly = data_get($this->jsonResource, 'studly_name');
            $tabSettingsElements = $this->getTabCoreConfig($moduleSnake, $moduleStudly);
        }

        //Log::debug(print_r($this->jsonResource, true));

        $formConfig = [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->jsonResource, 'name'),
            'description'  => __($this->moduleDescription),
            'tab_controls' => [
                'base_item' => [
                    'disabled'  => $defaultSettings['disabled'],
                    'tab_pages' => [
                        [
                            'tab'     => [
                                'label' => __('Common'),
                            ],
                            'content' => [
                                'form_elements' => [
                                    'is_installed' => [
                                        'html_element' => 'switch',
                                        'label'        => __('Installed'),
                                        'description'  => __('Module is installed or not.'),
                                        'disabled'     => true,
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'is_enabled'   => [
                                        'html_element' => 'switch',
                                        'label'        => __('Enabled'),
                                        'description'  => __('Enable/Disable this module.'),
                                        'disabled'     => true,
                                        'validator'    => [
                                            'nullable',
                                            'bool',
                                        ],
                                        'css_group'    => 'col-12 col-md-6',
                                    ],
                                    'name'         => [
                                        'html_element' => 'text',
                                        'label'        => __('Name'),
                                        'description'  => __('The name of the module.'),
                                        'disabled'     => true,
                                        'validator'    => [
                                            'nullable',
                                            'string',
                                            'Max:255',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                    'priority'     => [
                                        'html_element' => 'number_int',
                                        'label'        => __('Priority'),
                                        'description'  => __('Priority'),
                                        'disabled'     => true,
                                        'validator'    => [
                                            'nullable',
                                            'integer',
                                        ],
                                        'css_group'    => 'col-12 col-lg-6',
                                    ],
                                ],
                            ],
                        ],
                        $tabSettingsElements,
                    ],
                ],
            ],
        ];

        if ($this->moduleConfigFormClass) {
            if ($extend = $this->moduleConfigFormClass->getTabPages()) {
                $tabPages = data_get($formConfig, 'tab_controls.base_item.tab_pages');
                $tabPages = array_merge($tabPages, $extend);
                data_set($formConfig, 'tab_controls.base_item.tab_pages', $tabPages);
            }
        }

        return $formConfig;
    }

    /**
     * @param  string|null  $moduleSnakeName
     * @param  string|null  $moduleStudlyName
     * @param  string|null  $configPathPattern
     *
     * @return array
     */
    protected function getTabCoreConfig(?string $moduleSnakeName = null, ?string $moduleStudlyName = null, ?string $configPathPattern = null): array
    {

        $result = [
            'config.store_id'  => [
                'html_element'      => 'select_store',
                'livewire_live'     => true,
                'livewire_debounce' => 300,
                'label'             => __('Choose store.'),
                'description'       => __('Choose store.'),
                'validator'         => [
                    'nullable',
                    'integer',
                ],
                'css_group'         => 'col-12',
            ],
            '__'.uuid_create() => [
                'html_element' => 'hr',
                'css_group'    => 'col-12',
            ],
        ];
        $storeId = data_get($this->jsonResource, 'config.store_id');

        // Preload collection of configs used by module.
        // No sort needed, config itself is sorted we run through.
        $coreConfigModelCollection = CoreConfigModel::with([])->where('module', $moduleSnakeName)->where('store_id', $storeId)->get();

        // get prepared config by getJsonResource()
        // config is sorted by position, path
        $config = data_get($this->jsonResource, 'config.module.'.$moduleSnakeName, []);
        $configElementCount = 0;
        $prevCount = 0;
        app('system_base')->runThroughArray($config,
            function (string $key, mixed $value, string $currentRoot, int $currentDeep) use ($moduleSnakeName, $coreConfigModelCollection, &$result, &$configElementCount, &$config, &$prevCount) {

                $configPath = ($currentRoot ? $currentRoot.'.' : '').$key;
                $name = $this->getConfigElementName($configPath, $moduleSnakeName);
                if ($c = $coreConfigModelCollection->where('path', $configPath)->first()) {
                    if (data_get($c, 'options.form.new_group', false)) {
                        $index = uuid_create();
                        $result['__'.$index] = [
                            'html_element' => 'hr',
                            'css_group'    => 'col-12',
                        ];
                    }
                    $newRow = data_get($c, 'options.form.full_row', false);
                    $result[$name] = [
                        'html_element' => $c->form_input ?? 'text',
                        'label'        => __($c->label),
                        'description'  => __($c->description).'<br />'.'<span class="small text-primary">'.__($c->path).'</span>',
                        'validator'    => [
                            'nullable',
                            //'bool',
                        ],
                        'css_group'    => 'col-12 col-md-6'.($newRow ? ' col-md-12 col-lg-12 ' : ' ').($c->css_classes ?? ''),
                    ];
                    $configElementCount++;
                    $prevCount++;
                }
            }, callbackEveryNode: function (string $key, mixed $value, string $currentRoot, int $currentDeep) use (&$result) {
            });

        $description = '';
        if (!$configElementCount) {
            $description = __('No core configuration found for ":name".', ['name' => $moduleSnakeName]);
        }

        return [
            'tab'     => [
                'label' => __('Core Config ":name"', ['name' => $moduleStudlyName]),
            ],
            'content' => [
                'description'   => $description,
                'form_elements' => $result,
            ],

        ];
    }

    /**
     * @param  string       $path
     * @param  string|null  $moduleSnakeName
     *
     * @return string
     */
    protected function getConfigElementName(string $path, ?string $moduleSnakeName = null): string
    {
        return 'config.module.'.($moduleSnakeName ?? '_').'.'.$path;
    }


}