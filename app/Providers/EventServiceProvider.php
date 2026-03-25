<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Auth0\Auth0ExtendSocialite;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        SocialiteWasCalled::class => [
            Auth0ExtendSocialite::class . '@handle',
        ],
    ];

    public function boot(): void
    {
        //
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
