<?php

namespace App\Services;

use App\Models\BudgetGoal;
use App\Services\Interfaces\BudgetGoalServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetGoalService implements BudgetGoalServiceInterface
{
    public function __construct()
    {
    }

    /**
     * Cria uma nova Meta para o Orçamento
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
        $goal = BudgetGoal::create([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'count_only_share' => $countOnlyShare,
            'budget_id' => $budgetId,
        ]);

        $goal->save();
        TagService::saveTagsToModel($goal, $tags);

        return $goal;
    }

    /**
     * Atualiza uma Meta do Orçamento
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
    ): bool {
        $goal = BudgetGoal::with('tags')->find($id);

        if (!$goal) {
            throw new Exception('budget-provision.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($goal, $tags);

        return $goal->update([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'count_only_share' => $countOnlyShare,
        ]);
    }

    /**
     * Deleta uma Provisão do Orçamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $goal = BudgetGoal::with('tags')->find($id);

        if (!$goal) {
            throw new Exception('budget-provision.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($goal);

        return $goal->delete();
    }
}
