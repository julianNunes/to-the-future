<?php

namespace App\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Models\CreditCardInvoiceExpense;
use App\Repositories\Interfaces\{
    CreditCardInvoiceExpenseDivisionRepositoryInterface,
    CreditCardInvoiceExpenseRepositoryInterface,
    CreditCardInvoiceFileRepositoryInterface,
    CreditCardInvoiceRepositoryInterface,
    CreditCardRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\CreditCardInvoiceExpenseServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CreditCardInvoiceExpenseService implements CreditCardInvoiceExpenseServiceInterface
{
    public function __construct(
        private CreditCardRepositoryInterface $creditCardRepository,
        private CreditCardInvoiceRepositoryInterface $creditCardInvoiceRepository,
        private CreditCardInvoiceExpenseRepositoryInterface $creditCardInvoiceExpenseRepository,
        private CreditCardInvoiceExpenseDivisionRepositoryInterface $creditCardInvoiceExpenseDivisionRepository,
        private CreditCardInvoiceFileRepositoryInterface $creditCardInvoiceFileRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
        private BudgetCalculateInterface $budgetCalculate
    ) {
    }

    /**
     * Create new Expense to Invoice and your portions
     * @param integer $creditCardId
     * @param integer $invoiceId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param integer|null $portion
     * @param integer|null $portionTotal
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @param Collection|null $divisions
     * @return CreditCardInvoiceExpense
     */
    public function createWithPortions(
        int $creditCardId,
        int $invoiceId,
        string $description,
        string $date,
        float $value,
        string $group,
        int $portion,
        int $portionTotal,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
        Collection $divisions = null
    ): CreditCardInvoiceExpense {
        $credit_card = $this->creditCardRepository->show($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = $this->creditCardInvoiceRepository->show($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_invoice_expense = $this->create(
            $creditCardId,
            $invoiceId,
            $description,
            $date,
            $value,
            $group,
            $portion,
            $portionTotal,
            $remarks,
            $shareValue,
            $shareUserId,
            $tags,
            $divisions,
        );

        // Crio as outras parcelas
        if ($portionTotal && $portionTotal > 1) {
            $due_date = Carbon::parse($credit_card_invoice->due_date);
            $closing_date = Carbon::parse($credit_card_invoice->closing_date);
            $new_date = Carbon::parse($credit_card_invoice_expense->date);

            $new_due_date = $due_date->copy()->addMonth();
            $new_closing_date = $closing_date->copy()->addMonth();
            $new_date = $new_date->copy()->addMonth();

            for ($i = ($portion + 1); $i <= $portionTotal; $i++) {
                // Busca a fatura do mes seguinte
                $new_credit_card_invoice = $this->creditCardInvoiceRepository->getOne(['year' => $new_due_date->year, 'month' => $new_due_date->month, 'credit_card_id' => $creditCardId]);

                // Se não existe, ela é criada
                if (!$new_credit_card_invoice) {
                    $new_credit_card_invoice = $this->creditCardInvoiceRepository->store([
                        'due_date' => $new_due_date->format('y-m-d'),
                        'closing_date' => $new_closing_date->format('y-m-d'),
                        'year' => $new_due_date->year,
                        'month' => $new_due_date->format('m'),
                        'credit_card_id' => $creditCardId,
                    ]);
                }

                $credit_card_invoice_expense = $this->create(
                    $creditCardId,
                    $new_credit_card_invoice->id,
                    $description,
                    $new_due_date,
                    $value,
                    $group,
                    $i,
                    $portionTotal,
                    $remarks,
                    $shareValue,
                    $shareUserId,
                    $tags,
                    $divisions
                );

                $new_due_date->addMonth();
                $new_closing_date->addMonth();
                $new_date->addMonth();
            }
        }

        return $credit_card_invoice_expense;
    }

    /**
     * Create new Expense to Invoice
     * @param integer $creditCardId
     * @param integer $invoiceId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param integer|null $portion
     * @param integer|null $portionTotal
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @param Collection|null $divisions
     * @return CreditCardInvoiceExpense
     */
    public function create(
        int $creditCardId,
        int $invoiceId,
        string $description,
        string $date,
        float $value,
        string $group,
        int $portion = null,
        int $portionTotal = null,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
        Collection $divisions = null
    ): CreditCardInvoiceExpense {
        $credit_card = $this->creditCardRepository->show($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = $this->creditCardInvoiceRepository->show($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_invoice_expense = $this->creditCardInvoiceExpenseRepository->store([
            'description' => $description,
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'group' => $group,
            'portion' => $portion,
            'portion_total' => $portionTotal,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'invoice_id' => $invoiceId
        ]);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($credit_card_invoice_expense, $tags);

        // Tratativa para Divisão de despesas
        // Removo sempre todos os registros
        $this->creditCardInvoiceExpenseDivisionRepository->deleteDivisions($credit_card_invoice_expense->id);

        if ($divisions && $divisions->count()) {
            foreach ($divisions as $division) {
                $new_division = $this->creditCardInvoiceExpenseDivisionRepository->store([
                    'description' => $division['description'],
                    'value' => $division['value'],
                    'remarks' => $division['remarks'],
                    'share_value' => $division['share_value'],
                    'share_user_id' => $division['share_user_id'],
                    'expense_id' => $credit_card_invoice_expense->id,
                ]);

                // Atualiza Tags
                $this->tagRepository->saveTagsToModel($new_division, collect($division['tags']));
            }
        }

        // Atualiza o saldo total da fatura
        $this->recalculateTotalInvoice($invoiceId);

        // Atualiza Orçamento
        if ($credit_card_invoice->budget_id) {
            $this->budgetCalculate->recalculate($credit_card_invoice->budget_id, $shareUserId ? true : false);
        }

        return $credit_card_invoice_expense;
    }

    /**
     * Update a Expense
     * @param integer $id
     * @param integer $creditCardId
     * @param integer $invoiceId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param integer|null $portion
     * @param integer|null $portionTotal
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @param Collection|null $divisions
     * @return CreditCardInvoiceExpense
     */
    public function update(
        int $id,
        int $creditCardId,
        int $invoiceId,
        string $description,
        string $date,
        float $value,
        string $group,
        int $portion = null,
        int $portionTotal = null,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
        Collection $divisions = null,
    ): CreditCardInvoiceExpense {
        $credit_card = $this->creditCardRepository->show($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = $this->creditCardInvoiceRepository->show($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_invoice_expense = $this->creditCardInvoiceExpenseRepository->show($id);

        if (!$credit_card_invoice_expense) {
            throw new Exception('credit-card-invoice-expense.not-found');
        }

        $change_share_user = ($shareUserId != $credit_card_invoice_expense->share_user_id) || ($shareValue != $credit_card_invoice_expense->share_value) ? true : false;

        $credit_card_invoice_expense = $this->creditCardInvoiceExpenseRepository->store([
            'description' => $description,
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'group' => $group,
            'portion' => $portion,
            'portion_total' => $portionTotal,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ], $credit_card_invoice_expense);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($credit_card_invoice_expense, $tags);

        // Tratativa para Divisão de despesas
        // Removo sempre todos os registros
        $this->creditCardInvoiceExpenseDivisionRepository->deleteDivisions($credit_card_invoice_expense->id);

        if ($divisions && $divisions->count()) {
            foreach ($divisions as $division) {
                $new_division = $this->creditCardInvoiceExpenseDivisionRepository->store([
                    'description' => $division['description'],
                    'value' => $division['value'],
                    'remarks' => $division['remarks'],
                    'share_value' => $division['share_value'],
                    'share_user_id' => $division['share_user_id'],
                    'expense_id' => $credit_card_invoice_expense->id,
                ]);

                // Atualiza Tags
                $this->tagRepository->saveTagsToModel($new_division, collect($division['tags']));
            }
        }

        // Atualiza o saldo total da fatura
        $this->recalculateTotalInvoice($invoiceId);

        // Atualiza Orçamento
        if ($credit_card_invoice->budget_id) {
            $this->budgetCalculate->recalculate($credit_card_invoice->budget_id, $change_share_user);
        }

        return $credit_card_invoice_expense;
    }

    /**
     * Delete a Expense
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card_invoice_expense = $this->creditCardInvoiceExpenseRepository->show($id, [
            'invoice:id,budget_id',
            'divisions' => [
                'tags'
            ],
            'tags'
        ]);

        if (!$credit_card_invoice_expense) {
            throw new Exception('credit-card-invoice-expense.not-found');
        }

        $invoice_id = $credit_card_invoice_expense->invoice_id;
        $budget_id = $credit_card_invoice_expense->invoice->budget_id;
        $share_user_id = $credit_card_invoice_expense->share_user_id;

        // Remove todos os vinculos
        foreach ($credit_card_invoice_expense->divisions as $division) {
            $this->tagRepository->saveTagsToModel($division, $division->tags);
            $this->creditCardInvoiceExpenseDivisionRepository->delete($division->id);
        }

        $this->tagRepository->saveTagsToModel($credit_card_invoice_expense, $credit_card_invoice_expense->tags);
        $this->creditCardInvoiceExpenseRepository->delete($credit_card_invoice_expense->id);

        // Atualiza o saldo total da fatura
        $this->recalculateTotalInvoice($invoice_id);

        // Atualiza Orçamento
        if ($budget_id) {
            $this->budgetCalculate->recalculate($budget_id, $share_user_id ? true : false);
        }

        return true;
    }

    /**
     * Delete all Expense portions of a Invoice Credit Card
     * @param int $id
     * @return bool
     */
    public function deletePortions(int $id): bool
    {
        $credit_card_invoice_expense = $this->creditCardInvoiceExpenseRepository->show($id, ['invoice']);

        if (!$credit_card_invoice_expense) {
            throw new Exception('credit-card-invoice-expense.not-found');
        }

        if (!$credit_card_invoice_expense->invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_id = $credit_card_invoice_expense->invoice->credit_card_id;
        $portion_total = $credit_card_invoice_expense->portion_total;
        $description = $credit_card_invoice_expense->description;

        // Busca todas as despesas referente a parcela
        $expenses = $this->creditCardInvoiceExpenseRepository->get(function (Builder $query) use ($credit_card_id, $portion_total, $description) {
            $query->where('portion_total', $portion_total)
                ->where('description', $description)
                ->whereRelation('invoice', 'credit_card_id', '=', $credit_card_id);
        });

        foreach ($expenses as $expense) {
            $this->delete($expense->id);
        }

        return  true;
    }

    /**
     * Update Invoice total value
     * @param integer $invoiceId
     * @return void
     */
    public function recalculateTotalInvoice(int $invoiceId)
    {
        $credit_card_invoice = $this->creditCardInvoiceRepository->show($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        // Atualiza o saldo total da fatura
        $expenses = $this->creditCardInvoiceExpenseRepository->get(['invoice_id' => $invoiceId]);

        // Atualiza o saldo total da fatura
        if ($expenses && $expenses->count()) {
            $total = $expenses->sum(function ($item) {
                return floatval($item['value']);
            });
            $this->creditCardInvoiceRepository->store(['total' => $total], $credit_card_invoice);
        }
    }

    /**
     *
     * @param integer $id
     * @param Collection $data
     * @return boolean
     */
    public function storeImportExcel(int $invoiceId, Collection $data): bool
    {
        $credit_card_invoice = $this->creditCardInvoiceRepository->show($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-inovice.not-found');
        }

        foreach ($data as $expense) {
            if ($expense['portion'] && intval($expense['portion']) > 0) {
                $this->createWithPortions(
                    $credit_card_invoice->credit_card_id,
                    $credit_card_invoice->id,
                    $expense['description'],
                    $expense['date'],
                    floatval($expense['value']),
                    $expense['group'],
                    intval($expense['portion']),
                    intval($expense['portion_total']),
                    $expense['remarks'],
                    $expense['share_value'] ? floatval($expense['share_value']) : null,
                    $expense['share_user_id'] ? intval($expense['share_user_id']) : null,
                    collect($expense['tags'])
                );
            } else {
                $this->create(
                    $credit_card_invoice->credit_card_id,
                    $credit_card_invoice->id,
                    $expense['description'],
                    $expense['date'],
                    floatval($expense['value']),
                    $expense['group'],
                    intval($expense['portion']),
                    intval($expense['portion_total']),
                    $expense['remarks'],
                    $expense['share_value'] ? floatval($expense['share_value']) : null,
                    $expense['share_user_id'] ? intval($expense['share_user_id']) : null,
                    collect($expense['tags'])
                );
            }
        }

        return true;
    }
}
