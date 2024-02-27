<?php

namespace App\Providers;

use App\Helpers\Budget\BudgetCalculate;
use App\Helpers\Budget\BudgetShowData;
use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(BudgetShowDataInterface::class, BudgetShowData::class);
        $this->app->bind(BudgetCalculateInterface::class, BudgetCalculate::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
