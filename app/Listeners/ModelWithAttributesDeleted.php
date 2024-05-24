<?php

namespace Modules\WebsiteBase\app\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;

class ModelWithAttributesDeleted
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \Modules\WebsiteBase\app\Events\ModelWithAttributesDeleted  $event
     *
     * @return void
     */
    public function handle(\Modules\WebsiteBase\app\Events\ModelWithAttributesDeleted $event)
    {
        /** @var TraitAttributeAssignment $model */
        $model = $event->model;

        // delete all extra attributes
        $model->deleteModelAttributeTypeValues();

        Log::info(sprintf("Deleted Model %s : %s", get_class($model), $model->getKey()), [__METHOD__]);
    }
}
