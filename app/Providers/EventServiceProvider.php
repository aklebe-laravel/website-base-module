<?php

namespace Modules\WebsiteBase\app\Providers;

use Illuminate\Auth\Events\Authenticated;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Modules\WebsiteBase\app\Events\InitNavigation;
use Modules\WebsiteBase\app\Events\ModelWithAttributesDeleted;
use Modules\WebsiteBase\app\Events\ModelWithAttributesDeleting;
use Modules\WebsiteBase\app\Events\ModelWithAttributesLoaded;
use Modules\WebsiteBase\app\Events\ModelWithAttributesSaved;
use Modules\WebsiteBase\app\Listeners\MailSending;
use Modules\WebsiteBase\app\Listeners\MailSent;
use Modules\WebsiteBase\app\Listeners\UserOnline;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        InitNavigation::class              => [
            \Modules\WebsiteBase\app\Listeners\InitNavigation::class,
        ],
        ModelWithAttributesLoaded::class   => [
            \Modules\WebsiteBase\app\Listeners\ModelWithAttributesLoaded::class,
        ],
        ModelWithAttributesSaved::class    => [
            \Modules\WebsiteBase\app\Listeners\ModelWithAttributesSaved::class,
        ],
        ModelWithAttributesDeleting::class => [
            \Modules\WebsiteBase\app\Listeners\ModelWithAttributesDeleting::class,
        ],
        ModelWithAttributesDeleted::class  => [
            \Modules\WebsiteBase\app\Listeners\ModelWithAttributesDeleted::class,
        ],
        Authenticated::class               => [
            UserOnline::class,
        ],
        MessageSending::class              => [
            MailSending::class,
        ],
        MessageSent::class                 => [
            MailSent::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
