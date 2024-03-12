<?php

namespace App\Services;

use App\Models\PrepaidCardExtract;
use App\Repositories\Interfaces\{
    BudgetRepositoryInterface,
    PrepaidCardExtractExpenseRepositoryInterface,
    PrepaidCardExtractRepositoryInterface,
    CreditCardRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\PrepaidCardExtractServiceInterface;
use Exception;
use Illuminate\Support\Carbon;

class PrepaidCardExtractService implements PrepaidCardExtractServiceInterface
{
    public function __construct(
        private CreditCardRepositoryInterface $creditCardRepository,
        private PrepaidCardExtractRepositoryInterface $prepaidCardExtractRepository,
        private PrepaidCardExtractExpenseRepositoryInterface $prepaidCardExtractExpenseRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
        private BudgetRepositoryInterface $budgetRepository,
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
     * @param boolean $creditCardId
     * @return PrepaidCardExtract
     */
    public function createAutomatic(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
        bool $automaticGenerate
    ): bool {
        $invoice = $this->create($dueDate, $closingDate, $year, $month, $creditCardId);

        // Verifico se existe Budget
        $budget = $this->budgetRepository->getOne(['month' => $month, 'year' => $year, 'user_id' => auth()->user()->id]);

        if ($budget) {
            $this->prepaidCardExtractRepository->store(['budget_id' => $budget->id], $invoice);
        }

        if ($automaticGenerate) {
            $due_date = Carbon::parse($dueDate);
            $closing_date = Carbon::parse($closingDate);

            $new_due_date = $due_date->copy()->addMonth();
            $new_closing_date = $closing_date->copy()->addMonth();

            for ($i = $new_due_date->month; $i <= 12; $i++) {
                $invoice = $this->create($new_due_date, $new_closing_date, $year, $new_due_date->month, $creditCardId);
                // Verifico se existe Budget
                $budget = $this->budgetRepository->getOne(['month' => $new_due_date->month, 'year' => $year, 'user_id' => auth()->user()->id]);

                if ($budget) {
                    $this->prepaidCardExtractRepository->store(['budget_id' => $budget->id], $invoice);
                }

                $new_due_date->addMonth();
                $new_closing_date->addMonth();
            }
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
     * @return PrepaidCardExtract
     */
    public function create(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
    ): PrepaidCardExtract {
        $credit_card = $this->creditCardRepository->show($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = $this->prepaidCardExtractRepository->getOne(['year' => $year, 'month' => $month, 'credit_card_id' => $creditCardId]);

        if ($credit_card_invoice) {
            throw new Exception('credit-card-invoice.already-exists');
        }

        return $this->prepaidCardExtractRepository->store([
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
        $credit_card_invoice = $this->prepaidCardExtractRepository->show($id, [
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
            $this->tagRepository->saveTagsToModel($expense, $expense->tags);
            $this->prepaidCardExtractExpenseRepository->delete($expense->id);
        }

        return $this->prepaidCardExtractRepository->delete($credit_card_invoice->id);
    }

    /**
     * Returns data for viewing/editing a Credit Card Invoice
     * @param int $id
     * @return Array
     */
    public function show(int $id): array
    {
        $credit_card_invoice = $this->prepaidCardExtractRepository->show($id, [
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
