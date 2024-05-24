<?php

namespace Modules\WebsiteBase\app\Events;

use Illuminate\Database\Eloquent\Model;

class ModelWithAttributesLoaded
{
    /**
     * @var Model|null
     */
    public ?Model $model = null;

    /**
     * Create a new event instance.
     */
    public function __construct(?Model $model)
    {
        // remember model for listeners ...
        $this->model = $model;
    }

}
