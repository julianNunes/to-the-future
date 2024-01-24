<?php

namespace App\Services;

use App\Models\FixExpense;
use App\Models\ShareUser;
use Exception;

class FixExpenseService
{
    public function __construct()
    {
    }

    /**
     * Retorna os dados para o index de Gerenciamento de Despesas Fixas
     * @return Array
     */
    public function index(): array
    {
        $expenses = FixExpense::where('user_id', auth()->user()->id)->with('shareUser')->get();
        $shareUsers = ShareUser::where('user_id', auth()->user()->id)->with('shareUser')->get();

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


    public function create(
        string $description,
        string $dueDate,
        float $value,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null
    ): FixExpense {
        $expense = new FixExpense([
            'description' => $description,
            'due_date' => $dueDate,
            'value' => $value,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ]);

        $expense->save();
        return $expense;
    }

    public function update(
        int $id,
        string $description,
        string $dueDate,
        float $value,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null
    ): bool {
        $expense = FixExpense::find($id);

        if (!$expense) {
            throw new Exception('provision.not-found');
        }

        return $expense->update([
            'description' => $description,
            'value' => $value,
            'due_date' => $dueDate,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Deleta um Provisionamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $expense = FixExpense::find($id);

        if (!$expense) {
            throw new Exception('fix-expense.not-found');
        }

        return $expense->delete();
    }
}
