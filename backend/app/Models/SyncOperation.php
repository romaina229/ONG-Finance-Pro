<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SyncOperation extends Model
{
    protected $fillable = [
        'operation_id', 'organization_id', 'entity_type', 'local_id',
        'server_id', 'action', 'base_version', 'payload', 'status',
        'error_message', 'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $operation) {
            $operation->operation_id ??= (string) Str::uuid();
        });
    }
}
