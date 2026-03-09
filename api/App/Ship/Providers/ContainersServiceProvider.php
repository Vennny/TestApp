<?php

declare(strict_types=1);

namespace App\Ship\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Class ContainersServiceProvider
 *
 * @package App\Ship\Providers
 */
final class ContainersServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->registerContactsContainer();
    }

    private function registerContactsContainer(): void
    {
        $this->app->bind(
            \App\Containers\Contacts\Contracts\ContactsRepositoryInterface::class,
            \App\Containers\Contacts\Repositories\ContactsRepository::class
        );
    }
}
