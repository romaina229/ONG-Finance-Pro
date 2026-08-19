<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = ['organization_id', 'code', 'name', 'donor', 'budget_amount', 'spent_amount', 'status', 'start_date', 'end_date'];

    protected $casts = ['budget_amount' => 'decimal:2', 'spent_amount' => 'decimal:2', 'start_date' => 'date', 'end_date' => 'date'];

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function budgetLines(): HasMany { return $this->hasMany(BudgetLine::class); }
    public function transactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
}
