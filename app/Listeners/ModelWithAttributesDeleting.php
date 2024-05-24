<?php

namespace Modules\WebsiteBase\app\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;

class ModelWithAttributesDeleting
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
     * @param  \Modules\WebsiteBase\app\Events\ModelWithAttributesDeleting  $event
     *
     * @return void
     */
    public function handle(\Modules\WebsiteBase\app\Events\ModelWithAttributesDeleting $event)
    {
        /** @var TraitAttributeAssignment $model */
        $model = $event->model;

        Log::info(sprintf("Deleting Model %s : %s", get_class($model), $model->getKey()), [__METHOD__]);
    }
}
