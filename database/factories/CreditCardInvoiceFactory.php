<?php

namespace Database\Factories;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CreditCardInvoice>
 */
class CreditCardInvoiceFactory extends Factory
{
    protected $model = CreditCardInvoice::class;

    public function definition(): array
    {
        $referenceDate = Carbon::create(
            (int) fake()->numberBetween(2025, 2027),
            (int) fake()->numberBetween(1, 12),
            1,
        );

        return $this->periodState($referenceDate) + [
            'total' => fake()->randomFloat(2, 100, 5000),
            'total_paid' => 0,
            'closed' => false,
            'remarks' => fake()->sentence(),
            'credit_card_id' => CreditCard::factory(),
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
            'due_date' => $referenceDate->copy()->day(10)->toDateString(),
            'closing_date' => $referenceDate->copy()->day(5)->toDateString(),
            'year' => $referenceDate->format('Y'),
            'month' => $referenceDate->format('m'),
        ];
    }
}
