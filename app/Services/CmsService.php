<?php

namespace Modules\WebsiteBase\app\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Modules\Acl\app\Models\AclResource;
use Modules\Acl\app\Services\UserService;
use Modules\SystemBase\app\Services\Base\BaseService;
use Modules\SystemBase\app\Services\CacheService;
use Modules\SystemBase\app\Services\ParserService;
use Modules\WebsiteBase\app\Models\Base\CmsBase;
use Modules\WebsiteBase\app\Models\CmsContent;
use Modules\WebsiteBase\app\Models\CmsPage;

class CmsService extends BaseService
{
    /**
     * Remember all items to prevent recursion into infinity loop.
     *
     * @var array
     */
    protected array $alreadyParsed = [];

    /**
     * @var UserService|null
     */
    protected ?UserService $aclUserService = null;

    /**
     * @var bool
     */
    protected bool $currentUserCanManageCms = false;

    /**
     * @param  UserService  $aclUserService
     */
    public function __construct(UserService $aclUserService)
    {
        parent::__construct();
        $this->aclUserService = $aclUserService;
        $this->currentUserCanManageCms = $this->aclUserService->hasUserResource(Auth::user(),
            AclResource::RES_MANAGE_CONTENT);
    }

    /**
     * @param  string  $cmsModelClass
     * @param  string  $code
     * @param  string  $locale
     * @param  bool    $contentOnly
     *
     * @return mixed
     */
    public function get(string $cmsModelClass, string $code, string $locale = '', bool $contentOnly = true): mixed
    {
        if (!$locale) {
            $locale = config('app.locale', 'en');
        }

        /** @var CmsContent|CmsBase $cmsModelClass */
        return app(CacheService::class)->rememberUseConfig($cmsModelClass."_get_{$code}_{$locale}_".($contentOnly ? '1' : '0'), 'system-base.cache.object.instance.ttl', function () use ($cmsModelClass, $code, $locale, $contentOnly) {

            /** @var Builder $builder */
            $builder = $cmsModelClass::with([])->frontendItems()->where('code', $code)->where('locale', $locale);
            /** @var CmsContent|CmsBase $item */
            if ($item = $builder->first()) {
                if ($contentOnly) {
                    if ($item) {
                        return $item->content ?? '';
                    }

                    return ''; // force empty string
                }
            }

            return $item;
        });
    }

    /**
     * @param  string  $routeUri
     *
     * @return CmsPage|null
     */
    public function getRoutePage(string $routeUri): ?CmsPage
    {
        $locale = config('app.locale', 'en');

        return app(CacheService::class)->rememberUseConfig(CmsPage::class."_routePage_{$routeUri}_{$locale}", 'system-base.cache.object.instance.ttl', function () use ($routeUri, $locale) {
            /** @var Builder $builder */
            $builder = CmsPage::with([])->frontendItems()->where('web_uri', $routeUri)->where('locale', $locale);
            /** @var CmsPage $item */
            $item = $builder->first();

            return $item;
        });
    }


    /**
     * 1) format as desired
     * 2) run own parser
     * 3) run blade parser
     *
     * @param  CmsBase      $item
     * @param  string|null  $raw
     *
     * @return string
     */
    public function getCalculated(CmsBase $item, ?string $raw): string
    {
        if (!$raw) {
            return '';
        }

        $result = $raw;
        if ($this->isAlreadyParsed($item)) {
            $error = sprintf('[[[RECURSION DETECTED class: "%s", code: "%s"]]]', get_class($item), $item->code);
            $this->error($error, [$item, __METHOD__]);
            $error = "<div class='small alert alert-danger'>$error</div>";

            return $error;
        }

        $this->setAlreadyParsed($item);

        try {

            // run own parser ...
            $parser = $this->getCmsParser();

            // format as desired ...
            switch ($item->format) {
                case 'html':
                    break;
                case 'plain':
                case 'markdown':
                    $result = nl2br($result);
                    break;
            }

            $result = $parser->parse($result);

            // run blade render engine ...
            $result = Blade::render($result, ['cms_object' => $item]);


        } catch (Exception $e) {

            $this->error($e->getMessage());
            $this->error($e->getTraceAsString());

        }

        $this->setAlreadyParsed($item, false);

        return $result;
    }

    /**
     * @param  CmsBase  $o
     *
     * @return string
     */
    protected function getAlreadyParsedKey(CmsBase $o): string
    {
        return get_class($o).'_'.$o->code;
    }

    /**
     * @param  CmsBase  $o
     * @param  bool     $setOrRemove
     *
     * @return void
     */
    protected function setAlreadyParsed(CmsBase $o, bool $setOrRemove = true): void
    {
        $k = $this->getAlreadyParsedKey($o);
        if ($setOrRemove) {
            $this->alreadyParsed[$k] = true;
        } else {
            unset($this->alreadyParsed[$k]);
        }
    }

    /**
     * @param  CmsBase  $o
     *
     * @return bool
     */
    protected function isAlreadyParsed(CmsBase $o): bool
    {
        return isset($this->alreadyParsed[$this->getAlreadyParsedKey($o)]);
    }

    /**
     * @return ParserService
     */
    protected function getCmsParser(): ParserService
    {
        /** @var ParserService $parser */
        $parser = app(ParserService::class);

        $placeHolders = [
            'cms_page'    => [
                'parameters' => [],
                'callback'   => function (array $placeholderParameters, array $parameters, array $recursiveData) {
                    return $this->callbackParserFunctionCmsBase(CmsPage::class,
                        $placeholderParameters,
                        $parameters,
                        $recursiveData);
                },
            ],
            'cms_content' => [
                'parameters' => [],
                'callback'   => function (array $placeholderParameters, array $parameters, array $recursiveData) {
                    return $this->callbackParserFunctionCmsBase(CmsContent::class,
                        $placeholderParameters,
                        $parameters,
                        $recursiveData);
                },
            ],
        ];
        $parser->setPlaceholders($placeHolders);

        return $parser;
    }

    /**
     * @param  string  $cmsClass
     * @param  array   $placeholderParameters
     * @param  array   $parameters
     * @param  array   $recursiveData
     *
     * @return string
     */
    protected function callbackParserFunctionCmsBase(string $cmsClass, array $placeholderParameters, array $parameters, array $recursiveData): string
    {
        if ($code = data_get($placeholderParameters, "code", '')) {
            $property = data_get($placeholderParameters, "property", 'content');
            /** @var CmsBase $item */
            /** @var CmsBase $cmsClass */
            $item = $this->get($cmsClass, $code, contentOnly: false);
            if (isset($item->$property)) {
                $result = $this->getCalculated($item, $item->$property);

                // if user can manage, add the edit button
                if ($this->currentUserCanManageCms) {
                    $result .= view('content-pages.inc.cms-edit-button', [
                        'cmsModel'     => $item,
                        'cmsModelName' => (($item instanceof CmsContent) ? 'CmsContent' : (($item instanceof CmsPage) ? 'CmsPage' : '')),
                        'id'           => $item->getKey(),
                    ])->render();
                }

                return $result;
            }
        }

        return '';
    }


}