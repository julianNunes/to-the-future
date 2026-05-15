<?php

namespace Database\Factories;

use App\Models\CreditCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CreditCard>
 */
class CreditCardFactory extends Factory
{
    protected $model = CreditCard::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Visa', 'Mastercard', 'Nubank']) . ' ' . fake()->numerify('####'),
            'digits' => fake()->numerify('####'),
            'due_date' => '10',
            'closing_date' => '05',
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }
}
