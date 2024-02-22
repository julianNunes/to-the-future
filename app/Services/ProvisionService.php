<?php

namespace App\Services;

use App\Models\Provision;
use App\Repositories\Interfaces\ProvisionRepositoryInterface;
use App\Repositories\Interfaces\ShareUserRepositoryInterface;
use App\Services\Interfaces\ProvisionServiceInterface;
use Exception;
use Illuminate\Support\Collection;
use TagService;

class ProvisionService implements ProvisionServiceInterface
{
    public function __construct(
        private ProvisionRepositoryInterface $provisionRepository,
        private ShareUserRepositoryInterface $shareUserRepository
    ) {
    }

    /**
     * Retorna os dados para o index de Provisionamento
     * @return Array
     */
    public function index(): array
    {
        $provisions = $this->provisionRepository->get(['user_id' => auth()->user()->id], [], [], ['shareUser', 'tags']);
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
        $provision = $this->provisionRepository->store([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ]);

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
     * @return Provision
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
    ): Provision {
        $provision = $this->provisionRepository->show($id);

        if (!$provision) {
            throw new Exception('provision.not-found');
        }

        // Atualiza Tags
        TagService::saveTagsToModel($provision, $tags);

        return $this->provisionRepository->store([
            'description' => $description,
            'value' => $value,
            'group' => $group,
            'remarks' => $remarks,
            'share_value' => $shareValue,
            'share_user_id' => $shareUserId,
            'user_id' => auth()->user()->id
        ], $provision);
    }

    /**
     * Deleta um Provisionamento
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $provision = $this->provisionRepository->show($id);

        if (!$provision) {
            throw new Exception('provision.not-found');
        }

        // Remove Tags
        TagService::saveTagsToModel($provision);

        return $this->provisionRepository->delete($id);
    }
}
