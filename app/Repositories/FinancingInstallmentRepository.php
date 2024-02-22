<?php

namespace App\Repositories;

use App\Models\FinancingInstallment;
use App\Repositories\Interfaces\FinancingInstallmentRepositoryInterface;

class FinancingInstallmentRepository extends AppRepository implements FinancingInstallmentRepositoryInterface
{
    public function __construct(?FinancingInstallment $financingInstallment = null)
    {
        parent::__construct($financingInstallment ?? new FinancingInstallment);
    }
}
