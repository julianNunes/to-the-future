<?php

namespace App\Services;

use App\Models\PrepaidCard;
use App\Repositories\Interfaces\{
    PrepaidCardInvoiceExpenseRepositoryInterface,
    PrepaidCardInvoiceRepositoryInterface,
    PrepaidCardRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\PrepaidCardServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class PrepaidCardService implements PrepaidCardServiceInterface
{
    public function __construct(
        private PrepaidCardRepositoryInterface $prepaidCardRepository,
        private PrepaidCardInvoiceRepositoryInterface $prepaidCardInvoiceRepository,
        private PrepaidCardInvoiceExpenseRepositoryInterface $prepaidCardInvoiceExpenseRepository,
        private TagRepositoryInterface $tagRepository
    ) {
    }

    /**
     * Returns data to Credit Card Management
     * @return Array
     */
    public function index(): array
    {
        $prepaidCards = $this->prepaidCardRepository->get(['user_id' => auth()->user()->id]);
        return [
            'prepaidCards' => $prepaidCards,
        ];
    }

    /**
     * Create new Credit Card
     * @param string $name
     * @param string $digits
     * @param string $dueDate
     * @param string $closingDate
     * @param boolean $isActive
     * @return PrepaidCard
     */
    public function create(
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): PrepaidCard {
        $prepaid_card = $this->prepaidCardRepository->getOne(['name' => $name]);

        if ($prepaid_card) {
            throw new Exception('prepaid-card.already-exists');
        }

        $prepaid_card = $this->prepaidCardRepository->store([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ]);

        return $prepaid_card;
    }

    /**
     * Update a Credit Card
     * @param integer $id
     * @param string $name
     * @param string $digits
     * @param string $due_date
     * @param string $closing_date
     * @param boolean $is_active
     * @return boolean
     */
    public function update(
        int $id,
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): PrepaidCard {
        $prepaid_card = $this->prepaidCardRepository->getOne(function (Builder $query) use ($id, $name) {
            $query->where('name', $name)->where('id', '!=', $id);
        });

        if ($prepaid_card) {
            throw new Exception('prepaid-card.already-exists');
        }

        $prepaid_card = $this->prepaidCardRepository->show($id);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        return $this->prepaidCardRepository->store([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ], $prepaid_card);
    }

    /**
     * Deleta a Credit Card
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $prepaid_card = $this->prepaidCardRepository->show($id, [
            'invoices' => [
                'expenses' => [
                    'tags'
                ]
            ]
        ]);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        // Remove todos os vinculos
        foreach ($prepaid_card->invoices as $invoice) {
            foreach ($invoice->expenses as $expense) {
                $this->tagRepository->saveTagsToModel($expense, $expense->tags);
                $this->prepaidCardInvoiceExpenseRepository->delete($expense->id);
            }

            $this->prepaidCardInvoiceRepository->delete($invoice->id);
        }

        return $this->prepaidCardRepository->delete($prepaid_card->id);
    }
}
