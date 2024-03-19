<?php

namespace App\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Models\PrepaidCardExtractExpense;
use App\Repositories\Interfaces\{
    PrepaidCardExtractExpenseRepositoryInterface,
    PrepaidCardExtractRepositoryInterface,
    PrepaidCardRepositoryInterface,
    ShareUserRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\PrepaidCardExtractExpenseServiceInterface;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PrepaidCardExtractExpenseService implements PrepaidCardExtractExpenseServiceInterface
{
    public function __construct(
        private PrepaidCardRepositoryInterface $prepaidCardRepository,
        private PrepaidCardExtractRepositoryInterface $prepaidCardExtractRepository,
        private PrepaidCardExtractExpenseRepositoryInterface $prepaidCardExtractExpenseRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserRepositoryInterface $shareUserRepository,
        private BudgetCalculateInterface $budgetCalculate
    ) {
    }

    /**
     * Create new Expense to Extract
     * @param integer $prepaidCardId
     * @param integer $extractId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return PrepaidCardExtractExpense
     */
    public function create(
        int $prepaidCardId,
        int $extractId,
        string $description,
        string $date,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): PrepaidCardExtractExpense {
        $prepaid_card = $this->prepaidCardRepository->show($prepaidCardId);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        $prepaid_card_extract = $this->prepaidCardExtractRepository->show($extractId);

        if (!$prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.not-found');
        }

        $prepaid_card_extract_expense = $this->prepaidCardExtractExpenseRepository->store([
            'description' => $description,
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'extract_id' => $extractId
        ]);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($prepaid_card_extract_expense, $tags);

        // Atualiza Orçamento
        if ($prepaid_card_extract->budget_id) {
            $this->budgetCalculate->recalculate($prepaid_card_extract->budget_id, $shareUserId ? true : false);
        }

        return $prepaid_card_extract_expense;
    }

    /**
     * Update a Expense
     * @param integer $id
     * @param integer $prepaidCardId
     * @param integer $extractId
     * @param string $description
     * @param string $date
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return PrepaidCardExtractExpense
     */
    public function update(
        int $id,
        int $prepaidCardId,
        int $extractId,
        string $description,
        string $date,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): PrepaidCardExtractExpense {
        $prepaid_card = $this->prepaidCardRepository->show($prepaidCardId);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        $prepaid_card_extract = $this->prepaidCardExtractRepository->show($extractId);

        if (!$prepaid_card_extract) {
            throw new Exception('prepaid-card-extract.not-found');
        }

        $prepaid_card_extract_expense = $this->prepaidCardExtractExpenseRepository->show($id);

        if (!$prepaid_card_extract_expense) {
            throw new Exception('prepaid-card-extract-expense.not-found');
        }

        $change_share_user = ($shareUserId != $prepaid_card_extract_expense->share_user_id) || ($shareValue != $prepaid_card_extract_expense->share_value) ? true : false;

        $prepaid_card_extract_expense = $this->prepaidCardExtractExpenseRepository->store([
            'description' => $description,
            'date' => Carbon::parse($date)->format('y-m-d'),
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ], $prepaid_card_extract_expense);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($prepaid_card_extract_expense, $tags);

        // Atualiza Orçamento
        if ($prepaid_card_extract->budget_id) {
            $this->budgetCalculate->recalculate($prepaid_card_extract->budget_id, $change_share_user);
        }

        return $prepaid_card_extract_expense;
    }

    /**
     * Delete a Expense
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $prepaid_card_extract_expense = $this->prepaidCardExtractExpenseRepository->show($id, [
            'extract:id,budget_id',
            'tags'
        ]);

        if (!$prepaid_card_extract_expense) {
            throw new Exception('prepaid-card-extract-expense.not-found');
        }

        $budget_id = $prepaid_card_extract_expense->extract->budget_id;
        $share_user_id = $prepaid_card_extract_expense->share_user_id;

        $this->tagRepository->saveTagsToModel($prepaid_card_extract_expense, $prepaid_card_extract_expense->tags);
        $this->prepaidCardExtractExpenseRepository->delete($prepaid_card_extract_expense->id);

        // Atualiza Orçamento
        if ($budget_id) {
            $this->budgetCalculate->recalculate($budget_id, $share_user_id ? true : false);
        }

        return true;
    }
}
