<?php

namespace App\Services\Facades;

use Illuminate\Support\Facades\Facade;

class BudgetService extends Facade
{
    protected static function getFacadeAccessor()
    {
        // Retorna o caminho da Interface, que será resolvida pelo Service Provider.
        return \App\Services\Interfaces\BudgetServiceInterface::class;
    }
}
