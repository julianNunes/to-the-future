<?php

namespace App\Repositories;

use App\Models\Financing;
use App\Repositories\Interfaces\FinancingRepositoryInterface;

class FinancingRepository extends AppRepository implements FinancingRepositoryInterface
{
    public function __construct(?Financing $financing = null)
    {
        parent::__construct($financing ?? new Financing);
    }
}
