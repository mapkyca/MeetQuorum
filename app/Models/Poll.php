<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poll extends Model
{
    use HasFactory;
    use UsesUuid;

    protected $fillable = [
        'permalink_token',
        'creator_user_id',
        'creator_name',
        'creator_email',
        'mgmt_token',
        'title',
        'description',
        'meeting_link',
        'creator_tz',
        'slot_granularity',
        'status',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (! $model->getKey()) {
                $model->setAttribute($model->getKeyName(), (string) Str::uuid());
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PollSlot::class);
    }

    public function voters(): HasMany
    {
        return $this->hasMany(Voter::class);
    }
}
