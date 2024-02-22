<?php

namespace App\Repositories;

use App\Models\Provision;
use App\Repositories\Interfaces\ProvisionRepositoryInterface;

class ProvisionRepository extends AppRepository implements ProvisionRepositoryInterface
{
    public function __construct(?Provision $provision = null)
    {
        parent::__construct($provision ?? new Provision);
    }
}
