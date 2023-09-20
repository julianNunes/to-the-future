<?php
namespace App\Repositories\Contracts;

use App\Models\Provision;
use Illuminate\Support\Collection;

interface ProvisionRepositoryInterface
{
   public function all(): Collection;
}
