<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $fillable = ['name', 'code', 'currency', 'fiscal_year'];

    protected $casts = ['fiscal_year' => 'integer'];

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class);
    }
}
