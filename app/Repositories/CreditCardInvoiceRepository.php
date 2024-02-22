<?php

namespace App\Repositories;

use App\Models\CreditCardInvoice;
use App\Repositories\Interfaces\CreditCardInvoiceRepositoryInterface;

class CreditCardInvoiceRepository extends AppRepository implements CreditCardInvoiceRepositoryInterface
{
    public function __construct(?CreditCardInvoice $creditCardInvoice = null)
    {
        parent::__construct($creditCardInvoice ?? new CreditCardInvoice);
    }
}
