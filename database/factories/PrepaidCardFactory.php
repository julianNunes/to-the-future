<?php

namespace Database\Factories;

use App\Models\PrepaidCard;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrepaidCard>
 */
class PrepaidCardFactory extends Factory
{
    protected $model = PrepaidCard::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Nomad', 'Wise', 'Inter']) . ' ' . fake()->numerify('####'),
            'digits' => fake()->numerify('####'),
            'is_active' => true,
            'user_id' => User::factory(),
        ];
    }
}
