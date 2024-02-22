<?php

namespace App\Services;

use App\Models\FixExpense;
use App\Repositories\Interfaces\FixExpenseRepositoryInterface;
use App\Repositories\Interfaces\ShareUserRepositoryInterface;
use App\Services\Interfaces\FixExpenseServiceInterface;
use Exception;
use Illuminate\Support\Collection;
use TagService;

class FixExpenseService implements FixExpenseServiceInterface
{
    public function __construct(
        private FixExpenseRepositoryInterface $fixExpenseRepository,
        private ShareUserRepositoryInterface $shareUserRepository
    ) {
    }

    /**
     *  Returns data for the Fixed Expense index
     * @return Array
     */
    public function index(): array
    {
        $expenses = $this->fixExpenseRepository->get(['user_id' => auth()->user()->id], [], [], ['shareUser', 'tags']);
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
            'expenses' => $expenses,
            'shareUsers' => $shareUsers
        ];
    }

    /**
     * Create a new Fix Expense
     * @param string $description
     * @param string $dueDate
     * @param float $value
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return FixExpense
     */
    public function create(
        string $description,
        string $dueDate,
        float $value,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
    ): FixExpense {
        $expense = $this->fixExpenseRepository->store([
            'description' => $description,
            'due_date' => $dueDate,
            'value' => $value,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ]);

        // Salva Tags
        TagService::saveTagsToModel($expense, $tags);
        return $expense;
    }

    /**
     * Update a Fix Expense
     * @param integer $id
     * @param string $description
     * @param string $dueDate
     * @param float $value
     * @param string|null $remarks
     * @param float|null $shareValue
     * @param integer|null $shareUserId
     * @param Collection|null $tags
     * @return FixExpense
     */
    public function update(
        int $id,
        string $description,
        string $dueDate,
        float $value,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags = null,
    ): FixExpense {
        $expense = $this->fixExpenseRepository->show($id);

        if (!$expense) {
            throw new Exception('fix-expense.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($expense, $tags);

        return $this->fixExpenseRepository->store([
            'description' => $description,
            'value' => $value,
            'due_date' => $dueDate,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ], $expense);
    }

    /**
     * Deleta a Fix Expense
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $expense = $this->fixExpenseRepository->show($id);

        if (!$expense) {
            throw new Exception('fix-expense.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($expense);

        return $this->fixExpenseRepository->delete($id);
    }
}
