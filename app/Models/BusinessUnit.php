<?php

namespace App\Models;

use Database\Factories\BusinessUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class BusinessUnit extends Model
{
    /** @use HasFactory<BusinessUnitFactory> */
    use HasFactory;

    /** @return HasMany<Requisition, $this> */
    public function requisitions(): HasMany
    {
        return $this->hasMany(Requisition::class);
    }
}
