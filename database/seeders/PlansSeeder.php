<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'trial',
                'name' => 'Free Trial',
                'description' => 'Try the platform free for 14 days. No credit card required.',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'max_tours' => 5,
                'max_bookings_per_month' => 10,
                'max_admin_users' => 1,
                'has_diy_builder' => false,
                'has_custom_domain' => false,
                'has_api_access' => false,
                'has_advanced_reports' => false,
                'has_email_marketing' => false,
                'has_priority_support' => false,
                'is_active' => true,
                'sort_order' => 0,
            ],
            [
                'slug' => 'starter',
                'name' => 'Starter',
                'description' => 'Perfect for small travel agencies just getting started.',
                'monthly_price' => 49,
                'yearly_price' => 490,
                'max_tours' => 25,
                'max_bookings_per_month' => 100,
                'max_admin_users' => 3,
                'has_diy_builder' => false,
                'has_custom_domain' => false,
                'has_api_access' => false,
                'has_advanced_reports' => false,
                'has_email_marketing' => false,
                'has_priority_support' => false,
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'slug' => 'professional',
                'name' => 'Professional',
                'description' => 'For growing agencies with advanced needs.',
                'monthly_price' => 99,
                'yearly_price' => 990,
                'max_tours' => 100,
                'max_bookings_per_month' => 500,
                'max_admin_users' => 10,
                'has_diy_builder' => true,
                'has_custom_domain' => true,
                'has_api_access' => false,
                'has_advanced_reports' => true,
                'has_email_marketing' => true,
                'has_priority_support' => false,
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'slug' => 'enterprise',
                'name' => 'Enterprise',
                'description' => 'Unlimited power for large travel agencies.',
                'monthly_price' => 249,
                'yearly_price' => 2490,
                'max_tours' => -1, // unlimited
                'max_bookings_per_month' => -1, // unlimited
                'max_admin_users' => -1, // unlimited
                'has_diy_builder' => true,
                'has_custom_domain' => true,
                'has_api_access' => true,
                'has_advanced_reports' => true,
                'has_email_marketing' => true,
                'has_priority_support' => true,
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
