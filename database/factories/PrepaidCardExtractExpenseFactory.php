<?php

namespace Database\Factories;

use App\Models\PrepaidCardExtract;
use App\Models\PrepaidCardExtractExpense;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<PrepaidCardExtractExpense>
 */
class PrepaidCardExtractExpenseFactory extends Factory
{
    protected $model = PrepaidCardExtractExpense::class;

    public function definition(): array
    {
        return [
            'description' => $this->faker->words(3, true),
            'date' => Carbon::instance($this->faker->dateTimeBetween('-1 year', 'now'))->toDateString(),
            'value' => $this->faker->randomFloat(2, 10, 500),
            'group' => $this->faker->randomElement(['WEEK_1', 'WEEK_2', 'WEEK_3', 'WEEK_4']),
            'remarks' => $this->faker->optional()->sentence(),
            'share_value' => null,
            'share_user_id' => null,
            'extract_id' => PrepaidCardExtract::factory(),
        ];
    }
}
