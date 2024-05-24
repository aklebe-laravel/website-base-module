<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;
use Modules\WebsiteBase\Models\IdeHelperModelAttributeAssignment;


/**
 * @mixin IdeHelperModelAttributeAssignment
 */
class ModelAttributeAssignment extends Model
{
    use HasFactory;
    use TraitBaseModel;

    protected $table = 'model_attribute_assignments';

    public const ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX = 'model_attribute_assignment_';
    const ATTRIBUTE_TYPE_STRING = 'string';
    const ATTRIBUTE_TYPE_TEXT = 'text';
    const ATTRIBUTE_TYPE_INTEGER = 'integer';
    const ATTRIBUTE_TYPE_DOUBLE = 'double';

    /**
     * Filled up in constructor
     *
     * @var array|array[]
     */
    const ATTRIBUTE_TYPE_MAP = [
        self::ATTRIBUTE_TYPE_STRING  => [
            'type'         => 'string',
            'table_suffix' => 'strings',
            'validator'    => ['nullable', 'string', 'Max:255'],
        ],
        self::ATTRIBUTE_TYPE_TEXT    => [
            'type'         => 'string',
            'table_suffix' => 'texts',
            'validator'    => ['nullable', 'string'],
        ],
        self::ATTRIBUTE_TYPE_INTEGER => [
            'type'         => 'int',
            'table_suffix' => 'integers',
            'validator'    => ['nullable', 'integer'],
        ],
        self::ATTRIBUTE_TYPE_DOUBLE  => [
            'type'         => 'float',
            'table_suffix' => 'doubles',
            'validator'    => ['nullable', 'float'],
        ],
    ];

    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function modelAttribute()
    {
        return $this->belongsTo(ModelAttribute::class);//, 'model_attribute_id', 'id');
    }
}
