<?php

namespace Modules\WebsiteBase\app\Listeners;

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
     * @param  \Modules\WebsiteBase\app\Events\ModelWithAttributesLoaded  $event
     *
     * @return void
     */
    public function handle(\Modules\WebsiteBase\app\Events\ModelWithAttributesLoaded $event)
    {
        /** @var TraitAttributeAssignment $model */
        $model = $event->model;

        $model->refreshExtraAttributeProperties();
    }
}
