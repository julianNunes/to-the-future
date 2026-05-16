<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @mixin IdeHelperPrepaidCardExtractExpense
 */
class PrepaidCardExtractExpense extends Model
{
    use HasFactory;

    protected $table = 'prepaid_card_extract_expenses';

    protected $fillable = [
        'description',
        'date',
        'value',
        'group',
        'remarks',
        'share_value',
        'extract_id',
        'share_user_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'value' => 'decimal:2',
        'group' => 'string',
        'share_value' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function shareUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'share_user_id', 'id');
    }

    public function extract(): BelongsTo
    {
        return $this->belongsTo(PrepaidCardExtract::class, 'extract_id', 'id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
