<?php

namespace App\Services;

use App\Models\BudgetExpenseTagOption;
use App\Repositories\Interfaces\{
    BudgetExpenseTagOptionRepositoryInterface,
    BudgetRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetExpenseTagOptionServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetExpenseTagOptionService implements BudgetExpenseTagOptionServiceInterface
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetExpenseTagOptionRepositoryInterface $budgetExpenseTagOptionRepository,
        private TagRepositoryInterface $tagRepository,
    ) {}

    /**
     * Create a new Goal to Budget
     * @param integer $budgetId
     * @param Collection $tags
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetExpenseTagOption
     */
    public function create(
        int $budgetId,
        Collection $tags,
        bool $countShare,
        ?string $group = null
    ): BudgetExpenseTagOption {
        $goal = $this->budgetExpenseTagOptionRepository->store([
            'group' => $group,
            'count_share' => $countShare,
            'budget_id' => $budgetId,
        ]);

        // Atualiza Tag
        $this->tagRepository->saveTagsToModel($goal, $tags);

        return $goal;
    }

    /**
     * Update a new Goal to Budget
     * @param integer $id
     * @param Collection $tags
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetExpenseTagOption
     */
    public function update(
        int $id,
        Collection $tags,
        bool $countShare,
        ?string $group = null
    ): BudgetExpenseTagOption {
        $goal = $this->budgetExpenseTagOptionRepository->show($id);

        if (!$goal) {
            throw new Exception('budget-goal.not-found');
        }

        $budget = $this->budgetRepository->show($goal->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Atualiza Tag
        $this->tagRepository->saveTagsToModel($goal, $tags);

        return $this->budgetExpenseTagOptionRepository->store([
            'group' => $group,
            'count_share' => $countShare,
        ], $goal);
    }

    /**
     * Delete a new Goal to Budget
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $goal = $this->budgetExpenseTagOptionRepository->show($id);

        if (!$goal) {
            throw new Exception('budget-goal.not-found');
        }

        // Remove Tags
        $this->tagRepository->saveTagsToModel($goal);

        return $this->budgetExpenseTagOptionRepository->delete($id);
    }
}
