<?php

namespace App\Repositories;

use App\Models\PrepaidCardExtractExpense;
use App\Repositories\Interfaces\PrepaidCardExtractExpenseRepositoryInterface;
use Illuminate\Support\Collection;

class PrepaidCardExtractExpenseRepository extends AppRepository implements PrepaidCardExtractExpenseRepositoryInterface
{
    public function __construct(?PrepaidCardExtractExpense $prepaidCardExtractExpense = null)
    {
        parent::__construct($prepaidCardExtractExpense ?? new PrepaidCardExtractExpense);
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
            ->whereHas('extract.prepaidCard', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->orderBy('created_at', 'DESC')
            ->get()
            ->unique('description');
    }
}
