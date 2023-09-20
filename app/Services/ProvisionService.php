<?php

namespace App\Services;

use App\Models\Provision;
use App\Repositories\Contracts\ProvisionRepositoryInterface;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Collection;

class ProvisionService
{
    protected $provisionRepository;

    public function __construct(ProvisionRepositoryInterface $provisionRepository)
    {
        $this->provisionRepository = $provisionRepository;
    }

    /**
     * Retorna todos os provisonamentos
     * @return Collection
    */
    public function all()
    {
        $provisions = $this->provisionRepository->all();

        if ($provisions && $provisions->count()) {
            $provisions = $provisions->sortBy([
                ['week', 'asc'],
                ['id', 'asc']
            ]);
        }

        return $provisions;
    }

    /**
     * Cria um novo Provisionamento
     * @param array $data
     * @return Provision
    */
    public function create(array $data): Provision
    {
        return $this->provisionRepository->create($data);
    }

    /**
     * Atualiza um Provisionamento através
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $provision = $this->provisionRepository->find($id);

        if (!$provision) {
            throw new Exception('Provision Not Found');
        }

        return $this->provisionRepository->update($provision, $data);
    }

    /**
     * Deletar um Provisionamento através
     * @param int $id
     * return json response
     */
    public function delete(int $id)
    {
        $provision = $this->provisionRepository->find($id);

        if (!$provision) {
            throw new Exception('Provision Not Found');
        }

        return $this->provisionRepository->delete($provision);
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
