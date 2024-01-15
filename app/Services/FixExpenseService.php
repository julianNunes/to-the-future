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
     * Retorna os dados para o index de Provisionamento
     * @return Array
     */
    public function index(): array
    {
        $provisions = FixExpense::where('user_id', auth()->user()->id)->with('shareUser')->get();
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
            'provisions' => $provisions,
            'shareUsers' => $shareUsers
        ];
    }

    /**
     * Cria um novo Provisionamento
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string $remarks
     * @param integer $shareValue
     * @param integer|null $shareUserId
     * @return FixExpense
     */
    public function create(
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = 0,
        int $shareUserId = null
    ): FixExpense {
        $provision = new FixExpense([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ]);

        $provision->save();
        return $provision;
    }

    /**
     * Atualiza um Provisionamento
     * @param int $id
     * @param string $description
     * @param float $value
     * @param string $group
     * @param string $remarks
     * @param integer $shareValue
     * @param integer|null $shareUserId
     * @return bool
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null
    ): bool {
        $provision = FixExpense::find($id);

        if (!$provision) {
            throw new Exception('provision.not-found');
        }

        return $provision->update([
            'description' => $description,
            'value' => $value,
            'group' => $group,
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
        $provision = FixExpense::find($id);

        if (!$provision) {
            throw new Exception('provision.not-found');
        }

        return $provision->delete();
    }

    // /**
    //  * Armazenamento da Imagem do Provisionamento
    //  * @param object $image
    //  * @return string
    //  */
    // public function storeImageProvision(object $image)
    // {
    //     return $image->store("/provisions");
    // }
}
