<?php

namespace Database\Factories;

use App\Models\CreditCardInvoiceExpense;
use App\Models\CreditCardInvoiceExpenseDivision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCardInvoiceExpenseDivision>
 */
class CreditCardInvoiceExpenseDivisionFactory extends Factory
{
    protected $model = CreditCardInvoiceExpenseDivision::class;

    public function definition(): array
    {
        return [
            'description' => fake()->sentence(2),
            'value' => fake()->randomFloat(2, 10, 500),
            'remarks' => fake()->optional()->sentence(),
            'share_value' => null,
            'expense_id' => CreditCardInvoiceExpense::factory(),
            'share_user_id' => null,
        ];
    }
}
