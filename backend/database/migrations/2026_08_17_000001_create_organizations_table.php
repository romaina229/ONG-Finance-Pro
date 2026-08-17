<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table): void {
            $table->id();
            $table->uuid('local_id')->nullable()->unique();
            $table->string('name');
            $table->string('code')->unique();
            $table->char('currency', 3)->default('XOF');
            $table->unsignedSmallInteger('fiscal_year');
            $table->unsignedBigInteger('version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
