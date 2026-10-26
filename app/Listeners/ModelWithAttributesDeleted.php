<?php

namespace Modules\WebsiteBase\app\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\WebsiteBase\app\Events\ModelWithAttributesDeleted as ModelWithAttributesDeletedEvent;
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
     * @param  ModelWithAttributesDeletedEvent  $event
     *
     * @return void
     */
    public function handle(ModelWithAttributesDeletedEvent $event): void
    {
        /** @var TraitAttributeAssignment $model */
        $model = $event->model;

        // delete all extra attributes
        $model->deleteModelAttributeTypeValues();

        Log::info(sprintf("Deleted Model %s : %s", get_class($model), $model->getKey()), [__METHOD__]);
    }
}
