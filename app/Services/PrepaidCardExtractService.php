<?php

namespace App\Services;

use App\Helpers\ShareUser\Interfaces\ShareUserOptionsInterface;
use App\Models\PrepaidCardExtract;
use App\Repositories\Interfaces\{
    BudgetRepositoryInterface,
    PrepaidCardExtractExpenseRepositoryInterface,
    PrepaidCardExtractRepositoryInterface,
    PrepaidCardRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\PrepaidCardExtractServiceInterface;
use App\Support\Concerns\EnsuresResourceOwnership;
use Carbon\Carbon;
use Exception;

class PrepaidCardExtractService implements PrepaidCardExtractServiceInterface
{
    use EnsuresResourceOwnership;

    public function __construct(
        private PrepaidCardRepositoryInterface $prepaidCardRepository,
        private PrepaidCardExtractRepositoryInterface $prepaidCardExtractRepository,
        private PrepaidCardExtractExpenseRepositoryInterface $prepaidCardExtractExpenseRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserOptionsInterface $shareUserOptions,
        private BudgetRepositoryInterface $budgetRepository,
    ) {}

    /**
     * Returns data for Prepaid Card Extract Management
     * @return Array
     */
    public function index(int $prepaidCardId): array
    {
        $prepaidCard = $this->prepaidCardRepository->show($prepaidCardId, ['extracts']);

        if (!$prepaidCard) {
            throw new Exception('prepaid-card.not-found');
        }

        $this->ensureOwnedByCurrentUser($prepaidCard);

        return [
            'prepaidCard' => $prepaidCard,
            'extracts' => $prepaidCard->extracts,
        ];
    }

    /**
     * Create a new Extract
     * @param integer $prepaidCardId
     * @param string $year
     * @param string $month
     * @param float $credit
     * @param string $creditDate
     * @param string|null $remarks
     * @return PrepaidCardExtract
     */
    public function create(
        int $prepaidCardId,
        string $year,
        string $month,
        float $credit,
        string $creditDate,
        ?string $remarks = null,
    ): PrepaidCardExtract {
        $prepaid_card = $this->prepaidCardRepository->show($prepaidCardId);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        $this->ensureOwnedByCurrentUser($prepaid_card);

        $prepaid_card_extract = $this->prepaidCardExtractRepository->getOne(['year' => $year, 'month' => $month, 'prepaid_card_id' => $prepaidCardId]);

        if ($prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.already-exists');
        }

        $extract = $this->prepaidCardExtractRepository->store([
            'year' => $year,
            'month' => $month,
            'credit' => $credit,
            'credit_date' => Carbon::parse($creditDate)->format('y-m-d'),
            'remarks' => $remarks,
            'prepaid_card_id' => $prepaidCardId,
        ]);

        // Verifico se existe Budget
        $budget = $this->budgetRepository->getOne(['month' => $month, 'year' => $year, 'user_id' => auth()->user()->id]);

        if ($budget) {
            $this->prepaidCardExtractRepository->store(['budget_id' => $budget->id], $extract);
        }

        return $extract;
    }

    /**
     * Update a  Extract
     * @param integer $id
     * @param float $credit
     * @param string|null $remarks
     * @return bool
     */
    public function update(
        int $id,
        float $credit,
        string $creditDate,
        ?string $remarks = null
    ): PrepaidCardExtract {
        $prepaid_card_extract = $this->prepaidCardExtractRepository->show($id);

        if (!$prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.not-found');
        }

        $this->ensureOwnedByCurrentUser(
            $this->prepaidCardRepository->show($prepaid_card_extract->prepaid_card_id)
        );

        return $this->prepaidCardExtractRepository->store([
            'credit' => $credit,
            'credit_date' => Carbon::parse($creditDate)->format('y-m-d'),
            'remarks' => $remarks,
        ], $prepaid_card_extract);
    }

    /**
     * Delete a Extract
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $prepaid_card_extract = $this->prepaidCardExtractRepository->show($id, [
            'expenses' => [
                'tags'
            ]
        ]);

        if (!$prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.not-found');
        }

        $this->ensureOwnedByCurrentUser(
            $this->prepaidCardRepository->show($prepaid_card_extract->prepaid_card_id)
        );

        // Remove todos os vinculos
        foreach ($prepaid_card_extract->expenses as $expense) {
            $this->tagRepository->saveTagsToModel($expense, $expense->tags);
            $this->prepaidCardExtractExpenseRepository->delete($expense->id);
        }

        return $this->prepaidCardExtractRepository->delete($prepaid_card_extract->id);
    }

    /**
     * Returns data for viewing/editing a Prepaid Card Extract
     * @param int $id
     * @return Array
     */
    public function show(int $id): array
    {
        $prepaid_card_extract = $this->prepaidCardExtractRepository->show($id, [
            'prepaidCard',
            'expenses' => [
                'tags',
                'shareUser',
            ]
        ]);

        if (!$prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.not-found');
        }

        $this->ensureOwnedByCurrentUser($prepaid_card_extract->prepaidCard, 'user_id');

        $shareUsers = $this->shareUserOptions->resolveForUser(auth()->user()->id)['options'];

        return [
            'extract' => $prepaid_card_extract,
            'shareUsers' => $shareUsers,
        ];
    }
}
