<?php

namespace App\Services;

use App\Models\BudgetProvision;
use App\Repositories\Interfaces\{
    BudgetProvisionRepositoryInterface,
    BudgetRepositoryInterface,
    TagRepositoryInterface,
};
use App\Services\Interfaces\BudgetProvisionServiceInterface;
use App\Services\Interfaces\BudgetServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetProvisionService implements BudgetProvisionServiceInterface
{
    public function __construct(
        private BudgetServiceInterface $budgetService,
        private BudgetRepositoryInterface $budgetRepository,
        private BudgetProvisionRepositoryInterface $budgetProvisionRepository,
        private TagRepositoryInterface $tagRepository,
    ) {
    }

    /**
     * Create a new Provision to Budget
     * @param string $description
     * @param float $value
     * @param string $group
     * @param integer $budgetId
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetProvision
     */
    public function create(
        string $description,
        float $value,
        string $group,
        int $budgetId,
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
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'budget_id' => $budgetId,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId
        ]);

        $this->tagRepository->saveTagsToModel($provision, $tags);

        return $provision;
    }

    /**
     * Update a new Provision to Budget
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
    ): BudgetProvision {
        $provision = $this->budgetProvisionRepository->show($id);

        if (!$provision) {
            throw new Exception('budget-provision.not-found');
        }

        $budget = $this->budgetRepository->show($provision->budget_id);

        if (!$budget) {
            throw new Exception('budget.not-found');
        }

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($provision, $tags);

        return $this->budgetProvisionRepository->store([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ], $provision);
    }

    /**
     * Delete a new Provision to Budget
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $provision = $this->budgetProvisionRepository->show($id);

        if (!$provision) {
            throw new Exception('budget-provision.not-found');
        }

        // Remove Tags
        $this->tagRepository->saveTagsToModel($provision);

        return $this->budgetProvisionRepository->delete($id);
    }
}
