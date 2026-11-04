<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class BusinessUnit extends Model
{
    /** @return HasMany<Requisition, $this> */
    public function requisitions(): HasMany
    {
        return $this->hasMany(Requisition::class);
    }
}
