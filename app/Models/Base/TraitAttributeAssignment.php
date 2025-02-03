<?php

namespace Modules\WebsiteBase\app\Models\Base;

use Exception;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\WebsiteBase\app\Events\ModelWithAttributesDeleted;
use Modules\WebsiteBase\app\Events\ModelWithAttributesDeleting;
use Modules\WebsiteBase\app\Events\ModelWithAttributesLoaded;
use Modules\WebsiteBase\app\Events\ModelWithAttributesSaved;
use Modules\WebsiteBase\app\Models\ModelAttributeAssignment;
use Modules\WebsiteBase\app\Services\WebsiteService;

trait TraitAttributeAssignment
{
    /**
     * It's just the bag for mutator extra_attributes!
     * From outside, we use the mutator extra_attributes.
     *
     * Dynamic extra attributes by classes. Filled on load.
     * Usage like:
     *      data_get($product, 'extra_attributes.price')
     *      $product->getExtraAttribute('price')
     *
     * @var array
     */
    protected array $extraAttributes = [];

    /**
     * @var array
     */
    protected array $extraAttributeTypes = [];

    /**
     * @var bool
     * @todo: How to disable the whole created collection?
     */
    protected bool $extraAttributesLoaded = false;

    /**
     *
     */
    const string ATTR_NOTIFICATION_CHANNELS = 'preferred_notification_channels';

    /**
     * General boot...() info: Static Setup for this object like events
     * General initialize...() info: executed for every new instance
     *
     * @return void
     */
    public function initializeTraitAttributeAssignment(): void
    {
        $this->appends[] = 'extra_attributes';

        // Load with cache all assignments and defaults.
        // ModelWithAttributesLoaded will not care about make(), so we have also to call it here.
        $this->fillDefaultExtraAttributes();

        // Add the extra attribute events
        $this->dispatchesEvents = [
            'retrieved' => ModelWithAttributesLoaded::class,
            'saved'     => ModelWithAttributesSaved::class,
            'deleted'   => ModelWithAttributesDeleted::class,
            'deleting'  => ModelWithAttributesDeleting::class,
        ];
    }

    ///**
    // * General boot...() info: Static Setup for this object like events
    // * General initialize...() info: executed for every new instance
    // *
    // * @return void
    // */
    //public static function bootTraitAttributeAssignment(): void
    //{
    //}

    /**
     * @return void
     */
    public function fillDefaultExtraAttributes(): void
    {
        if ($collection = $this->getModelAttributeAssigmentCollection()) {
            foreach ($collection as $item) {

                // at first remember the type
                $this->extraAttributeTypes[$item->modelAttribute->code] = $item->attribute_type;

                // after type is saved, set the value in default way
                $this->setExtraAttribute($item->modelAttribute->code, $item->default_value);
            }
        }
    }

    /**
     * Get attribute cache settings for a model instance.
     *
     * @return array
     */
    public function getCacheParametersExtraAttributeEntity(): array
    {
        return [
            'key' => config('website-base.cache.extra_attribute_entity.prefix').$this->getAttributeModelIdent().'_'.$this->id,
            'ttl' => (int) config('website-base.cache.extra_attribute_entity.ttl'),
        ];
    }

    /**
     * Get attribute cache settings for a model
     *
     * @return array
     */
    public function getCacheParametersExtraAttributes(): array
    {
        return [
            'key' => config('website-base.cache.extra_attributes.prefix').$this->getAttributeModelIdent(),
            'ttl' => (int) config('website-base.cache.extra_attributes.ttl'),
        ];
    }

    /**
     * Clear extra attribute cache for this entity instance
     *
     * @return bool
     */
    public function clearCacheParametersExtraAttributeEntity(): bool
    {
        $cacheParams = $this->getCacheParametersExtraAttributeEntity();

        return WebsiteService::getExtraAttributeCache()->forget($cacheParams['key']);
    }

    /**
     * @return void
     */
    public function refreshExtraAttributeProperties(): void
    {
        $cacheParams = $this->getCacheParametersExtraAttributeEntity();

        // No assignment needed, because refreshing $this->extraAttributes[xxx] already

        $ea = WebsiteService::getExtraAttributeCache()->remember($cacheParams['key'], $cacheParams['ttl'],
            function () use ($cacheParams) {
                $r = [];
                // $this->extraAttributes have at least default values at this point
                foreach ($this->extraAttributes as $attributeCode => $v) {
                    $r[$attributeCode] = $this->loadModelAttributeTypeValue($attributeCode,
                        Arr::get($this->extraAttributes, $attributeCode));
                }

                return $r;
            });

        $this->setExtraAttributes($ea);

        $this->extraAttributesLoaded = true;
    }

