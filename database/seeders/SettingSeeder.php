<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'Jankatha.com', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'REAL STORIES. REAL PEOPLE.', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'newsroom@jankatha.com', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+91 98765 43210', 'group' => 'contact'],
            ['key' => 'contact_address', 'value' => 'Bilaspur, Chhattisgarh - 495001', 'group' => 'contact'],
            ['key' => 'whatsapp_helpline', 'value' => '+91 98765 43210', 'group' => 'contact'],
            ['key' => 'meta_title', 'value' => 'Jankatha.com | Real Stories. Real People.', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Jankatha is Chhattisgarh’s citizen-driven news portal bringing grassroots journalism, breaking news, local reports, and community voices.', 'group' => 'seo'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
