<?php

namespace App\Services;

use App\Helpers\Budget\Interfaces\BudgetCalculateInterface;
use App\Models\BudgetProvision;
use App\Repositories\Interfaces\{
    BudgetProvisionRepositoryInterface,
    BudgetRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetProvisionServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetProvisionService implements BudgetProvisionServiceInterface
{
    public function __construct(
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetProvisionRepositoryInterface $budgetProvisionRepository,
        private TagRepositoryInterface $tagRepository,
        private BudgetCalculateInterface $budgetCalculate
    ) {
    }

    /**
     * Create a new Provision to Budget
     * @param integer $budgetId
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetProvision
     */
    public function create(
        int $budgetId,
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): BudgetProvision {
        $budget = $this->budgetRepository->show($budgetId);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $provision = $this->budgetProvisionRepository->store([
            'budget_id' => $budgetId,
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId
        ]);

        $this->tagRepository->saveTagsToModel($provision, $tags);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budgetId, $shareUserId ? true : false);

        return $provision;
    }

    /**
     * Update a Provision to Budget
     * @param integer $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return boolean
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): bool {
        $provision = $this->budgetProvisionRepository->show($id);

        if (!$provision) {
            throw new Exception('budget-provision.not-found');
        }

        $budget = $this->budgetRepository->show($provision->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        $change_share_user = ($shareUserId != $provision->share_user_id) || ($shareValue != $provision->share_value) ? true : false;

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($provision, $tags);

        $this->budgetProvisionRepository->store([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ], $provision);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($provision->budget_id, $change_share_user);

        return true;
    }

    /**
     * Delete a Provision to Budget
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $provision = $this->budgetProvisionRepository->show($id);

        if (!$provision) {
            throw new Exception('budget-provision.not-found');
        }

        $budget_id = $provision->budget_id;
        $share_user_id = $provision->share_user_id;

        // Remove Tags
        $this->tagRepository->saveTagsToModel($provision);

        $this->budgetProvisionRepository->delete($id);

        // Atualiza Orçamento
        $this->budgetCalculate->recalculate($budget_id, $share_user_id ? true : false);

        return true;
    }
}
