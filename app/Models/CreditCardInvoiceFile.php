<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\CreditCardInvoiceFile
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property int $invoice_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CreditCardInvoice $invoice
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile whereInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CreditCardInvoiceFile whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CreditCardInvoiceFile extends Model
{
    use HasFactory;

    protected $table = 'credit_card_invoice_files';

    protected $fillable = [
        'name',
        'path',
        'invoice_id',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoice::class, 'invoice_id', 'id');
    }
}
