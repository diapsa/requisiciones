<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'bank_name', 'account_holder', 'account_type', 'account_number', 'bank_city', 'branch_number', 'is_active'])]
class BankAccount extends Model
{
    /** @var list<string> */
    protected $hidden = ['account_number'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'is_active' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<Requisition, $this> */
    public function requisitions(): HasMany
    {
        return $this->hasMany(Requisition::class);
    }
}
