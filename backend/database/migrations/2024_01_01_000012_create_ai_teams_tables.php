<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('department')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('lead_agent_id')->nullable()->constrained('ai_agents')->nullOnDelete();
            $table->json('routing_rules')->nullable(); // keyword => agent_slug
            $table->string('status')->default('active'); // active, disabled
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['organization_id', 'slug']);
            $table->index(['organization_id', 'status']);
        });

        Schema::create('ai_team_agent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_team_id')->constrained('ai_teams')->cascadeOnDelete();
            $table->foreignId('ai_agent_id')->constrained('ai_agents')->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->string('member_role')->nullable(); // lead, specialist, reviewer
            $table->timestamps();

            $table->unique(['ai_team_id', 'ai_agent_id']);
        });

        Schema::table('ai_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_conversations', 'ai_team_id')) {
                $table->foreignId('ai_team_id')->nullable()->constrained('ai_teams')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ai_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('ai_conversations', 'ai_team_id')) {
                $table->dropConstrainedForeignId('ai_team_id');
            }
        });
        Schema::dropIfExists('ai_team_agent');
        Schema::dropIfExists('ai_teams');
    }
};
