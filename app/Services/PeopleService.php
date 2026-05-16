<?php

namespace App\Services;

use App\Models\People;
use App\Repositories\Interfaces\PeopleRepositoryInterface;
use App\Services\Interfaces\PeopleServiceInterface;
use Exception;

class PeopleService implements PeopleServiceInterface
{
    public function __construct(private PeopleRepositoryInterface $peopleRepository)
    {
    }

    public function index(?string $search = null, ?array $sort = null, int $limit = 10): array
    {
        return [
            'data' => $this->peopleRepository->paginateIndex($search, $sort, $limit),
        ];
    }

    public function show(int $id): People
    {
        $person = $this->peopleRepository->show($id);

        if (!$person) {
            throw new Exception('people.not-found');
        }

        return $person;
    }

    public function create(array $data): People
    {
        return $this->peopleRepository->store($data);
    }

    public function update(int $id, array $data): People
    {
        return $this->peopleRepository->store($data, $this->show($id));
    }

    public function delete(int $id): bool
    {
        $this->show($id);

        return $this->peopleRepository->delete($id);
    }
}
