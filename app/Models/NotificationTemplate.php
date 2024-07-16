<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperNotificationTemplate
 */
class NotificationTemplate extends Model
{
    use HasFactory;
    use TraitBaseModel;

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @var string
     */
    protected $table = 'notification_templates';

    /**
     * @var array
     */
    protected $appends = [
        'is_valid'
    ];

    /**
     * @return BelongsTo
     */
    public function viewTemplate()
    {
        return $this->belongsTo(ViewTemplate::class);
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
            $q->where(function (Builder $b1) {
                $b1->whereDoesntHave('viewTemplate')->orWhereHas('viewTemplate', function (Builder $b2) {
                    $b2->validItems();
                });
            });
        });
    }

    /**
     * @return Attribute
     */
    protected function isValid(): Attribute
    {
        $result = $this->is_enabled && (!$this->viewTemplate || $this->viewTemplate->isValid);
        return Attribute::make(get: fn() => $result);
    }


}
