<?php

namespace App\Services;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\CreditCardInvoiceExpense;
use App\Models\CreditCardInvoiceExpenseDivision;
use App\Models\Tag;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CreditCardInvoiceExpenseService
{
    public function __construct()
    {
    }

    /**
     * Undocumented function
     *
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
        $credit_card = CreditCard::find($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = CreditCardInvoice::find($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_invoice_expense = new CreditCardInvoiceExpense([
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

        $credit_card_invoice_expense->save();

        // Atualiza Tags
        $this->saveTagsToExpense($tags, $credit_card_invoice_expense);

        // Tratativa para Divisão de despesas
        // Removo sempre todos os registros
        CreditCardInvoiceExpenseDivision::where('expense_id', $credit_card_invoice_expense->id)->delete();

        if ($divisions && $divisions->count()) {
            foreach ($divisions as $division) {
                $new_division = new CreditCardInvoiceExpenseDivision([
                    'description' => $division['description'],
                    'value' => $division['value'],
                    'remarks' => $division['remarks'],
                    'share_value' => $division['share_value'],
                    'share_user_id' => $credit_card_invoice_expense->id,
                    'expense_id' => $division['share_user_id'],
                ]);

                $new_division->save();

                // Atualiza Tags
                $this->saveTagsToExpenseDivision($division->tags, $new_division);
            }
        }

        // Atualiza o saldo total da fatura
        $this->recalculateTotalInvoice($invoiceId);

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
                $new_credit_card_invoice = CreditCardInvoice::where(['year' => $new_due_date->year, 'month' => $new_due_date->month])->first();

                // Se não existe, ela é criada
                if (!$new_credit_card_invoice) {
                    $new_credit_card_invoice = new CreditCardInvoice([
                        'due_date' => $new_due_date->format('y-m-d'),
                        'closing_date' => $new_closing_date->format('y-m-d'),
                        'year' => $new_due_date->year,
                        'month' => $new_due_date->month,
                        'credit_card_id' => $creditCardId,
                    ]);

                    $new_credit_card_invoice->save();
                }

                $new_expense = new CreditCardInvoiceExpense([
                    'description' => $description,
                    'date' => $new_date->format('y-m-d'),
                    'value' => $value,
                    'group' => $group,
                    'portion' => $i,
                    'portion_total' => $portionTotal,
                    'remarks' => $remarks,
                    'share_value' => $shareValue,
                    'share_user_id' => $shareUserId,
                    'invoice_id' => $new_credit_card_invoice->id
                ]);

                $new_expense->save();

                // Atualiza Tags
                $this->saveTagsToExpense($tags, $new_expense);

                if ($divisions && $divisions->count()) {
                    foreach ($divisions as $division) {
                        $new_division = new CreditCardInvoiceExpenseDivision([
                            'description' => $division['description'],
                            'value' => $division['value'],
                            'remarks' => $division['remarks'],
                            'share_value' => $division['share_value'],
                            'share_user_id' => $new_expense->id,
                            'expense_id' => $division['share_user_id'],
                        ]);

                        $new_division->save();

                        // Atualiza Tags
                        $this->saveTagsToExpenseDivision($division->tags, $new_division);
                    }
                }

                // Atualiza o saldo total da fatura
                $this->recalculateTotalInvoice($new_credit_card_invoice->id);

                $new_due_date->addMonth();
                $new_closing_date->addMonth();
                $new_date->addMonth();
            }
        }

        return $credit_card_invoice_expense;
    }

    /**
     * Undocumented function
     *
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
        $credit_card = CreditCard::find($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = CreditCardInvoice::find($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_invoice_expense = CreditCardInvoiceExpense::find($id);

        if (!$credit_card_invoice_expense) {
            throw new Exception('credit-card-invoice-expense.not-found');
        }

        $credit_card_invoice_expense->update([
            'description' => $description,
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'group' => $group,
            'portion' => $portion,
            'portion_total' => $portionTotal,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ]);

        // Atualiza Tags
        $this->saveTagsToExpense($tags, $credit_card_invoice_expense);

        // Tratativa para Divisão de despesas
        // Removo sempre todos os registros
        CreditCardInvoiceExpenseDivision::where('expense_id', $credit_card_invoice_expense->id)->delete();

        if ($divisions && $divisions->count()) {
            foreach ($divisions as $division) {
                $new_division = new CreditCardInvoiceExpenseDivision([
                    'description' => $division->description,
                    'value' => $division->value,
                    'remarks' => $division->remarks,
                    'share_value' => $division->share_value,
                    'share_user_id' => $credit_card_invoice_expense->id,
                    'expense_id' => $division->share_user_id,
                ]);

                $new_division->save();

                // Atualiza Tags
                $this->saveTagsToExpenseDivision($division->tags, $new_division);
            }
        }

        // Atualiza o saldo total da fatura
        $this->recalculateTotalInvoice($invoiceId);

        return $credit_card_invoice_expense;
    }

    /**
     * Deleta uma Despesa da Fatura do Cartão de credito
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card_invoice_expense = CreditCardInvoiceExpense::with([
            'divisions' => [
                'tags'
            ],
            'tags'
        ])->find($id);

        if (!$credit_card_invoice_expense) {
            throw new Exception('credit-card-invoice-expense.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card_invoice_expense->divisions as $division) {
            if ($division->tags && $division->tags->count()) {
                $division->tags()->detach();
            }

            $division->delete();
        }

        if ($credit_card_invoice_expense->tags && $credit_card_invoice_expense->tags->count()) {
            $credit_card_invoice_expense->tags()->detach();
        }

        return  $credit_card_invoice_expense->delete();
    }

    /**
     * Deleta todas as Despesas parceladas da Fatura do Cartão de credito
     * @param int $id
     */
    public function deletePortions(int $id): bool
    {
        $credit_card_invoice_expense = CreditCardInvoiceExpense::with(['invoice'])->find($id);

        if (!$credit_card_invoice_expense) {
            throw new Exception('credit-card-invoice-expense.not-found');
        }

        if (!$credit_card_invoice_expense->invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        $credit_card_id = $credit_card_invoice_expense->invoice->credit_card_id;

        // Busca todas as despesas referente a parcela
        $expenses = CreditCardInvoiceExpense::where('portion_total', $credit_card_invoice_expense->portion_total)
            ->where('description', $credit_card_invoice_expense->description)
            ->whereRelation('invoice', 'credit_card_id', '=', $credit_card_id)
            ->with([
                'divisions' => [
                    'tags'
                ],
                'tags',
                'invoice'
            ])
            ->get();

        foreach ($expenses as $expense) {
            // Remove todos os vinculos
            foreach ($expense->divisions as $division) {
                if ($division->tags && $division->tags->count()) {
                    $division->tags()->detach();
                }

                $division->delete();
            }

            if ($expense->tags && $expense->tags->count()) {
                $expense->tags()->detach();
            }

            $expense->delete();
        }

        return  true;
    }

    /**
     * Atualiza o saldo total da fatura
     * @param integer $invoiceId
     * @return void
     */
    protected function recalculateTotalInvoice(int $invoiceId)
    {
        $credit_card_invoice = CreditCardInvoice::find($invoiceId);

        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        // Atualiza o saldo total da fatura
        $expenses = CreditCardInvoiceExpense::where('invoice_id', $invoiceId)->get();

        // Atualiza o saldo total da fatura
        if ($expenses && $expenses->count()) {
            $credit_card_invoice->total = $expenses->sum(function ($item) {
                return floatval($item['value']);
            });
            $credit_card_invoice->save();
        }
    }

    /**
     * Undocumented function
     *
     * @param Collection $tags
     * @param CreditCardInvoiceExpense $creditCardInvoiceExpense
     * @return void
     */
    protected function saveTagsToExpense(Collection $tags, CreditCardInvoiceExpense $creditCardInvoiceExpense)
    {
        if ($tags && $tags->count()) {
            $tags_sync = collect();

            // Busca as Tags no banco
            foreach ($tags as $tag) {
                $new_tag = Tag::where('name', $tag['name'])->first();

                if (!$new_tag) {
                    $new_tag = new Tag([
                        'name' => $tag['name'],
                        'user_id' => auth()->user()->id
                    ]);
                    $new_tag->save();
                }

                $tags_sync->push($new_tag);
            }

            if ($tags_sync->count()) {
                $creditCardInvoiceExpense->tags()->sync($tags_sync->pluck('id')->toArray());
            } else {
                $creditCardInvoiceExpense->tags()->detach();
            }
        } else {
            $creditCardInvoiceExpense->tags()->detach();
        }
    }

    /**
     * Undocumented function
     *
     * @param Collection $tags
     * @param CreditCardInvoiceExpenseDivision $creditCardInvoiceExpenseDivision
     * @return void
     */
    protected function saveTagsToExpenseDivision(
        Collection $tags,
        CreditCardInvoiceExpenseDivision $creditCardInvoiceExpenseDivision
    ) {
        if ($tags && $tags->count()) {
            $tags_sync = collect();

            // Busca as Tags no banco
            foreach ($tags as $tag) {
                $new_tag = Tag::where('name', $tag['name'])->first();

                if (!$new_tag) {
                    $new_tag = new Tag([
                        'name' => $tag['name'],
                        'user_id' => auth()->user()->id
                    ]);
                    $new_tag->save();
                }

                $tags_sync->push($new_tag);
            }

            if ($tags_sync->count()) {
                $creditCardInvoiceExpenseDivision->tags()->sync($tags_sync->pluck('id')->toArray());
            } else {
                $creditCardInvoiceExpenseDivision->tags()->detach();
            }
        } else {
            $creditCardInvoiceExpenseDivision->tags()->detach();
        }
    }
}
