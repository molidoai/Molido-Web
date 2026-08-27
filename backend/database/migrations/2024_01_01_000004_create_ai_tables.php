<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // AI Conversations
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable();
            $table->string('status')->default('active'); // active, archived, closed
            $table->string('mode')->default('answer'); // answer, action
            $table->json('context')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'user_id']);
        });

        // AI Messages
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->string('role'); // user, assistant, system, tool
            $table->text('content');
            $table->json('metadata')->nullable(); // tokens, model, tool_calls...
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->timestamps();

            $table->index('conversation_id');
        });

        // AI Usage / Cost tracking
        Schema::create('ai_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('agent')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('estimated_cost', 12, 6)->default(0);
            $table->string('request_type')->nullable(); // chat, tool, embedding
            $table->timestamps();

            $table->index(['organization_id', 'created_at']);
        });

        // AI Agents (Workforce foundation)
        Schema::create('ai_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('role')->nullable(); // sales, support, crm, erp...
            $table->string('department')->nullable();
            $table->text('description')->nullable();
            $table->text('system_instructions')->nullable();
            $table->json('skills')->nullable();
            $table->json('tools')->nullable();
            $table->json('permissions')->nullable();
            $table->string('status')->default('available'); // available, working, waiting_approval, paused, error, disabled
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_agents');
        Schema::dropIfExists('ai_usages');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
    }
};
