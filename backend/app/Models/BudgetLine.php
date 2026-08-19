<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetLine extends Model
{
    protected $fillable = ['project_id', 'code', 'category', 'allocated_amount', 'committed_amount', 'spent_amount'];
    protected $casts = ['allocated_amount' => 'decimal:2', 'committed_amount' => 'decimal:2', 'spent_amount' => 'decimal:2'];
    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function transactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
}
