<?php

namespace App\Repositories;

use App\Models\CreditCardInvoiceExpense;
use App\Repositories\Interfaces\CreditCardInvoiceExpenseRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class CreditCardInvoiceExpenseRepository extends AppRepository implements CreditCardInvoiceExpenseRepositoryInterface
{
    public function __construct(?CreditCardInvoiceExpense $creditCardInvoiceExpense = null)
    {
        parent::__construct($creditCardInvoiceExpense ?? new CreditCardInvoiceExpense);
    }

    /**
     * Search by description. Used in the "v-auto-complete" component
     * @param string $description
     * @return Collection
     */
    public function search(string $description): Collection
    {
        return $this->model->select([
            'description',
            'value',
            'share_value',
            'share_user_id',
            'remarks'
        ])
            ->with(['tags'])
            ->where('description', 'LIKE', "%{$description}%")
            ->get();
    }
}
