<?php

namespace App\Services;

use App\Models\BudgetProvision;
use App\Services\Interfaces\BudgetProvisionServiceInterface;
use Exception;
use Illuminate\Support\Collection;

class BudgetProvisionService implements BudgetProvisionServiceInterface
{
    public function __construct()
    {
    }

    /**
     * Cria uma nova Provisão para o Orçamento
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string $remarks
     * @param integer $budgetId
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return BudgetProvision
     */
    public function create(
        string $description,
        float $value,
        string $group,
        string $remarks,
        int $budgetId,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): BudgetProvision {
        $provision = BudgetProvision::create([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'budget_id' => $budgetId,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId
        ]);

        $provision->save();
        TagService::saveTagsToModel($provision, $tags);

        return $provision;
    }

    /**
     * Atualiza uma Provisão do Orçamento
     * @param integer $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string $remarks
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
        string $remarks,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null
    ): bool {
        $provision = BudgetProvision::with('tags')->find($id);

        if (!$provision) {
            throw new Exception('budget-provision.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($provision, $tags);

        return $provision->update([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
        ]);
    }

    /**
     * Deleta uma Provisão do Orçamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $provision = BudgetProvision::with('tags')->find($id);

        if (!$provision) {
            throw new Exception('budget-provision.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($provision);

        return $provision->delete();
    }
}
