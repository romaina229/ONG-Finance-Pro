<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    protected $fillable = [
        'organization_id', 'local_id', 'reference', 'type', 'label',
        'amount', 'currency', 'project_code', 'status', 'version', 'is_deleted',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'version' => 'integer',
        'is_deleted' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
