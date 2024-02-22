<?php

namespace App\Repositories;

use App\Models\CreditCard;
use App\Repositories\Interfaces\CreditCardRepositoryInterface;

class CreditCardRepository extends AppRepository implements CreditCardRepositoryInterface
{
    public function __construct(?CreditCard $creditCard = null)
    {
        parent::__construct($creditCard ?? new CreditCard);
    }
}
