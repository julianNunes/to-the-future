<?php

namespace Database\Factories;

use App\Models\PrepaidCard;
use App\Models\PrepaidCardExtract;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<PrepaidCardExtract>
 */
class PrepaidCardExtractFactory extends Factory
{
    protected $model = PrepaidCardExtract::class;

    public function definition(): array
    {
        $referenceDate = Carbon::create(
            (int) fake()->numberBetween(2025, 2027),
            (int) fake()->numberBetween(1, 12),
            1,
        );

        return $this->periodState($referenceDate) + [
            'credit' => fake()->randomFloat(2, 100, 5000),
            'remarks' => fake()->sentence(),
            'prepaid_card_id' => PrepaidCard::factory(),
            'budget_id' => null,
        ];
    }

    public function forPeriod(int|string $year, int|string $month): static
    {
        $referenceDate = Carbon::create((int) $year, (int) $month, 1);

        return $this->state(fn () => $this->periodState($referenceDate));
    }

    protected function periodState(Carbon $referenceDate): array
    {
        return [
            'year' => $referenceDate->format('Y'),
            'month' => $referenceDate->format('m'),
            'credit_date' => $referenceDate->copy()->day(5)->toDateString(),
        ];
    }
}
