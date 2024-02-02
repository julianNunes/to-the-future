<?php

namespace App\Services;

use App\Models\Provision;
use App\Models\ShareUser;
use Exception;
use Illuminate\Support\Collection;

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
        $provisions = Provision::where('user_id', auth()->user()->id)->with('shareUser', 'tags')->get();
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
     * @param Collection $tags
     * @return Provision
     */
    public function create(
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = 0,
        int $shareUserId = null,
        Collection $tags
    ): Provision {
        $provision = new Provision([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ]);

        $provision->save();

        // Atualiza Tags
        TagService::saveTagsToModel($provision, $tags);
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
     * @param Collection $tags
     * @return bool
     */
    public function update(
        int $id,
        string $description,
        float $value,
        string $group,
        string $remarks = null,
        float $shareValue = null,
        int $shareUserId = null,
        Collection $tags
    ): bool {
        $provision = Provision::find($id);

        if (!$provision) {
            throw new Exception('provision.not-found');
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

        // Remove Tags
        TagService::saveTagsToModel($provision);

        return $provision->delete();
    }
}
