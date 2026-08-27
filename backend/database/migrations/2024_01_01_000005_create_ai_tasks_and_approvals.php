<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI Tasks
        Schema::create('ai_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // requester
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('general'); // general, crm, erp, payment...
            $table->string('priority')->default('medium'); // low, medium, high, critical
            $table->string('status')->default('pending');
            // pending, working, waiting_approval, completed, failed, cancelled
            $table->json('input')->nullable();
            $table->json('result')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'agent_id']);
        });

        // Human Approvals
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained('ai_tasks')->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete(); // AI or user
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action'); // e.g. refund, delete_customer, change_permission
            $table->text('reason')->nullable();
            $table->json('payload')->nullable(); // data to execute if approved
            $table->string('status')->default('pending'); // pending, approved, rejected, expired
            $table->string('risk_level')->default('medium'); // low, medium, high, critical
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
        Schema::dropIfExists('ai_tasks');
    }
};
