<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $aiModule = Module::where('slug', 'ai-workforce')->first();
        $analytics = Module::where('slug', 'advanced-analytics')->first();

        $plans = [
            [
                'name' => 'AI Workforce ماهانه',
                'slug' => 'ai-workforce-monthly',
                'description' => 'دسترسی ماهانه به کارمندان مجازی',
                'price' => 490000,
                'currency' => 'IRR',
                'interval' => 'monthly',
                'trial_days' => 14,
                'features' => ['agents', 'tasks', 'approvals'],
                'module_id' => $aiModule?->id,
                'is_active' => true,
            ],
            [
                'name' => 'AI Workforce سالانه',
                'slug' => 'ai-workforce-yearly',
                'description' => 'دسترسی سالانه (۲ ماه رایگان)',
                'price' => 4900000,
                'currency' => 'IRR',
                'interval' => 'yearly',
                'trial_days' => 14,
                'features' => ['agents', 'tasks', 'approvals'],
                'module_id' => $aiModule?->id,
                'is_active' => true,
            ],
            [
                'name' => 'Analytics ماهانه',
                'slug' => 'analytics-monthly',
                'description' => 'گزارش و داشبورد پیشرفته',
                'price' => 290000,
                'currency' => 'IRR',
                'interval' => 'monthly',
                'trial_days' => 7,
                'features' => ['reports', 'dashboards'],
                'module_id' => $analytics?->id,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $data) {
            Plan::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
