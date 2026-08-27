<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions
        $permissions = [
            // CRM
            ['name' => 'crm.customer.read', 'display_name' => 'مشاهده مشتریان', 'group' => 'crm'],
            ['name' => 'crm.customer.create', 'display_name' => 'ایجاد مشتری', 'group' => 'crm'],
            ['name' => 'crm.customer.update', 'display_name' => 'ویرایش مشتری', 'group' => 'crm'],
            ['name' => 'crm.customer.delete', 'display_name' => 'حذف مشتری', 'group' => 'crm'],
            ['name' => 'crm.lead.read', 'display_name' => 'مشاهده سرنخ‌ها', 'group' => 'crm'],
            ['name' => 'crm.lead.create', 'display_name' => 'ایجاد سرنخ', 'group' => 'crm'],
            ['name' => 'crm.lead.update', 'display_name' => 'ویرایش سرنخ', 'group' => 'crm'],

            // ERP
            ['name' => 'erp.order.read', 'display_name' => 'مشاهده سفارش‌ها', 'group' => 'erp'],
            ['name' => 'erp.order.create', 'display_name' => 'ایجاد سفارش', 'group' => 'erp'],
            ['name' => 'erp.order.update', 'display_name' => 'ویرایش سفارش', 'group' => 'erp'],

            // AI
            ['name' => 'ai.chat.use', 'display_name' => 'استفاده از چت‌بات', 'group' => 'ai'],
            ['name' => 'ai.agent.execute', 'display_name' => 'اجرای ایجنت', 'group' => 'ai'],
            ['name' => 'ai.agent.approve', 'display_name' => 'تأیید اقدامات AI', 'group' => 'ai'],

            // Payment
            ['name' => 'payment.create', 'display_name' => 'ایجاد پرداخت', 'group' => 'payment'],
            ['name' => 'payment.view', 'display_name' => 'مشاهده پرداخت‌ها', 'group' => 'payment'],
            ['name' => 'payment.refund', 'display_name' => 'بازگشت وجه', 'group' => 'payment'],

            // Module
            ['name' => 'module.purchase', 'display_name' => 'خرید ماژول', 'group' => 'module'],
            ['name' => 'module.activate', 'display_name' => 'فعال‌سازی ماژول', 'group' => 'module'],

            // Admin
            ['name' => 'admin.users.manage', 'display_name' => 'مدیریت کاربران', 'group' => 'admin'],
            ['name' => 'admin.roles.manage', 'display_name' => 'مدیریت نقش‌ها', 'group' => 'admin'],
            ['name' => 'admin.settings.manage', 'display_name' => 'مدیریت تنظیمات', 'group' => 'admin'],
            ['name' => 'admin.audit.view', 'display_name' => 'مشاهده لاگ‌ها', 'group' => 'admin'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Roles
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'سوپر ادمین',
                'description' => 'دسترسی کامل به تمام سیستم',
                'is_system' => true,
                'permissions' => Permission::pluck('name')->toArray(),
            ],
            [
                'name' => 'admin',
                'display_name' => 'ادمین',
                'description' => 'مدیر سازمان',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.customer.create', 'crm.customer.update', 'crm.customer.delete',
                    'crm.lead.read', 'crm.lead.create', 'crm.lead.update',
                    'erp.order.read', 'erp.order.create', 'erp.order.update',
                    'ai.chat.use', 'ai.agent.execute', 'ai.agent.approve',
                    'payment.create', 'payment.view', 'payment.refund',
                    'module.purchase', 'module.activate',
                    'admin.users.manage', 'admin.audit.view',
                ],
            ],
            [
                'name' => 'manager',
                'display_name' => 'مدیر',
                'description' => 'مدیر بخش',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.customer.create', 'crm.customer.update',
                    'crm.lead.read', 'crm.lead.create', 'crm.lead.update',
                    'erp.order.read', 'erp.order.create',
                    'ai.chat.use', 'ai.agent.execute',
                    'payment.view',
                ],
            ],
            [
                'name' => 'employee',
                'display_name' => 'کارمند',
                'description' => 'کارمند عادی',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.lead.read',
                    'erp.order.read',
                    'ai.chat.use',
                ],
            ],
            [
                'name' => 'sales',
                'display_name' => 'فروش',
                'description' => 'تیم فروش',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.customer.create', 'crm.customer.update',
                    'crm.lead.read', 'crm.lead.create', 'crm.lead.update',
                    'ai.chat.use', 'ai.agent.execute',
                ],
            ],
            [
                'name' => 'support',
                'display_name' => 'پشتیبانی',
                'description' => 'تیم پشتیبانی',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.customer.update',
                    'ai.chat.use', 'ai.agent.execute',
                ],
            ],
            [
                'name' => 'accountant',
                'display_name' => 'حسابدار',
                'description' => 'بخش مالی',
                'is_system' => true,
                'permissions' => [
                    'erp.order.read',
                    'payment.view', 'payment.refund',
                    'ai.chat.use',
                ],
            ],
            [
                'name' => 'customer',
                'display_name' => 'مشتری',
                'description' => 'کاربر مشتری',
                'is_system' => true,
                'permissions' => [
                    'ai.chat.use',
                    'module.purchase',
                ],
            ],
            [
                'name' => 'ai_agent',
                'display_name' => 'ایجنت هوش مصنوعی',
                'description' => 'نقش سیستم برای ایجنت‌های AI',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.lead.read',
                    'erp.order.read',
                    'ai.chat.use',
                ],
            ],
            [
                'name' => 'virtual_employee',
                'display_name' => 'کارمند مجازی',
                'description' => 'کارمند مجازی AI Workforce',
                'is_system' => true,
                'permissions' => [
                    'crm.customer.read', 'crm.lead.read',
                    'ai.chat.use', 'ai.agent.execute',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $perms = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate(['name' => $roleData['name']], $roleData);

            $permissionIds = Permission::whereIn('name', $perms)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
