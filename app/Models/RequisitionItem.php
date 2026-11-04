<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string|null $distance_km
 * @property string|null $tank_capacity_liters
 * @property string|null $km_per_liter
 * @property string|null $fuel_price_per_liter
 * @property string $unit_price
 * @property string $quantity
 * @property string $amount
 */
#[Fillable(['requisition_id', 'expense_type_id', 'vehicle_id', 'description', 'people_count', 'origin', 'destination', 'distance_km', 'tank_capacity_liters', 'km_per_liter', 'fuel_price_per_liter', 'days', 'meals_per_day', 'unit_price', 'quantity', 'unit', 'amount'])]
class RequisitionItem extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'people_count' => 'integer',
            'days' => 'integer',
            'meals_per_day' => 'integer',
            'distance_km' => 'decimal:2',
            'tank_capacity_liters' => 'decimal:2',
            'km_per_liter' => 'decimal:2',
            'fuel_price_per_liter' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'quantity' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Requisition, $this> */
    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    /** @return BelongsTo<ExpenseType, $this> */
    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }

    /** @return BelongsTo<Vehicle, $this> */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
