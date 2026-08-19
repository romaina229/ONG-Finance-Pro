<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'currency', 'fiscal_year'];
    protected $casts = ['fiscal_year' => 'integer'];
    public function transactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
    public function projects(): HasMany { return $this->hasMany(Project::class); }
    public function users(): BelongsToMany { return $this->belongsToMany(User::class)->withPivot('role_id')->withTimestamps(); }
}
