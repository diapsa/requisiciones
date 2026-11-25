<?php

namespace App\Http\Requests;

use App\Models\ExpenseRate;

class UpdateExpenseRateRequest extends StoreExpenseRateRequest
{
    /**
     * Skip the rate being edited when looking for overlapping rates.
     */
    protected function rateBeingUpdated(): ?ExpenseRate
    {
        $rate = $this->route('expense_rate');

        return $rate instanceof ExpenseRate ? $rate : null;
    }
}
