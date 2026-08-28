<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\DealController;
use App\Http\Controllers\Erp\ProductController;
use App\Http\Controllers\Erp\OrderController;
use App\Http\Controllers\AI\ChatController;
use App\Http\Controllers\AI\AgentController;
use App\Http\Controllers\AI\TaskController;
use App\Http\Controllers\AI\ApprovalController;
use App\Http\Controllers\Knowledge\KnowledgeController;
use App\Http\Controllers\Marketplace\ModuleController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Subscription\SubscriptionController;
use App\Http\Controllers\Admin\FeatureFlagController;
use App\Http\Controllers\Admin\MailTestController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - MOLIDO CORE
|--------------------------------------------------------------------------
|
| Versioned API: /api/v1/...
|
*/

Route::prefix('v1')->group(function () {

    // Public Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgot']);
        Route::post('/reset-password', [PasswordResetController::class, 'reset']);
    });

    // Public health
    Route::get('/health', HealthController::class);

    // Public payment callback (providers + mock)
    Route::get('/payments/mock-callback', [PaymentController::class, 'mockCallback']);
    Route::get('/payments/zarinpal-callback', [PaymentController::class, 'zarinpalCallback']);
    Route::post('/payments/verify', [PaymentController::class, 'verify']);

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // Customer Center (central identity)
        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])
                ->middleware('permission:crm.customer.read');
            Route::post('/', [CustomerController::class, 'store'])
                ->middleware('permission:crm.customer.create');
            Route::get('/{id}', [CustomerController::class, 'show'])
                ->middleware('permission:crm.customer.read');
            Route::put('/{id}', [CustomerController::class, 'update'])
                ->middleware('permission:crm.customer.update');
            Route::delete('/{id}', [CustomerController::class, 'destroy'])
                ->middleware('permission:crm.customer.delete');
        });

        // CRM — Leads
        Route::prefix('crm/leads')->group(function () {
            Route::get('/', [LeadController::class, 'index'])
                ->middleware('permission:crm.lead.read');
            Route::post('/', [LeadController::class, 'store'])
                ->middleware('permission:crm.lead.create');
            Route::get('/{id}', [LeadController::class, 'show'])
                ->middleware('permission:crm.lead.read');
            Route::put('/{id}', [LeadController::class, 'update'])
                ->middleware('permission:crm.lead.update');
            Route::delete('/{id}', [LeadController::class, 'destroy'])
                ->middleware('permission:crm.lead.update');
            Route::post('/{id}/convert', [LeadController::class, 'convert'])
                ->middleware('permission:crm.lead.update');
        });

        // CRM — Deals
        Route::prefix('crm/deals')->group(function () {
            Route::get('/', [DealController::class, 'index'])
                ->middleware('permission:crm.customer.read');
            Route::post('/', [DealController::class, 'store'])
                ->middleware('permission:crm.customer.create');
            Route::get('/{id}', [DealController::class, 'show'])
                ->middleware('permission:crm.customer.read');
            Route::put('/{id}', [DealController::class, 'update'])
                ->middleware('permission:crm.customer.update');
            Route::delete('/{id}', [DealController::class, 'destroy'])
                ->middleware('permission:crm.customer.update');
        });

        // ERP — Products
        Route::prefix("erp/products")->group(function () {
            Route::get("/", [ProductController::class, "index"])
                ->middleware("permission:erp.order.read");
            Route::post("/", [ProductController::class, "store"])
                ->middleware("permission:erp.order.create");
            Route::get("/{id}", [ProductController::class, "show"])
                ->middleware("permission:erp.order.read");
            Route::put("/{id}", [ProductController::class, "update"])
                ->middleware("permission:erp.order.update");
            Route::delete("/{id}", [ProductController::class, "destroy"])
                ->middleware("permission:erp.order.update");
        });

        // ERP — Orders
        Route::prefix("erp/orders")->group(function () {
            Route::get("/", [OrderController::class, "index"])
                ->middleware("permission:erp.order.read");
            Route::post("/", [OrderController::class, "store"])
                ->middleware("permission:erp.order.create");
            Route::get("/{id}", [OrderController::class, "show"])
                ->middleware("permission:erp.order.read");
            Route::put("/{id}", [OrderController::class, "update"])
                ->middleware("permission:erp.order.update");
            Route::delete("/{id}", [OrderController::class, "destroy"])
                ->middleware("permission:erp.order.update");
        });









        // Dashboard stats
        Route::get("/organization", [OrganizationController::class, "show"]);
        Route::put("/organization", [OrganizationController::class, "update"]);
        Route::get("/dashboard/stats", [DashboardController::class, "stats"]);

        // Audit logs
        Route::get("/audit-logs", [AuditController::class, "index"]);


        // Mail test (admin)
        Route::post('/mail/test', [MailTestController::class, 'test']);

        // Feature Flags
        Route::prefix("feature-flags")->group(function () {
            Route::get("/", [FeatureFlagController::class, "index"]);
            Route::get("/enabled", [FeatureFlagController::class, "enabled"]);
            Route::get("/check/{key}", [FeatureFlagController::class, "check"]);
            Route::put("/{key}", [FeatureFlagController::class, "update"]);
        });

        // Subscriptions
        Route::prefix("subscriptions")->group(function () {
            Route::get("/plans", [SubscriptionController::class, "plans"]);
            Route::get("/", [SubscriptionController::class, "index"]);
            Route::post("/subscribe", [SubscriptionController::class, "subscribe"]);
            Route::post("/{id}/cancel", [SubscriptionController::class, "cancel"]);
        });

        // Payments
        Route::prefix("payments")->group(function () {
            Route::get("/", [PaymentController::class, "index"])
                ->middleware("permission:payment.view");
            Route::post("/initiate", [PaymentController::class, "initiate"])
                ->middleware("permission:payment.create");
            Route::get("/{uuid}", [PaymentController::class, "show"])
                ->middleware("permission:payment.view");
        });

        // Module Marketplace
        Route::prefix("modules")->group(function () {
            Route::get("/", [ModuleController::class, "index"]);
            Route::get("/my", [ModuleController::class, "myModules"]);
            Route::get("/{slug}", [ModuleController::class, "show"]);
            Route::post("/{slug}/activate", [ModuleController::class, "activate"])
                ->middleware("permission:module.activate");
        });

        // Knowledge Base + Lightweight RAG
        Route::prefix("knowledge")->group(function () {
            Route::get("/", [KnowledgeController::class, "index"]);
            Route::post("/", [KnowledgeController::class, "store"]);
            Route::get("/search", [KnowledgeController::class, "search"]);
            Route::get("/retrieve", [KnowledgeController::class, "retrieve"]);
            Route::get("/{id}", [KnowledgeController::class, "show"]);
            Route::put("/{id}", [KnowledgeController::class, "update"]);
            Route::delete("/{id}", [KnowledgeController::class, "destroy"]);
        });

        // AI Tasks (Inbox)
        Route::prefix("ai/tasks")->group(function () {
            Route::get("/", [TaskController::class, "index"])
                ->middleware("permission:ai.agent.execute");
            Route::post("/", [TaskController::class, "store"])
                ->middleware("permission:ai.agent.execute");
            Route::get("/{id}", [TaskController::class, "show"])
                ->middleware("permission:ai.agent.execute");
            Route::patch("/{id}/status", [TaskController::class, "updateStatus"])
                ->middleware("permission:ai.agent.execute");
        });

        // Human Approvals
        Route::prefix("ai/approvals")->group(function () {
            Route::get("/", [ApprovalController::class, "index"])
                ->middleware("permission:ai.agent.approve");
            Route::post("/", [ApprovalController::class, "store"])
                ->middleware("permission:ai.agent.execute");
            Route::post("/{id}/review", [ApprovalController::class, "review"])
                ->middleware("permission:ai.agent.approve");
        });

        // AI Agents
        Route::prefix("ai/agents")->group(function () {
            Route::get("/", [AgentController::class, "index"])
                ->middleware("permission:ai.chat.use");
            Route::get("/templates", [AgentController::class, "templates"])
                ->middleware("permission:ai.chat.use");
            Route::post("/", [AgentController::class, "store"]);
            Route::put("/{id}", [AgentController::class, "update"]);
            Route::delete("/{id}", [AgentController::class, "destroy"]);
            Route::get("/{slug}", [AgentController::class, "show"])
                ->middleware("permission:ai.chat.use");
        });

        // AI Chat
        Route::prefix("ai")->group(function () {
            Route::get("/conversations", [ChatController::class, "conversations"])
                ->middleware("permission:ai.chat.use");
            Route::post("/conversations", [ChatController::class, "storeConversation"])
                ->middleware("permission:ai.chat.use");
            Route::get("/conversations/{id}/messages", [ChatController::class, "messages"])
                ->middleware("permission:ai.chat.use");
            Route::post("/conversations/{id}/send", [ChatController::class, "send"])
                ->middleware("permission:ai.chat.use");
        });
    });
});
