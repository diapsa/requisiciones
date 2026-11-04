<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $tank_capacity_liters
 * @property string $km_per_liter
 */
#[Fillable(['model', 'plate_number', 'tank_capacity_liters', 'km_per_liter', 'is_active'])]
class Vehicle extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tank_capacity_liters' => 'decimal:2',
            'km_per_liter' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /** @return HasMany<RequisitionItem, $this> */
    public function requisitionItems(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }
}
