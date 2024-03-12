<?php

namespace App\Repositories;

use App\Models\PrepaidCardExtract;
use App\Repositories\Interfaces\PrepaidCardExtractRepositoryInterface;

class PrepaidCardExtractRepository extends AppRepository implements PrepaidCardExtractRepositoryInterface
{
    public function __construct(?PrepaidCardExtract $prepaidCardExtract = null)
    {
        parent::__construct($prepaidCardExtract ?? new PrepaidCardExtract);
    }
}
