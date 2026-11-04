<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'calculation_method', 'default_unit', 'is_requestable', 'sort_order'])]
class ExpenseType extends Model
{
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
