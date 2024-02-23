<?php

namespace App\Services;

use App\Models\CreditCardInvoice;
use App\Repositories\Interfaces\{
    CreditCardInvoiceExpenseDivisionRepositoryInterface,
    CreditCardInvoiceExpenseRepositoryInterface,
    CreditCardInvoiceFileRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    CreditCardRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\CreditCardInvoiceServiceInterface;
use Exception;
use Illuminate\Support\Carbon;

class CreditCardInvoiceService implements CreditCardInvoiceServiceInterface
{
    public function __construct(
        private CreditCardRepositoryInterface $creditCardRepository,
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private CreditCardInvoiceExpenseRepositoryInterface $creditCardInvoiceExpenseRepository,
        private CreditCardInvoiceExpenseDivisionRepositoryInterface $creditCardInvoiceExpenseDivisionRepository,
        private CreditCardInvoiceFileRepositoryInterface $creditCardInvoiceFileRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserRepositoryInterface $shareUserRepository
    ) {
    }

    /**
     * Returns data for Credit Card Invoice Management
     * @return Array
     */
    public function index(int $creditCardId): array
    {
        $creditCard = $this->creditCardRepository->show($creditCardId, ['invoices']);
        return [
            'creditCard' => $creditCard,
            'invoices' => $creditCard->invoices,
        ];
    }

    /**
     * Create a new Invoices at to end of year
     * @param string $dueDate
     * @param string $closingDate
     * @param string $year
     * @param string $month
     * @param integer $creditCardId
     * @return CreditCardInvoice
     */
    public function createAutomatic(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
    ): bool {
        $this->create($dueDate, $closingDate, $year, $month, $creditCardId);
        $due_date = Carbon::parse($dueDate);
        $closing_date = Carbon::parse($closingDate);

        $new_due_date = $due_date->copy()->addMonth();
        $new_closing_date = $closing_date->copy()->addMonth();

        for ($i = $new_due_date->month; $i <= 12; $i++) {
            $this->create($new_due_date, $new_closing_date, $year, $new_due_date->month, $creditCardId);
            $new_due_date->addMonth();
            $new_closing_date->addMonth();
        }

        return true;
    }

    /**
     * Create a new Invoice
     * @param string $dueDate
     * @param string $closingDate
     * @param string $year
     * @param string $month
     * @param integer $creditCardId
     * @return CreditCardInvoice
     */
    public function create(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
    ): CreditCardInvoice {
        $credit_card = $this->creditCardRepository->show($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = $this->creditCardInvoiceRepository->getOne(['year' => $year, 'month' => $month, 'credit_card_id' => $creditCardId]);

        if ($credit_card_invoice) {
            throw new Exception('credit-card-invoice.already-exists');
        }

        return $this->creditCardInvoiceRepository->store([
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'year' => $year,
            'month' => $month,
            'credit_card_id' => $creditCardId,
        ]);
    }

    /**
     * Delete a Invoice
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card_invoice = $this->creditCardInvoiceRepository->show($id, [
            'file',
            'expenses' => [
                'divisions' => [
                    'tags'
                ],
                'tags'
            ]
        ]);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card_invoice->expenses as $expense) {
            foreach ($expense->divisions as $division) {
                $this->tagRepository->saveTagsToModel($division, $$division->tags);
                $this->creditCardInvoiceExpenseDivisionRepository->delete($division->id);
            }

            $this->tagRepository->saveTagsToModel($expense, $$expense->tags);
            $this->creditCardInvoiceExpenseRepository->delete($expense->id);
        }

        if ($credit_card_invoice->file) {
            $this->creditCardInvoiceFileRepository->delete($credit_card_invoice->file->id);
        }

        return $this->creditCardInvoiceRepository->delete($credit_card_invoice->id);
    }

    /**
     * Returns data for viewing/editing a Credit Card Invoice
     * @param int $id
     * @return Array
     */
    public function show(int $id): array
    {
        $credit_card_invoice = $this->creditCardInvoiceRepository->show($id, [
            'creditCard',
            'file',
            'expenses' => [
                'tags',
                'shareUser',
                'divisions' => [
                    'tags',
                    'shareUser'
                ]
            ]
        ]);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-inovice.not-found');
        }

        $shareUsers = $this->shareUserRepository->get(['user_id' => auth()->user()->id], [], [], ['shareUser']);

        if ($shareUsers && $shareUsers->count()) {
            $shareUsers = $shareUsers->map(function ($item) {
                return [
                    'share_user_id' => $item->share_user_id,
                    'share_user_name' => $item->shareUser->name
                ];
            });
        }

        return [
            'invoice' => $credit_card_invoice,
            'shareUsers' => $shareUsers,
        ];
    }
}
