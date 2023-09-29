<?php

namespace App\Services;

use App\Models\Provision;
use App\Models\ShareUser;
use Exception;

class ProvisionService
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
        $provisions = Provision::where('user_id', auth()->user()->id)->with('shareUser')->get();
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
     * @param string $week
     * @param string $remarks
     * @param integer $share_value
     * @param integer|null $share_user_id
     * @return Provision
     */
    public function create(
        string $description,
        float $value,
        string $week,
        string $remarks = null,
        float $share_value = 0,
        int $share_user_id = null
    ): Provision {
        $provision = new Provision([
            'description' => $description,
            'value' => $value,
            'week' => $week,
            'remarks' => $remarks,
            'share_value' => $share_value,
            'share_user_id' => $share_user_id,
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
     * @param string $week
     * @param string $remarks
     * @param integer $share_value
     * @param integer|null $share_user_id
     * @return bool
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $week,
        string $remarks = null,
        float $share_value = null,
        int $share_user_id = null
    ): bool {
        $provision = Provision::find($id);

        if (!$provision) {
            throw new Exception('provision.not-found');
        }

        return $provision->update([
            'description' => $description,
            'value' => $value,
            'week' => $week,
            'remarks' => $remarks,
            'share_value' => $share_value,
            'share_user_id' => $share_user_id,
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Deleta um Provisionamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $provision = Provision::find($id);


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
