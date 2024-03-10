<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function shareUser(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'share_user_id');
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
