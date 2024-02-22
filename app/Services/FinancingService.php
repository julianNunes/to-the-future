<?php

namespace App\Services;

use App\Models\Financing;
use App\Models\FinancingInstallment;
use App\Repositories\Interfaces\FinancingInstallmentRepositoryInterface;
use App\Repositories\Interfaces\FinancingRepositoryInterface;
use App\Services\Interfaces\FinancingServiceInterface;
use Illuminate\Support\Carbon;
use Exception;

class FinancingService implements FinancingServiceInterface
{
    public function __construct(
        private FinancingRepositoryInterface $financingRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository
    ) {
    }

    /**
     * Returns data to Financial Management
     * @return Array
     */
    public function index(): array
    {
        $financings = $this->financingRepository->get(['user_id' => auth()->user()->id], [], [], []);
        return [
            'financings' => $financings,
        ];
    }

    /**
     * Create a new Financing amd your installments
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
        string $startDateInstallment,
        float $valueInstallment,
        string $remarks = null
    ): Financing {
        $financing = $this->financingRepository->store([
            'description' => $description,
            'start_date' => Carbon::parse($startDate)->format('y-m-d'),
            'total' => $total,
            'fees_monthly' => $feesMonthly,
            'portion_total' => $portionTotal,
            'remarks' => $remarks,
            'user_id' => auth()->user()->id
        ]);

        $start_date = Carbon::parse($startDateInstallment);
        for ($i = 1; $i <= $portionTotal; $i++) {
            $this->financingInstallmentRepository->store([
                'value' => $valueInstallment,
                'portion' => $i,
                'date' => $start_date->format('Y-m-d'),
                'paid' => false,
                'financing_id' => $financing->id,
            ]);

            $start_date->addMonth(1);
        }

        return $financing;
    }

    /**
     * Edit a Financing. Only some data are available.
     * Updating of installments will only occur in installments that are open
     * @param integer $id
     * @param string $description
     * @param string $startDate
     * @param float $total
     * @param float $feesMonthly
     * @param string $remarks
     * @param float|null $valueInstallment
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        string $startDate,
        float $total,
        float $feesMonthly,
        float $valueInstallment = null,
        string $remarks = null
    ): Financing {
        $financing = $this->financingRepository->show($id, ['installments']);

        if (!$financing) {
            throw new Exception('financing.not-found');
        }

        if ($valueInstallment) {
            foreach ($financing->installments as $installment) {
                if (!$installment->paid) {
                    $this->financingInstallmentRepository->store(['value' => $valueInstallment], $installment);
                }
            }
        }

        return $this->financingRepository->store([
            'description' => $description,
            'start_date' => Carbon::parse($startDate)->format('y-m-d'),
            'total' => $total,
            'feesMonthly' => $feesMonthly,
            'remarks' => $remarks,
        ], $financing);
    }

    /**
     * Deleta a Financing
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $financing = $this->financingRepository->show($id, ['installments']);

        if (!$financing) {
            throw new Exception('financing.not-found');
        }

        // Remove todos os vinculos
        foreach ($financing->installments as $installment) {
            $this->financingInstallmentRepository->delete($installment->id);
        }

        return $this->financingRepository->delete($id);
    }
}
