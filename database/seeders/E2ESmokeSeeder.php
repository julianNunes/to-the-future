<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\Financing;
use App\Models\FinancingInstallment;
use App\Models\PrepaidCard;
use App\Models\PrepaidCardExtract;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class E2ESmokeSeeder extends Seeder
{
    public const DEFAULT_EMAIL = 'e2e@example.com';
    public const DEFAULT_PASSWORD = 'password';
    public const DEFAULT_BUDGET_YEAR = '2026';
    public const DEFAULT_BUDGET_MONTH = '05';

    public static function email(): string
    {
        return (string) env('PLAYWRIGHT_E2E_EMAIL', self::DEFAULT_EMAIL);
    }

    public static function password(): string
    {
        return (string) env('PLAYWRIGHT_E2E_PASSWORD', self::DEFAULT_PASSWORD);
    }

    public static function budgetYear(): string
    {
        return str_pad((string) env('PLAYWRIGHT_E2E_BUDGET_YEAR', self::DEFAULT_BUDGET_YEAR), 4, '0', STR_PAD_LEFT);
    }

    public static function budgetMonth(): string
    {
        return str_pad((string) env('PLAYWRIGHT_E2E_BUDGET_MONTH', self::DEFAULT_BUDGET_MONTH), 2, '0', STR_PAD_LEFT);
    }

    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => self::email()],
            [
                'name' => 'E2E Smoke User',
                'email_verified_at' => now(),
                'password' => Hash::make(self::password()),
            ],
        );

        $budget = $this->seedBudget($user);
        $this->seedFinancing($user, $budget);
        $creditCard = $this->seedCreditCard($user);
        $this->seedInvoice($creditCard, $budget);
        $prepaidCard = $this->seedPrepaidCard($user);
        $this->seedExtract($prepaidCard, $budget);
    }

    protected function seedBudget(User $user): Budget
    {
        $attributes = Arr::only(
            Budget::factory()
                ->forPeriod(self::budgetYear(), self::budgetMonth())
                ->make(['user_id' => $user->id])
                ->getAttributes(),
            [
                'year',
                'month',
                'start_week_1',
                'end_week_1',
                'start_week_2',
                'end_week_2',
                'start_week_3',
                'end_week_3',
                'start_week_4',
                'end_week_4',
                'total_expense',
                'total_income',
                'closed',
                'user_id',
            ],
        );

        return Budget::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'year' => self::budgetYear(),
                'month' => self::budgetMonth(),
            ],
            $attributes,
        );
    }

    protected function seedCreditCard(User $user): CreditCard
    {
        $attributes = Arr::only(
            CreditCard::factory()
                ->make([
                    'name' => 'E2E Credit Card',
                    'digits' => '4242',
                    'user_id' => $user->id,
                ])
                ->getAttributes(),
            ['name', 'digits', 'due_date', 'closing_date', 'is_active', 'user_id'],
        );

        return CreditCard::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'E2E Credit Card',
            ],
            $attributes,
        );
    }

    protected function seedFinancing(User $user, Budget $budget): Financing
    {
        $startDate = CarbonImmutable::createFromDate((int) $budget->year, (int) $budget->month, 1)->startOfMonth();

        $financing = Financing::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'description' => 'E2E Financing',
            ],
            [
                'start_date' => $startDate->toDateString(),
                'total' => 18000,
                'fees_monthly' => 1.25,
                'portion_total' => '012',
                'remarks' => 'E2E Smoke Financing',
            ],
        );

        $installments = [
            1 => [
                'value' => 1500,
                'paid_value' => null,
                'date' => $startDate->day(10)->toDateString(),
                'payment_date' => null,
                'paid' => false,
            ],
            2 => [
                'value' => 1500,
                'paid_value' => null,
                'date' => $startDate->addMonth()->day(10)->toDateString(),
                'payment_date' => null,
                'paid' => false,
            ],
            3 => [
                'value' => 1500,
                'paid_value' => null,
                'date' => $startDate->addMonths(2)->day(10)->toDateString(),
                'payment_date' => null,
                'paid' => false,
            ],
        ];

        FinancingInstallment::query()
            ->where('financing_id', $financing->id)
            ->whereNotIn('portion', array_keys($installments))
            ->delete();

        foreach ($installments as $portion => $attributes) {
            FinancingInstallment::query()->updateOrCreate(
                [
                    'financing_id' => $financing->id,
                    'portion' => $portion,
                ],
                $attributes,
            );
        }

        return $financing;
    }

    protected function seedInvoice(CreditCard $creditCard, Budget $budget): CreditCardInvoice
    {
        $attributes = Arr::only(
            CreditCardInvoice::factory()
                ->for($creditCard, 'creditCard')
                ->for($budget, 'budget')
                ->forPeriod(self::budgetYear(), self::budgetMonth())
                ->make([
                    'remarks' => 'E2E Smoke Invoice',
                    'total' => 1200,
                    'total_paid' => 0,
                ])
                ->getAttributes(),
            [
                'due_date',
                'closing_date',
                'year',
                'month',
                'total',
                'total_paid',
                'closed',
                'remarks',
                'credit_card_id',
                'budget_id',
            ],
        );

        return CreditCardInvoice::query()->updateOrCreate(
            [
                'credit_card_id' => $creditCard->id,
                'year' => self::budgetYear(),
                'month' => self::budgetMonth(),
            ],
            $attributes,
        );
    }

    protected function seedPrepaidCard(User $user): PrepaidCard
    {
        $attributes = Arr::only(
            PrepaidCard::factory()
                ->make([
                    'name' => 'E2E Prepaid Card',
                    'digits' => '3030',
                    'user_id' => $user->id,
                ])
                ->getAttributes(),
            ['name', 'digits', 'is_active', 'user_id'],
        );

        return PrepaidCard::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'name' => 'E2E Prepaid Card',
            ],
            $attributes,
        );
    }

    protected function seedExtract(PrepaidCard $prepaidCard, Budget $budget): PrepaidCardExtract
    {
        $attributes = Arr::only(
            PrepaidCardExtract::factory()
                ->for($prepaidCard, 'prepaidCard')
                ->for($budget, 'budget')
                ->forPeriod(self::budgetYear(), self::budgetMonth())
                ->make([
                    'remarks' => 'E2E Smoke Extract',
                    'credit' => 800,
                ])
                ->getAttributes(),
            [
                'year',
                'month',
                'credit',
                'credit_date',
                'remarks',
                'prepaid_card_id',
                'budget_id',
            ],
        );

        return PrepaidCardExtract::query()->updateOrCreate(
            [
                'prepaid_card_id' => $prepaidCard->id,
                'year' => self::budgetYear(),
                'month' => self::budgetMonth(),
            ],
            $attributes,
        );
    }
}
