<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->foreignId('project_id')->nullable()->after('organization_id')->constrained('projects')->nullOnDelete();
            $table->foreignId('budget_line_id')->nullable()->after('project_id')->constrained('budget_lines')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->after('budget_line_id')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->string('source')->nullable()->after('project_code');
            $table->string('tranche')->nullable()->after('source');
            $table->text('description')->nullable()->after('tranche');
            $table->string('document_url')->nullable()->after('description');
            $table->timestamp('submitted_at')->nullable()->after('document_url');
            $table->timestamp('approved_at')->nullable()->after('submitted_at');
            $table->timestamp('reconciled_at')->nullable()->after('approved_at');
            $table->string('workflow_status', 30)->default('draft')->after('status');
            $table->index(['organization_id', 'project_id', 'type']);
            $table->index(['organization_id', 'workflow_status']);
        });
    }

    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['budget_line_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['project_id', 'budget_line_id', 'created_by', 'approved_by', 'source', 'tranche', 'description', 'document_url', 'submitted_at', 'approved_at', 'reconciled_at', 'workflow_status']);
        });
    }
};
