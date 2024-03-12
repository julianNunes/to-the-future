<?php

namespace App\Services;

use App\Models\PrepaidCard;
use App\Repositories\Interfaces\{
    PrepaidCardExtractExpenseRepositoryInterface,
    PrepaidCardExtractRepositoryInterface,
    PrepaidCardRepositoryInterface,
    TagRepositoryInterface
};
use App\Services\Interfaces\PrepaidCardServiceInterface;
use Exception;
use Illuminate\Database\Eloquent\Builder;

class PrepaidCardService implements PrepaidCardServiceInterface
{
    public function __construct(
        private PrepaidCardRepositoryInterface $prepaidCardRepository,
        private PrepaidCardExtractRepositoryInterface $prepaidCardExtractRepository,
        private PrepaidCardExtractExpenseRepositoryInterface $prepaidCardExtractExpenseRepository,
        private TagRepositoryInterface $tagRepository
    ) {
    }

    /**
     * Returns data to Prepaid Card Management
     * @return Array
     */
    public function index(): array
    {
        $prepaidCards = $this->prepaidCardRepository->get(['user_id' => auth()->user()->id]);
        return [
            'prepaidCards' => $prepaidCards,
        ];
    }

    /**
     * Create new Prepaid Card
     * @param string $name
     * @param string $digits
     * @param boolean $isActive
     * @return PrepaidCard
     */
    public function create(
        string $name,
        string $digits,
        bool $isActive
    ): PrepaidCard {
        $prepaid_card = $this->prepaidCardRepository->getOne(['name' => $name]);

        if ($prepaid_card) {
            throw new Exception('prepaid-card.already-exists');
        }

        $prepaid_card = $this->prepaidCardRepository->store([
            'name' => $name,
            'digits' => $digits,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ]);

        return $prepaid_card;
    }

    /**
     * Update a Prepaid Card
     * @param integer $id
     * @param string $name
     * @param string $digits
     * @param boolean $is_active
     * @return boolean
     */
    public function update(
        int $id,
        string $name,
        string $digits,
        bool $isActive
    ): PrepaidCard {
        $prepaid_card = $this->prepaidCardRepository->getOne(function (Builder $query) use ($id, $name) {
            $query->where('name', $name)->where('id', '!=', $id);
        });

        if ($prepaid_card) {
            throw new Exception('prepaid-card.already-exists');
        }

        $prepaid_card = $this->prepaidCardRepository->show($id);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        return $this->prepaidCardRepository->store([
            'name' => $name,
            'digits' => $digits,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ], $prepaid_card);
    }

    /**
     * Deleta a Prepaid Card
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $prepaid_card = $this->prepaidCardRepository->show($id, [
            'extracts' => [
                'expenses' => [
                    'tags'
                ]
            ]
        ]);

        if (!$prepaid_card) {
            throw new Exception('prepaid-card.not-found');
        }

        // Remove todos os vinculos
        foreach ($prepaid_card->extracts as $invoice) {
            foreach ($invoice->expenses as $expense) {
                $this->tagRepository->saveTagsToModel($expense, $expense->tags);
                $this->prepaidCardExtractExpenseRepository->delete($expense->id);
            }

            $this->prepaidCardExtractRepository->delete($invoice->id);
        }

        return $this->prepaidCardRepository->delete($prepaid_card->id);
    }
}
