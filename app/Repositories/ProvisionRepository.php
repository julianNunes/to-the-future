
<?php

namespace App\Repositories;

use App\Models\Provision;
use App\Repositories\Contracts\ProvisionRepositoryInterface;
use Illuminate\Support\Collection;

class ProvisionRepository extends BaseRepository implements ProvisionRepositoryInterface
{
   /**
    * ProvisionRepository constructor.
    *
    * @param Provision $model
    */
   public function __construct(Provision $model)
   {
       parent::__construct($model);
   }

   /**
    * @return Collection
    */
   public function all(): Collection
   {
       return $this->model->all();
   }
}
