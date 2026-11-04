<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['approvable_type', 'approvable_id', 'approver_id', 'stage', 'decision', 'comments', 'decided_at'])]
class Approval extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
