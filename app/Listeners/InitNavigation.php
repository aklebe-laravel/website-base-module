<?php

namespace Modules\WebsiteBase\app\Listeners;

class InitNavigation
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
     * @param  object  $event
     *
     * @return void
     */
    public function handle($event)
    {
        app('website_base_settings')->createNavigation();
    }
}
