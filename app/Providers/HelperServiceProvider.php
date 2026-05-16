<?php

namespace App\Providers;

use App\Helpers\Budget\BudgetCalculate;
use App\Helpers\Budget\BudgetRelationPeriodBinder;
use App\Helpers\Budget\BudgetShowData;
use App\Helpers\Budget\BudgetShowRelations;
use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Helpers\Budget\Interfaces\BudgetRelationPeriodBinderInterface;
use App\Helpers\Budget\Interfaces\BudgetShowDataInterface;
use App\Helpers\Budget\Interfaces\BudgetShowRelationsInterface;
use App\Helpers\ShareUser\Interfaces\ShareUserOptionsInterface;
use App\Helpers\ShareUser\ShareUserOptions;
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
        $this->app->bind(BudgetShowRelationsInterface::class, BudgetShowRelations::class);
        $this->app->bind(BudgetRelationPeriodBinderInterface::class, BudgetRelationPeriodBinder::class);
        $this->app->bind(ShareUserOptionsInterface::class, ShareUserOptions::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
