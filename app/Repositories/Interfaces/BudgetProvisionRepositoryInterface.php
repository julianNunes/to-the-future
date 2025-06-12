<?php

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface BudgetProvisionRepositoryInterface extends AppRepositoryInterface
{
    /**
     * Search by description. Used in the "v-auto-complete" component
     * @param string $description
     * @return Collection
     */
    public function search(string $description): Collection;
}
