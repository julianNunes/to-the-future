<?php

namespace App\Services;

use App\Models\BudgetGoal;
use App\Repositories\Interfaces\{
    BudgetGoalRepositoryInterface,
    BudgetRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetGoalServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetGoalService implements BudgetGoalServiceInterface
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetGoalRepositoryInterface $budgetGoalRepository,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    /**
     * Create a new Goal to Budget
     * @param integer $budgetId
     * @param string $description
     * @param float $value
     * @param Collection $tag
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetGoal
     */
    public function create(
        int $budgetId,
        string $description,
        float $value,
        Collection $tags,
        bool $countShare,
        string $group = null
    ): BudgetGoal {
        $goal = $this->budgetGoalRepository->store([
            'description' => $description,
            'value' => $value,
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
     * @param string $description
     * @param float $value
     * @param Collection $tags
     * @param boolean $countShare
     * @param string|null $group
     * @return BudgetGoal
     */
    public function update(
        int $id,
        string $description,
        float $value,
        Collection $tags,
        bool $countShare,
        string $group = null
    ): BudgetGoal {
        $goal = $this->budgetGoalRepository->show($id);

        if (!$goal) {
            throw new Exception('budget-goal.not-found');
        }

        $budget = $this->budgetRepository->show($goal->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Atualiza Tag
        $this->tagRepository->saveTagsToModel($goal, $tags);

        return $this->budgetGoalRepository->store([
            'description' => $description,
            'value' => $value,
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
        $goal = $this->budgetGoalRepository->show($id);

        if (!$goal) {
            throw new Exception('budget-goal.not-found');
        }

        // Remove Tags
        $this->tagRepository->saveTagsToModel($goal);

        return $this->budgetGoalRepository->delete($id);
    }
}
