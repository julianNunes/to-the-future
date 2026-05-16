<?php

namespace Database\Factories;

use App\Models\CreditCardInvoice;
use App\Models\CreditCardInvoiceExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCardInvoiceExpense>
 */
class CreditCardInvoiceExpenseFactory extends Factory
{
    protected $model = CreditCardInvoiceExpense::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence(3),
            'date' => $this->faker->date('Y-m-d'),
            'value' => $this->faker->randomFloat(2, 10, 1000),
            'group' => $this->faker->randomElement(['PORTION', 'WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4']),
            'group_portion' => null,
            'portion' => null,
            'portion_total' => null,
            'remarks' => $this->faker->optional()->sentence(),
            'share_value' => null,
            'invoice_id' => CreditCardInvoice::factory(),
            'share_user_id' => null,
        ];
    }
}
