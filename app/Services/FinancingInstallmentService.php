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
    public function index(int $id): array
    {
        $financing = Financing::with('installments')->find($id);

        return [
            'financing' => $financing,
            // 'installments' => $financing->installments,
        ];
    }


    public function update(
        int $id,
        string $date,
        float $value,
        float $paidValue = null,
        bool $paid,
    ): FinancingInstallment {
        $installment = FinancingInstallment::find($id);

        if (!$installment) {
            throw new Exception('financing-installment.not-found');
        }

        return $installment->update([
            'start_date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'paidValue' => $paidValue,
            'paid' => $paid,
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
