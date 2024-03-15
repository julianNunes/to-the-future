<?php

namespace App\Services;

use App\Models\CreditCard;
use App\Repositories\Interfaces\{
    CreditCardInvoiceExpenseDivisionRepositoryInterface,
    CreditCardInvoiceExpenseRepositoryInterface,
    CreditCardInvoiceFileRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    CreditCardRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\CreditCardServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

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

        if ($credit_card->due_date != $dueDate || $credit_card->closing_date != $closingDate) {
            \Log::info('sao datas diferentes');
            $invoices = $this->creditCardInvoiceRepository->get(['closed' => false, 'credit_card_id' => $credit_card->id]);

            if ($invoices && $invoices->count()) {
                foreach ($invoices as $invoice) {
                    // $update = [];

                    // if ($credit_card->due_date != $dueDate) {
                    //     $update[] = (object) ['due_date' => Carbon::parse($invoice->due_date)->day($dueDate)->format('y-m-d')];
                    // }

                    // if ($credit_card->closing_date != $closingDate) {
                    //     $update[] = (object) ['closing_date' => Carbon::parse($invoice->closing_date)->day($closingDate)->format('y-m-d')];
                    // }

                    // \Log::info($update);

                    $this->creditCardInvoiceRepository->store([
                        'due_date' => Carbon::parse($invoice->due_date)->day($dueDate)->format('y-m-d'),
                        'closing_date' => Carbon::parse($invoice->closing_date)->day($closingDate)->format('y-m-d')
                    ], $invoice);
                }
            }
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
                    $this->tagRepository->saveTagsToModel($division, $division->tags);
                    $this->creditCardInvoiceExpenseDivisionRepository->delete($division->id);
                }

                $this->tagRepository->saveTagsToModel($expense, $expense->tags);
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
