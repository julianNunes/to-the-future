<?php

namespace App\Services;

use App\Models\Financing;
use App\Models\FinancingInstallment;
use Illuminate\Support\Carbon;
use Exception;

class FinancingService
{
    public function __construct()
    {
    }

    /**
     * Retorna os dados para o Gerenciamento de Financiamentos
     * @return Array
     */
    public function index(): array
    {
        $financings = Financing::where('user_id', auth()->user()->id)->get();

        return [
            'financings' => $financings,
        ];
    }

    /**
     * Undocumented function
     *
     * @param string $description
     * @param string $startDate
     * @param float $total
     * @param float $feesMonthly
     * @param integer $portionTotal
     * @param string $remarks
     * @param string $startDateInstallment
     * @param float $valueInstallment
     * @return Financing
     */
    public function create(
        string $description,
        string $startDate,
        float $total,
        float $feesMonthly,
        int $portionTotal,
        string $remarks,
        string $startDateInstallment,
        float $valueInstallment
    ): Financing {
        $financing = new Financing([
            'description' => $description,
            'start_date' => Carbon::parse($startDate)->format('y-m-d'),
            'total' => $total,
            'feesMonthly' => $feesMonthly,
            'portionTotal' => $portionTotal,
            'remarks' => $remarks,
            'user_id' => auth()->user()->id
        ]);

        $financing->save();

        $start_date = Carbon::parse($startDateInstallment);
        for ($i = 1; $i <= $portionTotal; $i++) {
            $installment = new FinancingInstallment([
                'value' => $valueInstallment,
                'portion' => $i,
                'date' => $start_date->format('Y-m-d'),
                'paid' => false,
                'financing_id' => $financing->id,
            ]);

            $installment->save();
            $start_date->addMonth(1);
        }

        return $financing;
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @param string $description
     * @param string $startDate
     * @param float $total
     * @param float $feesMonthly
     * @param string $remarks
     * @param float $valueInstallment
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        string $startDate,
        float $total,
        float $feesMonthly,
        string $remarks,
        float $valueInstallment
    ): bool {
        $financing = Financing::with('installments')->find($id);

        if (!$financing) {
            throw new Exception('financing.not-found');
        }

        if ($valueInstallment) {
            foreach ($financing->installments as $installment) {
                if (!$installment->paid) {
                    $installment->update(['value' => $valueInstallment]);
                }
            }
        }

        return $financing->update([
            'description' => $description,
            'start_date' => Carbon::parse($startDate)->format('y-m-d'),
            'total' => $total,
            'feesMonthly' => $feesMonthly,
            'remarks' => $remarks,
        ]);
    }

    /**
     * Deleta um Financiamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $financing = Financing::with(['installments'])->find($id);

        if (!$financing) {
            throw new Exception('financing.not-found');
        }

        // Remove todos os vinculos
        foreach ($financing->installments as $installment) {
            $installment->delete();
        }

        return $financing->delete();
    }
}
