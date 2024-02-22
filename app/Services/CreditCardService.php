<?php

namespace App\Services;

use App\Models\CreditCard;
use App\Repositories\Interfaces\CreditCardInvoiceExpenseDivisionRepositoryInterface;
use App\Repositories\Interfaces\CreditCardInvoiceExpenseRepositoryInterface;
use App\Repositories\Interfaces\CreditCardInvoiceFileRepositoryInterface;
use App\Repositories\Interfaces\CreditCardInvoiceRepositoryInterface;
use App\Repositories\Interfaces\CreditCardRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\CreditCardServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class CreditCardService implements CreditCardServiceInterface
{
    public function __construct(
        private CreditCardRepositoryInterface $creditCardRepository,
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private CreditCardInvoiceExpenseRepositoryInterface $creditCardInvoiceExpenseRepository,
        private CreditCardInvoiceExpenseDivisionRepositoryInterface $creditCardInvoiceExpenseDivisionRepository,
        private CreditCardInvoiceFileRepositoryInterface $creditCardInvoiceFileRepository,
        private TagRepositoryInterface $tagRepository
    ) {
    }

    /**
     * Returns data to Credit Card Management
     * @return Array
     */
    public function index(): array
    {
        $creditCards = $this->creditCardRepository->get(['user_id' => auth()->user()->id]);
        return [
            'creditCards' => $creditCards,
        ];
    }

    /**
     * Create new Credit Card
     * @param string $name
     * @param string $digits
     * @param string $dueDate
     * @param string $closingDate
     * @param boolean $isActive
     * @return CreditCard
     */
    public function create(
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): CreditCard {
        $credit_card = $this->creditCardRepository->getOne(['name' => $name]);

        if ($credit_card) {
            throw new Exception('credit-card.already-exists');
        }

        $credit_card = $this->creditCardRepository->store([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ]);

        return $credit_card;
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
    ): CreditCard {
        $credit_card = $this->creditCardRepository->getOne(function (Builder $query) use ($id, $name) {
            $query->where('name', $name)->where('id', '!=', $id);
        });

        if ($credit_card) {
            throw new Exception('credit-card.already-exists');
        }

        $credit_card = $this->creditCardRepository->show($id);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        return $this->creditCardRepository->store([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ], $credit_card);
    }

    /**
     * Deleta a Credit Card
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card = $this->creditCardRepository->show($id, [
            'invoices' => [
                'file',
                'expenses' => [
                    'divisions' => [
                        'tags'
                    ],
                    'tags'
                ]
            ]
        ]);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card->invoices as $invoice) {
            foreach ($invoice->expenses as $expense) {
                foreach ($expense->divisions as $division) {
                    $this->tagRepository->saveTagsToModel($division, $$division->tags);
                    $this->creditCardInvoiceExpenseDivisionRepository->delete($division->id);
                }

                $this->tagRepository->saveTagsToModel($expense, $$expense->tags);
                $this->creditCardInvoiceExpenseRepository->delete($expense->id);
            }

            if ($invoice->file) {
                $this->creditCardInvoiceFileRepository->delete($invoice->file->id);
            }

            $this->creditCardInvoiceRepository->delete($invoice->id);
        }

        return $this->creditCardRepository->delete($credit_card->id);
    }
}
