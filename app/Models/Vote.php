<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Vote extends Model
{
    use HasFactory;
    use UsesUuid;

    public const CREATED_AT = null;

    protected $fillable = [
        'voter_id',
        'slot_id',
        'response',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Voter::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(PollSlot::class, 'slot_id');
    }
}
