<?php

namespace App\Services;

use App\Models\FinancingInstallment;
use App\Models\Financing;
use App\Models\ShareUser;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class FinancingInstallmentService
{
    public function __construct()
    {
    }

    /**
     * Retorna os dados para o Gerenciamento das Parcelas do Financiamento
     * @return Array
     */
    public function index(int $financingId): array
    {
        $financing = Financing::with('installments')->find($financingId);

        return [
            'financing' => $financing,
            'installments' => $financing->installments,
        ];
    }

    /**
     *
     * @param integer $id
     * @param string $date
     * @param float $value
     * @param boolean $paid
     * @param string|null $paymentDate
     * @param float|null $paidValue
     * @return boolean
     */
    public function update(
        int $id,
        string $date,
        float $value,
        bool $paid,
        string $paymentDate = null,
        float $paidValue = null,
    ): bool {
        $installment = FinancingInstallment::find($id);

        if (!$installment) {
            throw new Exception('financing-installment.not-found');
        }

        return $installment->update([
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'paid' => $paid,
            'payment_date' => $paymentDate ? Carbon::parse($paymentDate)->format('y-m-d') : null,
            'paid_value' => $paidValue,
        ]);
    }

    /**
     * Deleta uma fatura do cartão de credito
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card_invoice = FinancingInstallment::with([
            'file',
            'expenses' => [
                'divisions' => [
                    'tags'
                ],
                'tags'
            ]
        ])->find($id);


        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card_invoice->expenses as $expense) {
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

        if ($credit_card_invoice->file) {
            $credit_card_invoice->file->delete();
        }

        return $credit_card_invoice->delete();
    }
}
