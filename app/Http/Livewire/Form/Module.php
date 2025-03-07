<?php

namespace Modules\WebsiteBase\app\Http\Livewire\Form;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Modules\Form\app\Services\FormService;
use Modules\SystemBase\app\Http\Livewire\Form\Base\ModuleCoreConfigBase;
use Modules\SystemBase\app\Models\JsonViewResponse;
use Modules\SystemBase\app\Services\ModuleService;
use Modules\SystemBase\app\Services\SystemService;
use Modules\WebsiteBase\app\Http\Livewire\Form\Base\WebsiteNativeBase;
use Modules\WebsiteBase\app\Models\CoreConfig as CoreConfigModel;
use Modules\WebsiteBase\app\Services\CoreConfigService;
use Modules\WebsiteBase\app\Services\WebsiteBaseFormService;

class Module extends WebsiteNativeBase
{
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
    public function initDataSource(mixed $id = null): JsonResource
    {
        if ($this->getDataSource()) {
            return $this->getDataSource();
        }

        /** @var SystemService $systemService */
        $systemService = app('system_base');

        if ($id) {
            // store id by form store id, default from settings
            $storeId = data_get($this->liveCommands, 'controls_store_id', $systemService::selectValueNoChoice);
            // if first time, use default store (which is the current store)
            if ($storeId === $systemService::selectValueNoChoice) {
                $storeId = (int) data_get($this->objectInstanceDefaultValues, 'core_config.store_id');
            }
            // any invalid values = back to null store ...
            $storeId = (int) $storeId;
            if ($storeId < 1) {
                $storeId = null;
            }

            /** @var ModuleService $moduleService */
            $moduleService = app('system_base_module');
            $moduleList = $moduleService->getItemInfoList(false);
            foreach ($moduleList as $module) {
                if ($id == $module['name']) {
                    $moduleSnakeName = $module['snake_name'];

                    //Log::debug(print_r($module, true));
                    $this->moduleDescription = data_get($module, 'module_json.description');

                    $coreConfig = app('website_base_config');
                    // config is sorted by position and path
                    $config = $coreConfig->getConfigModuleTree($storeId, $moduleSnakeName);

                    data_set($module, 'core_config.store_id', $storeId);

                    // set jsonResource using config tree
                    app('system_base')->runThroughArray($config,
                        function (string $key, mixed $value, string $currentRoot, int $currentDeep) use (&$module, $moduleSnakeName) {
                            $configPath = ($currentRoot ? $currentRoot.'.' : '').$key;
                            $name = $this->getConfigElementName($configPath, $moduleSnakeName);
                            data_set($module, $name, $value);
                        });

                    //
                    $this->setDataSource(new JsonResource($module));

                    // prepare module specific form data if exists ...
                    if ($moduleConfigFormClass = $systemService->findModuleClass('ModuleCoreConfig', 'livewire-forms', false, $module['studly_name'])) {
                        $this->moduleConfigFormClass = new $moduleConfigFormClass();
                        // extend data for extra tab pages for the specific module
                        $this->moduleConfigFormClass->extendDataSource($this->getDataSource());
                    }

                    break;
                }
            }
        }

        return $this->getDataSource();
    }

