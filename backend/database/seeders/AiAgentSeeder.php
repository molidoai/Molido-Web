<?php

namespace Database\Seeders;

use App\Models\AiAgent;
use Illuminate\Database\Seeder;

class AiAgentSeeder extends Seeder
{
    public function run(): void
    {
        $agents = [
            [
                'name' => 'General Assistant',
                'slug' => 'general',
                'role' => 'general',
                'department' => 'Core',
                'description' => 'دستیار عمومی MOLIDO',
                'system_instructions' => 'You are the general MOLIDO AI assistant. Help users with navigation, basic questions, and route them to specialized agents when needed.',
                'skills' => ['general_qa', 'routing'],
                'tools' => [],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'Sales AI',
                'slug' => 'sales',
                'role' => 'sales',
                'department' => 'Sales',
                'description' => 'مدیریت سرنخ، پیگیری مشتری و کمک فروش',
                'system_instructions' => 'You are Sales AI. Help with leads, deals, customer follow-up and sales assistance. Suggest next actions but never invent deal values.',
                'skills' => ['lead_management', 'follow_up', 'sales_assistance'],
                'tools' => ['CRMTool', 'CustomerTool'],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'Support AI',
                'slug' => 'support',
                'role' => 'support',
                'department' => 'Support',
                'description' => 'پشتیبانی مشتری، FAQ و تیکت',
                'system_instructions' => 'You are Support AI. Help customers with issues, create tickets when needed, and classify problems. Be polite and clear.',
                'skills' => ['faq', 'ticket_creation', 'issue_classification'],
                'tools' => ['TicketTool', 'KnowledgeTool', 'CustomerTool'],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'CRM AI',
                'slug' => 'crm',
                'role' => 'crm',
                'department' => 'CRM',
                'description' => 'تحلیل مشتری و سازمان‌دهی سرنخ',
                'system_instructions' => 'You are CRM AI. Analyze customers, organize leads, and recommend follow-ups based on available data only.',
                'skills' => ['customer_analysis', 'lead_organization', 'follow_up_recommendations'],
                'tools' => ['CRMTool', 'CustomerTool'],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'ERP AI',
                'slug' => 'erp',
                'role' => 'erp',
                'department' => 'Operations',
                'description' => 'کمک موجودی، سفارش و گزارش عملیاتی',
                'system_instructions' => 'You are ERP AI. Assist with inventory, orders and operational reports. Never change stock without verification.',
                'skills' => ['inventory_assistance', 'order_assistance', 'operational_reports'],
                'tools' => ['ERPTool', 'ReportTool'],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'Marketing AI',
                'slug' => 'marketing',
                'role' => 'marketing',
                'department' => 'Marketing',
                'description' => 'کمک کمپین و بخش‌بندی',
                'system_instructions' => 'You are Marketing AI. Help with campaign ideas, segmentation and marketing analysis.',
                'skills' => ['campaign_assistance', 'segmentation', 'marketing_analysis'],
                'tools' => [],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'Finance AI',
                'slug' => 'finance',
                'role' => 'finance',
                'department' => 'Finance',
                'description' => 'گزارش مالی و تحلیل هزینه',
                'system_instructions' => 'You are Finance AI. Provide financial reports and expense analysis. Never execute payments or refunds without human approval.',
                'skills' => ['financial_reports', 'expense_analysis'],
                'tools' => ['ReportTool', 'PaymentTool'],
                'status' => 'available',
                'is_system' => true,
            ],
            [
                'name' => 'Technical AI',
                'slug' => 'technical',
                'role' => 'technical',
                'department' => 'Technical',
                'description' => 'تحلیل خطا و لاگ',
                'system_instructions' => 'You are Technical AI. Help analyze errors, logs and technical tasks.',
                'skills' => ['error_analysis', 'log_analysis', 'technical_tasks'],
                'tools' => [],
                'status' => 'available',
                'is_system' => true,
            ],
        ];

        foreach ($agents as $data) {
            AiAgent::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
