<?php

namespace Modules\WebsiteBase\app\Listeners;

use Modules\WebsiteBase\app\Events\ModelWithAttributesSaved as ModelWithAttributesSavedEvent;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;

class ModelWithAttributesSaved
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
     * @param  ModelWithAttributesSavedEvent  $event
     *
     * @return void
     */
    public function handle(ModelWithAttributesSavedEvent $event): void
    {
        /** @var TraitAttributeAssignment $model */
        $model = $event->model;

        // save all extra attributes
        $model->saveModelAttributeTypeValues();
    }
}
