<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'CRM Pro',
                'slug' => 'crm-pro',
                'version' => '1.0.0',
                'category' => 'crm',
                'description' => 'مدیریت پیشرفته مشتری، سرنخ و معامله',
                'features' => ['leads', 'deals', 'pipeline', 'activities'],
                'price' => 0,
                'billing_type' => 'free',
                'trial_days' => 0,
                'status' => 'active',
                'is_core' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'ERP Lite',
                'slug' => 'erp-lite',
                'version' => '1.0.0',
                'category' => 'erp',
                'description' => 'محصول، موجودی و سفارش',
                'features' => ['products', 'inventory', 'orders'],
                'price' => 0,
                'billing_type' => 'free',
                'trial_days' => 0,
                'status' => 'active',
                'is_core' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'AI Workforce',
                'slug' => 'ai-workforce',
                'version' => '1.0.0',
                'category' => 'ai',
                'description' => 'کارمندان مجازی و ایجنت‌های تخصصی',
                'features' => ['agents', 'tasks', 'approvals'],
                'price' => 490000,
                'currency' => 'IRR',
                'billing_type' => 'monthly',
                'trial_days' => 14,
                'status' => 'active',
                'is_core' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Advanced Analytics',
                'slug' => 'advanced-analytics',
                'version' => '1.0.0',
                'category' => 'analytics',
                'description' => 'گزارش‌ها و داشبورد پیشرفته',
                'features' => ['reports', 'dashboards', 'export'],
                'price' => 290000,
                'currency' => 'IRR',
                'billing_type' => 'monthly',
                'trial_days' => 7,
                'status' => 'active',
                'sort_order' => 4,
            ],
            [
                'name' => 'Marketing Hub',
                'slug' => 'marketing-hub',
                'version' => '1.0.0',
                'category' => 'marketing',
                'description' => 'کمپین و بخش‌بندی مشتری',
                'features' => ['campaigns', 'segments'],
                'price' => 390000,
                'currency' => 'IRR',
                'billing_type' => 'monthly',
                'trial_days' => 14,
                'status' => 'coming_soon',
                'sort_order' => 5,
            ],
        ];

        foreach ($modules as $data) {
            Module::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
