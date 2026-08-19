<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('code', 100);
            $table->string('category');
            $table->decimal('allocated_amount', 20, 2)->default(0);
            $table->decimal('committed_amount', 20, 2)->default(0);
            $table->decimal('spent_amount', 20, 2)->default(0);
            $table->timestamps();
            $table->unique(['project_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_lines');
    }
};