    /**
     * Attribute 'extra_attributes'
     * Mutator should load automatically
     *
     * @return Attribute
     */
    protected function extraAttributes(): Attribute
    {
        return Attribute::make(get: function ($v) {
            return $this->getExtraAttributes();
        }, set: function ($v) {
            return $v;
        },);
    }

    /**
     * @alias setExtraAttribute()
     *
     * @param  string  $attributeCode
     * @param  mixed   $value
     *
     * @return void
     */
    public function addExtraAttribute(string $attributeCode, mixed $value): void
    {
        $this->setExtraAttribute($attributeCode, $value);
    }

    /**
     * @param  string  $attributeCode
     * @param  mixed   $value
     *
     * @return void
     */
    public function setExtraAttribute(string $attributeCode, mixed $value): void
    {
        switch ($this->extraAttributeTypes[$attributeCode]) {
            case ModelAttributeAssignment::ATTRIBUTE_TYPE_INTEGER:
                $value = (int) $value;
                break;

            case ModelAttributeAssignment::ATTRIBUTE_TYPE_DOUBLE:
                $value = (double) $value;
                break;

            case ModelAttributeAssignment::ATTRIBUTE_TYPE_ARRAY:
                if ($value !== null && !is_array($value)) {
                    $value = json_decode($value, true);
                }
                break;

            case ModelAttributeAssignment::ATTRIBUTE_TYPE_OBJECT:
                if ($value !== null && !is_object($value)) {
                    $value = (object) json_decode($value, true);
                }
                break;
        }

        //if ($value !== null) {
        //    Log::debug(__METHOD__, [$attributeCode, $this->extraAttributeTypes[$attributeCode], $value]);
        //}
        $this->extraAttributes[$attributeCode] = $value;
    }

