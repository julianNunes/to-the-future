<?php

namespace App\Helpers\Budget\Interfaces;

use Illuminate\Support\Collection;

interface BudgetCalculateInterface
{
    /**
     * Recalculate budget
     * @param integer $id
     * @param boolean $calculateShareUser
     * @return boolean
     */
    public function recalculate(int $id, bool $calculateShareUser = false): bool;
}
