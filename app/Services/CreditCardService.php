<?php

namespace App\Services;

use App\Models\CreditCard;
use Exception;

class CreditCardService
{
    public function __construct()
    {
    }

    /**
     * Retorna os dados para o Gerenciamento de Cartões de Credito
     * @return Array
     */
    public function index(): array
    {
        $creditCards = CreditCard::where('user_id', auth()->user()->id)->get();

        return [
            'creditCards' => $creditCards,
        ];
    }

    /**
     * Cria um novo Cartão do Crédito
     * @return CreditCard
     */
    public function create(
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): CreditCard {
        $credit_card = CreditCard::where('name', $name)->first();

        if ($credit_card) {
            throw new Exception('credit-card.already-exists');
        }

        $credit_card = new CreditCard([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ]);

        $credit_card->save();
        return $credit_card;
    }

    /**
     * Atualiza um Cartão de Credito
     * @param integer $id
     * @param string $name
     * @param string $digits
     * @param string $due_date
     * @param string $closing_date
     * @param boolean $is_active
     * @return boolean
     */
    public function update(
        int $id,
        string $name,
        string $digits,
        string $dueDate,
        string $closingDate,
        bool $isActive
    ): bool {
        $tag = CreditCard::where('name', $name)->where('id', '!=', $id)->first();

        if ($tag) {
            throw new Exception('credit-card.already-exists');
        }

        $credit_card = CreditCard::find($id);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        return $credit_card->update([
            'name' => $name,
            'digits' => $digits,
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'is_active' => $isActive,
            'user_id' => auth()->user()->id
        ]);
    }

    /**
     * Deleta um Cartão de Crédito
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card = CreditCard::with([
            'invoices' => [
                'file',
                'expenses' => [
                    'divisions' => [
                        'tags'
                    ],
                    'tags'
                ]
            ]
        ])->find($id);


        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card->invoices as $key => $invoice) {

            foreach ($invoice->expenses as $expense) {

                foreach ($expense->divisions as $division) {
                    foreach ($division->tags as $tag) {
                        $tag->delete();
                    }

                    $division->delete();
                }

                foreach ($expense->tags as $tag) {
                    $tag->delete();
                }

                $expense->delete();
            }

            if ($invoice->file) {
                $invoice->file->delete();
            }
        }

        return $credit_card->delete();
    }
}
