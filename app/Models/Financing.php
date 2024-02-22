<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\Financing
 *
 * @property int $id
 * @property string $description Descrição
 * @property string $start_date Data de contratação do financiamento
 * @property string $total Valor total do emprestimo
 * @property string|null $fees_monthly Valor mensal de juros
 * @property string $portion_total Total de Parcelas
 * @property string|null $remarks
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FinancingInstallment> $installments
 * @property-read int|null $installments_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Financing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Financing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Financing query()
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereFeesMonthly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing wherePortionTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereRemarks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Financing whereUserId($value)
 * @mixin \Eloquent
 */
class Financing extends Model
{
    use HasFactory;

    protected $table = 'financings';

    protected $fillable = [
        'description',
        'start_date',
        'total',
        'fees_monthly',
        'portion_total',
        'remarks',
        'user_id',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(FinancingInstallment::class, 'financing_id', 'id');
    }
}
