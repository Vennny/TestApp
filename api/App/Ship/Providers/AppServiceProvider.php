<?php

declare(strict_types=1);

namespace App\Ship\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\DateFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        DateFactory::use(CarbonImmutable::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
