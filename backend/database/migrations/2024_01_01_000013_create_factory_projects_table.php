<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * AI Factory "projects" live inside MOLIDO CORE (same DB, same tenancy).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factory_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('template')->default('ai_api');
            // ai_saas | ai_agent | rag_app | ai_api | ai_automation | internal_tool
            $table->string('status')->default('draft');
            // draft | active | paused | archived
            $table->text('description')->nullable();
            $table->json('ai_config')->nullable();      // model policy, provider prefs
            $table->json('security_config')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('default_team_id')->nullable()->constrained('ai_teams')->nullOnDelete();
            $table->foreignId('default_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->unsignedInteger('monthly_token_budget')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factory_projects');
    }
};
