<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            // null organization_id = system-wide knowledge
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('category')->nullable(); // faq, policy, product, instruction, internal, ai_instruction
            $table->string('type')->default('article'); // article, faq, document, instruction
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->json('tags')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('is_public')->default(false); // visible to customers
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organization_id', 'category', 'is_published']);
            $table->index(['organization_id', 'type']);
            // Full-text ready (MySQL)
            // $table->fullText(['title', 'summary', 'content']); // enable when using MyISAM/InnoDB fulltext
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
