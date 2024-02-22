<?php

namespace App\Providers;

use App\Services\Interfaces\{
    FixExpenseServiceInterface,
    ProvisionServiceInterface,
    TagServiceInterface
};
use App\Services\{
    FixExpenseService,
    ProvisionService,
    TagService
};
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // if ($this->app->isLocal()) {
        //     $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        // }
        $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);

        // Apps Services
        $this->app->bind(FixExpenseServiceInterface::class, FixExpenseService::class);
        $this->app->bind(ProvisionServiceInterface::class, ProvisionService::class);
        $this->app->bind(TagServiceInterface::class, TagService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
