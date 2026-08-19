<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id', 'project_id', 'budget_line_id', 'created_by', 'approved_by',
        'local_id', 'reference', 'type', 'label', 'amount', 'currency', 'project_code',
        'source', 'tranche', 'description', 'document_url', 'status', 'workflow_status',
        'submitted_at', 'approved_at', 'reconciled_at', 'version', 'is_deleted',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'version' => 'integer',
        'is_deleted' => 'boolean',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function budgetLine(): BelongsTo { return $this->belongsTo(BudgetLine::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
