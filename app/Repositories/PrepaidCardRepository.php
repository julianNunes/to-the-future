<?php

namespace App\Repositories;

use App\Models\PrepaidCard;
use App\Repositories\Interfaces\PrepaidCardRepositoryInterface;

class PrepaidCardRepository extends AppRepository implements PrepaidCardRepositoryInterface
{
    public function __construct(?PrepaidCard $prepaidCard = null)
    {
        parent::__construct($prepaidCard ?? new PrepaidCard);
    }
}
