<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $subtotal
 * @property string $tax_amount
 * @property string $total_amount
 */
#[Fillable(['expense_report_id', 'expense_type_id', 'line_number', 'receipt_type', 'invoice_number', 'cfdi_uuid', 'issuer_rfc', 'vendor_name', 'incurred_on', 'subtotal', 'tax_amount', 'total_amount', 'justification', 'xml_path', 'receipt_path'])]
class Expense extends Model
{
    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'line_number' => 'integer',
            'incurred_on' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<ExpenseReport, $this> */
    public function expenseReport(): BelongsTo
    {
        return $this->belongsTo(ExpenseReport::class);
    }

    /** @return BelongsTo<ExpenseType, $this> */
    public function expenseType(): BelongsTo
    {
        return $this->belongsTo(ExpenseType::class);
    }
}
