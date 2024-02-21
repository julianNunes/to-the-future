<?php

namespace App\Providers;

use App\Repositories\{
    AppRepository,
    TagRepository
};
use App\Repositories\Interfaces\{
    AppRepositoryInterface,
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
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
