<?php

namespace App\Services;

use App\Models\BudgetGoal;
use App\Repositories\Interfaces\BudgetGoalRepositoryInterface;
use App\Repositories\Interfaces\BudgetRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\BudgetGoalServiceInterface;
use App\Services\Interfaces\BudgetServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetGoalService implements BudgetGoalServiceInterface
{
    public function __construct(
        private BudgetServiceInterface $budgetService,
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetGoalRepositoryInterface $budgetGoalRepository,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    /**
     * Create a new Goal to Budget
     * @param string $description
     * @param float $value
     * @param float $group
     * @param boolean $countOnlyShare
     * @param integer $budgetId
     * @param Collection|null $tags
     * @return BudgetGoal
     */
    public function create(
        string $description,
        float $value,
        float $group,
        bool $countOnlyShare,
        int $budgetId,
        Collection $tags = null
    ): BudgetGoal {
        $goal = $this->budgetGoalRepository->store([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'count_only_share' => $countOnlyShare,
            'budget_id' => $budgetId,
        ]);

        $this->tagRepository->saveTagsToModel($goal, $tags);
        return $goal;
    }

    /**
     * Update a new Goal to Budget
     * @param integer $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param boolean $countOnlyShare
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        bool $countOnlyShare,
        Collection $tags = null
    ): BudgetGoal {
        $goal = $this->budgetGoalRepository->show($id);

        if (!$goal) {
            throw new Exception('budget-goal.not-found');
        }

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($goal, $tags);

        return $this->budgetGoalRepository->store([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'count_only_share' => $countOnlyShare,
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
            throw new Exception('budget-provision.not-found');
        }

        // Remove Tags
        $this->tagRepository->saveTagsToModel($goal);

        return $this->budgetGoalRepository->delete($id);
    }
}