    /**
     * @param  array  $values
     *
     * @return void
     */
    public function setExtraAttributes(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->setExtraAttribute($key, $value);
        }
    }

    /**
     * @return array
     */
    public function getExtraAttributes(): array
    {
        return $this->extraAttributes;
    }

    /**
     * Value has valid values if setExtraAttribute() was uses to set the value (which should always).
     *
     * @param  string      $attributeCode
     * @param  mixed|null  $default
     *
     * @return mixed
     */
    public function getExtraAttribute(string $attributeCode, mixed $default = null): mixed
    {
        return Arr::get($this->extraAttributes, $attributeCode, $default);
    }

    /**
     * Return the class itself or the class defined in static::ATTRIBUTE_MODEL_IDENT (if exist)
     * It's important for models wanted to use the same 'model' column in DB.
     *
     * @return string
     */
    public function getAttributeModelIdent(): string
    {
        // defined is slow ?!?
        $ttlDefault = config('system-base.cache.default_ttl', 1);
        $ttl = config('system-base.cache.db.signature.ttl', $ttlDefault);

        return WebsiteService::getExtraAttributeCache()->remember(static::class.'-CONST-DEFINED-ATTRIBUTE_MODEL_IDENT', $ttl, function () {
            return defined(static::class.'::ATTRIBUTE_MODEL_IDENT') ? static::ATTRIBUTE_MODEL_IDENT : static::class;
        });
    }

    /**
     * Get the collection of related model attributes (with relation modelAttribute) by using cache
     * for the specific inherited model like Product, Category or something else.
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|Collection
     */
    public function getModelAttributeAssigmentCollection(): Collection|array
    {
        $cacheParams = $this->getCacheParametersExtraAttributes();

        return WebsiteService::getExtraAttributeCache()->remember($cacheParams['key'], $cacheParams['ttl'], function () use ($cacheParams) {

            try {
                $attrModelIdent = $this->getAttributeModelIdent();
                $collection = ModelAttributeAssignment::with(['modelAttribute'])
                    ->where('model', '=', $attrModelIdent)
                    ->orderBy('form_position')
                    ->get();
            } catch (Exception $ex) {
                Log::error("Error by getting model attributes!", [static::class]);
                Log::error($ex->getMessage());
                $collection = Collection::make();
            }

            return $collection;
        });
    }

    /**
     * Returns a list of found values as rows. Should usually be a single entry.
     *
     * @param  string  $attributeCode
     *
     * @return Builder|null
     */
    public function loadModelAttributeTypeValues(string $attributeCode): ?Builder
    {
        $collection = $this->getModelAttributeAssigmentCollection()->where('modelAttribute.code', '=', $attributeCode);
        /** @var ModelAttributeAssignment $attribute */
        if ($attribute = $collection->first()) {
            if ($builder = DB::table(static::getAttributeTypeTableName($attribute->attribute_type))
                ->where('model_id', '=', $this->id)
                ->where('model_attribute_assignment_id', '=', $attribute->id)
            ) {
                return $builder;
            }
        }

        return null;
    }

    /**
     * Get the value of an attribute assigned to this instance id.
     *
     * @param  string  $attributeCode
     * @param          $default
     *
     * @return mixed|null
     */
    public function loadModelAttributeTypeValue(string $attributeCode, $default = null): mixed
    {
        if ($list = $this->loadModelAttributeTypeValues($attributeCode)) {
            if ($item = $list->first()) {
                return $item->value;
            }
        }

        return $default;
    }

    /**
     * @param  string  $attributeType
     *
     * @return string
     */
    public static function getAttributeTypeTableName(string $attributeType): string
    {
        return ModelAttributeAssignment::ATTRIBUTE_ASSIGNMENT_TYPE_TABLE_PREFIX.Arr::get(ModelAttributeAssignment::ATTRIBUTE_TYPE_MAP,
                $attributeType.'.table_suffix');
    }

    /**
     * @param  string  $attributeCode
     * @param  mixed   $value
     *
     * @return bool
     */
    public function saveModelAttributeTypeValue(string $attributeCode, mixed $value): bool
    {
        $collection = $this->getModelAttributeAssigmentCollection()->where('modelAttribute.code', '=', $attributeCode);
        /** @var ModelAttributeAssignment $attribute */
        if ($attribute = $collection->first()) {

            // cast array and objects to json strings
            if (is_array($value) || is_object($value)) {
                // make NULL if [] or {}
                if (empty($value)) {
                    $value = null;
                } else {
                    $value = json_encode($value);
                }
            }

            // check attribute already exists ...
            if ($builder = DB::table(static::getAttributeTypeTableName($attribute->attribute_type))
                ->where('model_id', '=', $this->id)
                ->where('model_attribute_assignment_id', '=', $attribute->id)
            ) {
                if ($item = $builder->first()) {

                    // if yes, just update it
                    $item->value = $value;

                    $builder->update([
                        'value'      => $value,
                        'updated_at' => Date::now(),
                    ]);

                    return true;
                }
            }

            // if not already exist, create a new one ...
            DB::table(static::getAttributeTypeTableName($attribute->attribute_type))->insert([
                'model_id'                      => $this->id,
                'model_attribute_assignment_id' => $attribute->id,
                'value'                         => $value,
                'updated_at'                    => Date::now(),
                'created_at'                    => Date::now(),
            ]);

            // We are clearing the cache if we delete all with saveModelAttributeTypeValues()
            // $this->clearCacheParametersExtraAttributeEntity();
            return true;
        }

        return false;
    }

    /**
     * Called after save automatically.
     *
     * @return void
     */
    public function saveModelAttributeTypeValues(): void
    {
        foreach ($this->extraAttributes as $attributeCode => $v) {
            $this->saveModelAttributeTypeValue($attributeCode, $v);
        }

        // force cache clear after saving
        $this->clearCacheParametersExtraAttributeEntity();
    }

    /**
     * @param  string|null  $attributeCode
     *
     * @return bool
     */
    public function deleteModelAttributeTypeValue(?string $attributeCode = null): bool
    {
        $collection = $this->getModelAttributeAssigmentCollection()->where('modelAttribute.code', '=', $attributeCode);
        /** @var ModelAttributeAssignment $attribute */
        if ($attribute = $collection->first()) {

            if ($builder = DB::table(static::getAttributeTypeTableName($attribute->attribute_type))
                ->where('model_id', '=', $this->id)
                ->where('model_attribute_assignment_id', '=', $attribute->id)
            ) {
                if ($item = $builder->first()) {
                    $builder->delete($item->id);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Called after deleted automatically.
     *
     * @return void
     */
    public function deleteModelAttributeTypeValues(): void
    {
        foreach ($this->extraAttributes as $attributeCode => $v) {
            $this->deleteModelAttributeTypeValue($attributeCode, $v);
        }

        // force cache clear after deleting
        $this->clearCacheParametersExtraAttributeEntity();
    }

    /**
     * Save without fire extra attribute events.
     *
     * @param  array  $options
     *
     * @return bool
     */
    public function saveWithoutEvents(array $options = []): bool
    {
        return $this->withoutEvents(function () use ($options) {
            return $this->save($options);
        });
    }

    /**
     * Update without fire extra attribute events.
     *
     * @param  array  $data
     * @param  array  $options
     *
     * @return bool
     */
    public function updateWithoutEvents(array $data = [], array $options = []): bool
    {
        return $this->withoutEvents(function () use ($data, $options) {
            return $this->update($data, $options);
        });
    }

}
