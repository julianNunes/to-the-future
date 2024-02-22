<?php

namespace App\Repositories;

use App\Models\CreditCardInvoiceFile;
use App\Repositories\Interfaces\CreditCardInvoiceFileRepositoryInterface;

class CreditCardInvoiceFileRepository extends AppRepository implements CreditCardInvoiceFileRepositoryInterface
{
    public function __construct(?CreditCardInvoiceFile $creditCardInvoiceFile = null)
    {
        parent::__construct($creditCardInvoiceFile ?? new CreditCardInvoiceFile);
    }
}
