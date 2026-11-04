<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $approved_amount
 */
#[Fillable(['number', 'sequence', 'suffix', 'parent_id', 'requester_id', 'bank_account_id', 'department_id', 'business_unit_id', 'project_id', 'service_description', 'service_location', 'requested_on', 'starts_on', 'ends_on', 'status', 'approved_amount', 'submitted_at', 'terms_accepted_at'])]
class Requisition extends Model
{
    use SoftDeletes;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'requested_on' => 'date',
            'starts_on' => 'date',
            'ends_on' => 'date',
            'approved_amount' => 'decimal:2',
            'submitted_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Requisition, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Requisition::class, 'parent_id');
    }

    /** @return HasMany<Requisition, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Requisition::class, 'parent_id');
    }

    /** @return BelongsTo<User, $this> */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    /** @return BelongsTo<BankAccount, $this> */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<BusinessUnit, $this> */
    public function businessUnit(): BelongsTo
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    /** @return BelongsTo<Project, $this> */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /** @return HasMany<RequisitionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** @return HasMany<ExpenseReport, $this> */
    public function expenseReports(): HasMany
    {
        return $this->hasMany(ExpenseReport::class);
    }

    /** @return MorphMany<Approval, $this> */
    public function approvals(): MorphMany
    {
        return $this->morphMany(Approval::class, 'approvable');
    }
}
