<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sync_operations', function (Blueprint $table) {
            $table->id();
            $table->uuid('operation_id')->unique();
            $table->uuid('organization_id');
            $table->string('entity_type', 100);
            $table->uuid('local_id');
            $table->uuid('server_id')->nullable();
            $table->string('action', 20);
            $table->unsignedBigInteger('base_version')->nullable();
            $table->json('payload');
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index(['entity_type', 'local_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_operations');
    }
};
