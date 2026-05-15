<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    protected $model = Budget::class;

    public function definition(): array
    {
        $referenceDate = Carbon::create(
            (int) fake()->numberBetween(2025, 2027),
            (int) fake()->numberBetween(1, 12),
            1,
        );

        return $this->periodState($referenceDate) + [
            'total_expense' => 0,
            'total_income' => 0,
            'closed' => false,
            'user_id' => User::factory(),
        ];
    }

    public function forPeriod(int|string $year, int|string $month): static
    {
        $referenceDate = Carbon::create((int) $year, (int) $month, 1);

        return $this->state(fn () => $this->periodState($referenceDate));
    }

    protected function periodState(Carbon $referenceDate): array
    {
        $monthStart = $referenceDate->copy()->startOfMonth();

        return [
            'year' => $referenceDate->format('Y'),
            'month' => $referenceDate->format('m'),
            'start_week_1' => $monthStart->copy()->toDateString(),
            'end_week_1' => $monthStart->copy()->addDays(6)->toDateString(),
            'start_week_2' => $monthStart->copy()->addDays(7)->toDateString(),
            'end_week_2' => $monthStart->copy()->addDays(13)->toDateString(),
            'start_week_3' => $monthStart->copy()->addDays(14)->toDateString(),
            'end_week_3' => $monthStart->copy()->addDays(20)->toDateString(),
            'start_week_4' => $monthStart->copy()->addDays(21)->toDateString(),
            'end_week_4' => $referenceDate->copy()->endOfMonth()->toDateString(),
        ];
    }
}
