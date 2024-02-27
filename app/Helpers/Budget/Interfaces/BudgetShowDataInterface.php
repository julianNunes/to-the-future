<?php

namespace App\Helpers\Budget\Interfaces;

use Illuminate\Support\Collection;

interface BudgetShowDataInterface
{
    /**
     * Show data to the view
     * @param integer $id
     * @return array
     */
    public function dataShow(int $id): array;
}
