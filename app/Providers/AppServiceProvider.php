<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use App\Services\ClaimsRatioService;
use App\Services\NepService;
use App\Services\ExpenseService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ClaimsRatioService::class, function ($app) {
            return new ClaimsRatioService('mysql', $app->make(NepService::class));
        });
       $this->app->bind(ExpenseService::class, function ($app) {
            return new ExpenseService('mysql',$app->make(NepService::class));
        });
       
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
