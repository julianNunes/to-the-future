<?php

namespace App\Providers;

use App\Repositories\{
    AppRepository,
    ProvisionRepository,
    ShareUserRepository,
    TagRepository
};
use App\Repositories\Interfaces\{
    AppRepositoryInterface,
    ProvisionRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(AppRepositoryInterface::class, AppRepository::class);
        $this->app->bind(ShareUserRepositoryInterface::class, ShareUserRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(ProvisionRepositoryInterface::class, ProvisionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
