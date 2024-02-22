<?php

namespace App\Services;

use App\Models\FinancingInstallment;
use App\Repositories\Interfaces\FinancingInstallmentRepositoryInterface;
use App\Repositories\Interfaces\FinancingRepositoryInterface;
use App\Services\Interfaces\FinancingInstallmentServiceInterface;
use Exception;
use Illuminate\Support\Carbon;

class FinancingInstallmentService implements FinancingInstallmentServiceInterface
{
    public function __construct(
        private FinancingRepositoryInterface $financingRepository,
        private FinancingInstallmentRepositoryInterface $financingInstallmentRepository
    ) {
    }

    /**
     * Returns data for Financing Installment Management
     * @return Array
     */
    public function index(int $financingId): array
    {
        $financing = $this->financingRepository->show($financingId, ['installments']);
        return [
            'financing' => $financing,
            'installments' => $financing->installments,
        ];
    }

    /**
     * Update a Installment of Financing
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
    ): FinancingInstallment {
        $installment = $this->financingInstallmentRepository->show($id);

        if (!$installment) {
            throw new Exception('financing-installment.not-found');
        }

        return $this->financingInstallmentRepository->store([
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'paid' => $paid,
            'payment_date' => $paymentDate ? Carbon::parse($paymentDate)->format('y-m-d') : null,
            'paid_value' => $paidValue,
        ], $installment);
    }
}
