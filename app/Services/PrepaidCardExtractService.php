<?php

namespace App\Services;

use App\Models\PrepaidCardExtract;
use App\Repositories\Interfaces\{
    BudgetRepositoryInterface,
    PrepaidCardExtractExpenseRepositoryInterface,
    PrepaidCardExtractRepositoryInterface,
    CreditCardRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\PrepaidCardExtractServiceInterface;
use Exception;

class PrepaidCardExtractService implements PrepaidCardExtractServiceInterface
{
    public function __construct(
        private CreditCardRepositoryInterface $prepaidCardRepository,
        private PrepaidCardExtractRepositoryInterface $prepaidCardExtractRepository,
        private PrepaidCardExtractExpenseRepositoryInterface $prepaidCardExtractExpenseRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
        private BudgetRepositoryInterface $budgetRepository,
    ) {
    }

    /**
     * Returns data for Prepaid Card Extract Management
     * @return Array
     */
    public function index(int $prepaidCardId): array
    {
        $prepaidCard = $this->prepaidCardRepository->show($prepaidCardId, ['extracts']);
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
     * @param float $balance
     * @param string|null $remarks
     * @return PrepaidCardExtract
     */
    public function create(
        int $prepaidCardId,
        string $year,
        string $month,
        float $balance,
        string $remarks = null,
    ): PrepaidCardExtract {
        $prepaid_card = $this->prepaidCardRepository->show($prepaidCardId);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        $prepaid_card_extract = $this->prepaidCardExtractRepository->getOne(['year' => $year, 'month' => $month, 'credit_card_id' => $prepaidCardId]);

        if ($prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.already-exists');
        }

        return $this->prepaidCardExtractRepository->store([
            'year' => $year,
            'month' => $month,
            'balance' => $balance,
            'remarks' => $remarks,
            'prepaid_card_id' => $prepaidCardId,
        ]);
    }

    /**
     * Update a  Extract
     * @param integer $id
     * @param float $balance
     * @param string|null $remarks
     * @return bool
     */
    public function update(
        int $id,
        float $balance,
        string $remarks = null
    ): PrepaidCardExtract {
        $prepaid_card_extract = $this->prepaidCardExtractRepository->show($id);

        if (!$prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.not-found');
        }

        return $this->prepaidCardExtractRepository->store([
            'balance' => $balance,
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

        $shareUsers = $this->shareUserRepository->get(['user_id' => auth()->user()->id], [], [], ['shareUser']);

        if ($shareUsers && $shareUsers->count()) {
            $shareUsers = $shareUsers->map(function ($item) {
                return [
                    'share_user_id' => $item->share_user_id,
                    'share_user_name' => $item->shareUser->name
                ];
            });
        }

        return [
            'extract' => $prepaid_card_extract,
            'shareUsers' => $shareUsers,
        ];
    }
}
