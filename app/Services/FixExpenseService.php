<?php

namespace App\Services;

use App\Helpers\ShareUser\Interfaces\ShareUserOptionsInterface;
use App\Models\FixExpense;
use App\Repositories\Interfaces\FixExpenseRepositoryInterface;
use App\Repositories\Interfaces\TagRepositoryInterface;
use App\Services\Interfaces\FixExpenseServiceInterface;
use App\Support\Concerns\EnsuresResourceOwnership;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class FixExpenseService implements FixExpenseServiceInterface
{
    use EnsuresResourceOwnership;

    public function __construct(
        private FixExpenseRepositoryInterface $fixExpenseRepository,
        private TagRepositoryInterface $tagRepository,
        private ShareUserOptionsInterface $shareUserOptions,
    ) {}

    /**
     *  Returns data for the Fixed Expense index
     * @return Array
     */
    public function index(): array
    {
        $userId = (int) Auth::id();
        $expenses = $this->fixExpenseRepository->get(['user_id' => $userId], [], [], ['shareUser', 'tags']);
        $shareUsers = $this->shareUserOptions->resolveForUser($userId)['options'];

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
        ?string $remarks = null,
        ?float $shareValue = null,
        ?int $shareUserId = null,
        Collection|null $tags = null,
    ): FixExpense {
        $expense = $this->fixExpenseRepository->store([
            'description' => $description,
            'due_date' => $dueDate,
            'value' => $value,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => (int) Auth::id()
        ]);

        // Salva Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);
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
        ?string $remarks = null,
        ?float $shareValue = null,
        ?int $shareUserId = null,
        Collection|null $tags = null,
    ): FixExpense {
        $expense = $this->fixExpenseRepository->show($id);

        if (!$expense) {
            throw new Exception('fix-expense.not-found');
        }

        $this->ensureOwnedByCurrentUser($expense);

        // Atualiza Tags
        $this->tagRepository->saveTagsToModel($expense, $tags);

        return $this->fixExpenseRepository->store([
            'description' => $description,
            'value' => $value,
            'due_date' => $dueDate,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => (int) Auth::id()
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

        $this->ensureOwnedByCurrentUser($expense);

        // Remove Tags
        $this->tagRepository->saveTagsToModel($expense);

        return $this->fixExpenseRepository->delete($id);
    }
}
