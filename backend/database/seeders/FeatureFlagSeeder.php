<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        $flags = [
            'CHATBOT_ENABLED' => true,
            'AI_WORKFORCE_ENABLED' => true,
            'CRM_ENABLED' => true,
            'ERP_ENABLED' => true,
            'MARKETPLACE_ENABLED' => true,
            'PAYMENT_ENABLED' => true,
            'SUBSCRIPTION_ENABLED' => true,
            'VOICE_ENABLED' => false,
            'ADVANCED_RAG_ENABLED' => false,
        ];

        foreach ($flags as $key => $enabled) {
            FeatureFlag::firstOrCreate(
                ['key' => $key],
                ['enabled' => $enabled, 'config' => null]
            );
        }
    }
}
