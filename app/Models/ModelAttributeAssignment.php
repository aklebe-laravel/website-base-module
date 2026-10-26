<?php

namespace Modules\WebsiteBase\app\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\WebsiteBase\app\Models\Base\TraitBaseModel;

/**
 * @mixin IdeHelperModelAttributeAssignment
 */
class ModelAttributeAssignment extends Model
{
    use HasFactory;
    use TraitBaseModel;

    protected $table = 'model_attribute_assignments';

    public const string ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX = 'model_attribute_assignment_';
    const string ATTRIBUTE_TYPE_STRING = 'string';
    const string ATTRIBUTE_TYPE_TEXT = 'text';
    const string ATTRIBUTE_TYPE_INTEGER = 'integer';
    const string ATTRIBUTE_TYPE_DOUBLE = 'double';
    const string ATTRIBUTE_TYPE_OBJECT = 'object';
    const string ATTRIBUTE_TYPE_ARRAY = 'array';
    const string ATTRIBUTE_TYPE_JSON = 'json'; // same like array

    /**
     * Filled up in constructor
     *
     * @var array|array[]
     */
    const array ATTRIBUTE_TYPE_MAP = [
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
        self::ATTRIBUTE_TYPE_OBJECT  => [
            'type'         => 'object',
            'table_suffix' => 'texts',
            'validator'    => ['nullable', 'object'],
        ],
        self::ATTRIBUTE_TYPE_ARRAY   => [
            'type'         => 'array',
            'table_suffix' => 'texts',
            'validator'    => ['nullable', 'array'],
        ],
        self::ATTRIBUTE_TYPE_JSON    => [
            'type'         => 'array',
            'table_suffix' => 'texts',
            'validator'    => ['nullable', 'array'],
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
    public function modelAttribute(): BelongsTo
    {
        return $this->belongsTo(ModelAttribute::class);//, 'model_attribute_id', 'id');
    }
}
