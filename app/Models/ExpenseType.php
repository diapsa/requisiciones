<?php

namespace App\Models;

use Database\Factories\ExpenseTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'calculation_method', 'default_unit', 'is_requestable', 'sort_order'])]
class ExpenseType extends Model
{
    /** @use HasFactory<ExpenseTypeFactory> */
    use HasFactory;

    /**
     * How a requisition item of this type gets its amount (see docs/reglas-de-negocio.md).
     *
     * @var list<string>
     */
    public const CALCULATION_METHODS = ['fuel_by_distance', 'per_person_meals', 'breakfast_reference', 'per_night', 'fixed'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_requestable' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** @return HasMany<ExpenseRate, $this> */
    public function expenseRates(): HasMany
    {
        return $this->hasMany(ExpenseRate::class);
    }

    /** @return HasMany<RequisitionItem, $this> */
    public function requisitionItems(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    /** @return HasMany<Expense, $this> */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
