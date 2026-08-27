<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modules catalog
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->default('1.0.0');
            $table->string('category')->nullable(); // crm, erp, ai, marketing, finance...
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency')->default('IRR');
            $table->string('billing_type')->default('free');
            // free, one_time, monthly, yearly, trial, subscription, enterprise
            $table->unsignedInteger('trial_days')->default(0);
            $table->string('status')->default('active'); // active, inactive, coming_soon
            $table->string('compatibility')->nullable();
            $table->json('assets')->nullable(); // icons, screenshots
            $table->boolean('is_core')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'category']);
        });

        // Organization entitlements (access to modules)
        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('active');
            // active, trial, expired, suspended, cancelled
            $table->string('source')->nullable(); // purchase, trial, grant, subscription
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['organization_id', 'module_id']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('modules');
    }
};
