<?php

namespace App\Providers;

use App\Listeners\LogSentEmail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Mail\Events\MessageSent;
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
        MessageSent::class => [
            LogSentEmail::class,
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
