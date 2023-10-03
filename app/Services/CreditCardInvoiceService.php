<?php

namespace App\Services;

use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\ShareUser;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreditCardInvoiceService
{
    public function __construct()
    {
    }

    /**
     * Retorna os dados para o Gerenciamento de Faturas de um Cartões de Credito
     * @return Array
     */
    public function index(int $creditCardId): array
    {
        $creditCard = CreditCard::where('id', $creditCardId)->with('invoices')->first();

        return [
            'creditCard' => $creditCard,
            'invoices' => $creditCard->invoices,
        ];
    }


    /**
     * Undocumented function
     *
     * @param string $dueDate
     * @param string $closingDate
     * @param string $year
     * @param string $month
     * @param integer $creditCardId
     * @param boolean $automaticGenerate
     * @return CreditCardInvoice
     */
    public function create(
        string $dueDate,
        string $closingDate,
        string $year,
        string $month,
        int $creditCardId,
        bool $automaticGenerate = false
    ): CreditCardInvoice {
        $credit_card = CreditCard::find($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $credit_card_invoice = CreditCardInvoice::where(['year' => $year, 'month' => $month])->first();

        if ($credit_card_invoice) {
            throw new Exception('credit-card-invoice.already-exists');
        }

        $credit_card_invoice = new CreditCardInvoice([
            'due_date' => $dueDate,
            'closing_date' => $closingDate,
            'year' => $year,
            'month' => $month,
            'credit_card_id' => $creditCardId,
        ]);

        $credit_card_invoice->save();

        if ($automaticGenerate) {
            $due_date = Carbon::parse($dueDate);
            $closing_date = Carbon::parse($closingDate);

            $new_due_date = $due_date->copy()->addMonth();
            $new_closing_date = $closing_date->copy()->addMonth();

            for ($i = $new_due_date->month; $i <= 12; $i++) {
                $new_credit_card_invoice = CreditCardInvoice::where(['year' => $year, 'month' => $new_closing_date->month])->first();

                if (!$new_credit_card_invoice) {
                    $new_credit_card_invoice = new CreditCardInvoice([
                        'due_date' => $new_due_date->format('y-m-d'),
                        'closing_date' => $new_closing_date->format('y-m-d'),
                        'year' => $year,
                        'month' => $new_closing_date->month,
                        'credit_card_id' => $creditCardId,
                    ]);

                    $new_credit_card_invoice->save();
                }

                $new_due_date->addMonth();
                $new_closing_date->addMonth();
            }
        }


        return $credit_card_invoice;
    }

    /**
     * Deleta uma fatura do cartãp de credito
     * @param int $id
     */
    public function delete(int $id): bool
    {
        $credit_card_invoice = CreditCardInvoice::with([
            'file',
            'expenses' => [
                'divisions' => [
                    'tags'
                ],
                'tags'
            ]
        ])->find($id);


        if (!$credit_card_invoice) {
            throw new Exception('credit-card-invoice.not-found');
        }

        // Remove todos os vinculos
        foreach ($credit_card_invoice->expenses as $expense) {

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

        if ($credit_card_invoice->file) {
            $credit_card_invoice->file->delete();
        }

        return $credit_card_invoice->delete();
    }

    /**
     * Retorna os dados para visualização/edição da Fatura de um Cartões de Credito
     * @return Array
     */
    public function show(int $creditCardId, int $id): array
    {
        $credit_card = CreditCard::find($creditCardId);

        if (!$credit_card) {
            throw new Exception('credit-card.not-found');
        }

        $creditCardInvoice = CreditCardInvoice::where('id', $id)->with(['creditCard', 'expenses'])->first();

        if (!$creditCardInvoice) {
            throw new Exception('credit-card-inovice.not-found');
        }

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
            'creditCardInvoice' => $creditCardInvoice,
            'shareUsers' => $shareUsers,
        ];
    }
}
