<?php

namespace App\Providers;

use App\Repositories\{
    BaseRepository,
    ProvisionRepository
};
use App\Repositories\Contracts\{
    ProvisionRepositoryInterface,
    EloquentRepositoryInterface
};
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
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
