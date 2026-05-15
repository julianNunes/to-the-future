<?php

namespace App\Repositories;

use App\Models\BudgetProvision;
use App\Repositories\Interfaces\BudgetProvisionRepositoryInterface;
use Illuminate\Support\Collection;

class BudgetProvisionRepository extends AppRepository implements BudgetProvisionRepositoryInterface
{
    public function __construct(?BudgetProvision $budgetProvision = null)
    {
        parent::__construct($budgetProvision ?? new BudgetProvision);
    }

    /**
     * Search by description. Used in the "v-auto-complete" component
     * @param string $description
     * @return Collection
     */
    public function search(string $description): Collection
    {
        return $this->model
            ->select([
                'id',
                'description',
                'value',
                'share_value',
                'share_user_id',
                'remarks'
            ])
            ->with(['tags'])
            ->where('description', 'LIKE', "%{$description}%")
            ->whereHas('budget', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'DESC')
            ->get()
            ->unique('description');
    }
}
