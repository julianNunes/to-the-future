<?php

namespace App\Providers;

use App\Services\Interfaces\TagServiceInterface;
use App\Services\TagService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
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