    /**
     * @return array
     */
    public function makeObjectInstanceDefaultValues(): array
    {
        return app('system_base')->arrayMergeRecursiveDistinct(parent::makeObjectInstanceDefaultValues(), []);
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
        if ($this->getDataSource()) {
            $moduleSnake = data_get($this->getDataSource(), 'snake_name');
            $moduleStudly = data_get($this->getDataSource(), 'studly_name');
            $tabSettingsElements = $this->getTabCoreConfig($moduleSnake, $moduleStudly);
        }

        $formConfig = [
            ... $parentFormData,
            'title'        => $this->makeFormTitle($this->getDataSource(), 'name'),
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
        /** @var FormService $formService */
        $formService = app(FormService::class);
        /** @var WebsiteBaseFormService $websiteBaseFormService */
        $websiteBaseFormService = app(WebsiteBaseFormService::class);

        $e = $formService->getFormElement('store', [
            'livewire'          => 'liveCommands',
            'livewire_live'     => true,
            'livewire_debounce' => 300,
            'css_group'         => 'col-12',
        ]);

        $result = [];

        $storeId = data_get($this->getDataSource(), 'core_config.store_id');

        // Preload collection of configs used by module.
        // No sort needed, config itself is sorted we run through.
        // @todo: maybe inaccurate row for label and description in this way ...
        $coreConfigModelCollection = CoreConfigModel::with([])->where('module', $moduleSnakeName)->where(function ($b) use ($storeId) {
            $b->where('store_id', $storeId);
            $b->orWhereNull('store_id');
        })->get();

        $coreDefaultConfigModelCollection = CoreConfigModel::with([])->where('module', $moduleSnakeName)->orWhereNull('store_id')->get();

        // get prepared config by getJsonResource()
        // config is sorted by position, path
        $config = data_get($this->getDataSource(), 'core_config.module.'.$moduleSnakeName, []);
        $configElementCount = 0;
        $prevCount = 0;
        app('system_base')->runThroughArray($config, function (string $key, mixed $value, string $currentRoot, int $currentDeep) use (
            $formService,
            $moduleSnakeName,
            $coreConfigModelCollection,
            $coreDefaultConfigModelCollection,
            &$result,
            &$configElementCount,
            &$config,
            &$prevCount
        ) {

            $configPath = ($currentRoot ? $currentRoot.'.' : '').$key;
            $name = $this->getConfigElementName($configPath, $moduleSnakeName);
            if ($c = $coreConfigModelCollection->where('path', $configPath)->first()) {

                // layout/design: add new row?
                if (data_get($c, 'options.form.new_group', false)) {
                    $index = uuid_create();
                    $result['__'.$index] = [
                        'html_element' => 'hr',
                        'css_group'    => 'col-12',
                    ];
                }

                // layout/design: use full row?
                $newRow = data_get($c, 'options.form.full_row', false);

                // prepare the form element
                $result[$name] = [
                    'html_element' => $c->form_input ?? 'text',
                    'label'        => __($c->label),
                    'description'  => __($c->description).'<br />'.'<span class="small text-primary">'.__($c->path).'</span>',
                    'validator'    => [
                        'nullable',
                        //'bool',
                    ],
                    'css_group'    => 'col-12 col-md-6'.($newRow ? ' col-md-12 col-lg-12 ' : ' ').($c->css_classes ?? ''),
                    //'default'      => $c->value, // default by this store
                    'default'      => $coreDefaultConfigModelCollection->where('path', $configPath)->first()?->value ?? null,
                ];

                // form element data
                $result[$name] = $formService->getFormElement($c->path, $result[$name]);

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
        return 'core_config.module.'.($moduleSnakeName ?? '_').'.'.$path;
    }

    /**
     * @return void
     */
    protected function initLiveCommands(): void
    {
        //parent::initLiveCommands();

        $this->addStoreCommand();

        $this->addReloadCommand();
    }

    /**
     * Called by save() or other high level calls.
     *
     * @return JsonViewResponse
     */
    protected function saveFormData(): JsonViewResponse
    {
        $jsonResponse = new JsonViewResponse();
        if ($validatedData = $this->validateForm()) {

            $storeId = (int) data_get($this->liveCommands, 'controls_store_id');

            /** @var CoreConfigService $configService */
            $configService = app('website_base_config');

            $configUpdateCount = 0;
            $allModulesConfigData = data_get($validatedData, 'core_config.module', []);
            foreach ($allModulesConfigData as $moduleSnakeName => $moduleConfigData) { // there should be only one
                if ($storeId < 1) {
                    $storeId = null;
                }

                $r = Arr::dot($moduleConfigData);
                foreach ($r as $path => $value) {

                    if ($configService->save($path, $value, $storeId, $moduleSnakeName)) {
                        $configUpdateCount++;
                    }

                }
            }

            $this->addSuccessMessage('Saved '.$configUpdateCount.' core settings.');

            return $jsonResponse;

        } else {
            $jsonResponse->setErrorMessage('Unable to load data or validation error.');

            return $jsonResponse;
        }

    }


}
