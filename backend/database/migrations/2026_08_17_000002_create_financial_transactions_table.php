<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->uuid('local_id')->nullable()->unique();
            $table->string('reference')->unique();
            $table->enum('type', ['expense', 'revenue']);
            $table->string('label');
            $table->decimal('amount', 20, 2);
            $table->char('currency', 3)->default('XOF');
            $table->string('project_code')->nullable();
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('version')->default(1);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['organization_id', 'type', 'status']);
            $table->index(['organization_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
