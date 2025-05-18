<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->singleton(ApiContext::class, function ($app) {
            $apiContext = new ApiContext(
                new OAuthTokenCredential(
                    config('paypal.client_id'),
                    config('paypal.secret')
                )
            );
            $apiContext->setConfig(config('paypal.settings'));            
            return $apiContext;
        });
    }
}
