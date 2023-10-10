<?php

namespace App\Services;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\CreditCardInvoiceExpense;
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
        Collection $tags = null
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
                $credit_card_invoice_expense->tags()->sync($tags_sync->pluck('id')->toArray());
            } else {
                $credit_card_invoice_expense->tags()->detach();
            }
        }

        $expenses = CreditCardInvoiceExpense::where('invoice_id', $invoiceId)->get();

        // Atualiza o saldo total da fatura
        if ($expenses && $expenses->count()) {
            $credit_card_invoice->total = $expenses->sum('total');
            $credit_card_invoice->save();
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
        Collection $tags = null
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
                $credit_card_invoice_expense->tags()->sync($tags_sync->pluck('id')->toArray());
            } else {
                $credit_card_invoice_expense->tags()->detach();
            }
        }

        // Atualiza o saldo total da fatura
        $expenses = CreditCardInvoiceExpense::where('invoice_id', $invoiceId)->get();

        if ($expenses && $expenses->count()) {
            $credit_card_invoice->total = $expenses->sum('total');
            $credit_card_invoice->save();
        }

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
}
