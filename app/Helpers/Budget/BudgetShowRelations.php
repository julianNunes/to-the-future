<?php

namespace App\Helpers\Budget;

use App\Helpers\Budget\Interfaces\BudgetShowRelationsInterface;

class BudgetShowRelations implements BudgetShowRelationsInterface
{
    public function relations(): array
    {
        return [
            'expenses' => [
                'tags',
                'shareUser',
            ],
            'incomes.tags',
            'provisions' => [
                'tags',
                'shareUser',
            ],
            'goals.tags',
            'expenseTagOptions.tags',
            'invoices' => [
                'creditCard',
                'file',
                'expenses' => [
                    'tags',
                    'shareUser',
                    'divisions' => [
                        'tags',
                        'shareUser',
                    ],
                ],
            ],
            'extracts' => [
                'prepaidCard',
                'expenses' => [
                    'tags',
                    'shareUser',
                ],
            ],
        ];
    }
}
