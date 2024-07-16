<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Shipu\Themevel\Facades\Theme;

/**
 * @mixin IdeHelperViewTemplate
 */
class ViewTemplate extends Model
{
    use HasFactory;
    use TraitBaseModel;

    const PARAMETER_VARIANT_DEFAULT = 'default';
    const PARAMETER_VARIANT_USER = 'user';

    const VALID_PARAMETER_VARIANTS = [
        self::PARAMETER_VARIANT_DEFAULT,
        self::PARAMETER_VARIANT_USER,
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'view_templates';

    /**
     * @var array
     */
    protected $appends = [
        'is_valid'
    ];

    /**
     * Get content from file ist exist, otherwise $this->content is used.
     *
     * @return false|mixed|string
     */
    public function getContent()
    {
        if ($this->view_file) {
            // Need to set active theme explicit here
            Theme::set(config('theme.active'));

            // get plain / not rendered html
            if (View::exists($this->view_file)) {
                $v = View::make($this->view_file);
                return file_get_contents($v->getPath());
            }

            return "unknown view ".$this->view_file;
        }

        return $this->content;
    }

    /**
     * scope scopeValidItems()
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeValidItems(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('is_enabled', true);
        });
    }

    /**
     * @return Attribute
     */
    protected function isValid(): Attribute
    {
        return Attribute::make(get: fn() => $this->is_enabled);
    }


}
