<?php

namespace Modules\WebsiteBase\app\Listeners;

use Modules\WebsiteBase\app\Events\ModelWithAttributesLoaded as ModelWithAttributesLoadedEvent;
use Modules\WebsiteBase\app\Models\Base\TraitAttributeAssignment;

class ModelWithAttributesLoaded
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
     * @param  ModelWithAttributesLoadedEvent  $event
     *
     * @return void
     */
    public function handle(ModelWithAttributesLoadedEvent $event): void
    {
        /** @var TraitAttributeAssignment $model */
        $model = $event->model;

        $model->refreshExtraAttributeProperties();
    }
}
