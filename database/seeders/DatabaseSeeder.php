<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminPassword = env('JANKATHA_SUPER_ADMIN_PASSWORD', 'Secret@123');

        $admin = User::updateOrCreate(['email' => env('JANKATHA_SUPER_ADMIN_EMAIL', 'superadmin@jankatha.test')], [
            'name' => env('JANKATHA_SUPER_ADMIN_NAME', 'Jankatha Super Admin'),
            'mobile' => env('JANKATHA_SUPER_ADMIN_MOBILE', '9876543210'),
            'password' => Hash::make($adminPassword),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $editor = User::updateOrCreate(['email' => 'editor@jankatha.test'], [
            'name' => 'Jankatha Senior Editor',
            'mobile' => '9876543211',
            'password' => Hash::make('Secret@123'),
            'role' => 'editor',
            'status' => 'active',
        ]);

        $citizen = User::updateOrCreate(['email' => 'citizen@jankatha.test'], [
            'name' => 'आलोक शर्मा (Citizen Reporter)',
            'mobile' => '9876543212',
            'password' => Hash::make('Secret@123'),
            'role' => 'citizen',
            'status' => 'active',
        ]);

        $this->call([
            CategorySeeder::class,
            SettingSeeder::class,
            NewsSeeder::class,
            CitizenSubmissionSeeder::class,
        ]);

        $this->command?->info('Default development accounts:');
        $this->command?->info('Super Admin : superadmin@jankatha.test / '.$adminPassword);
        $this->command?->info('Editor      : editor@jankatha.test / Secret@123');
        $this->command?->info('Citizen     : citizen@jankatha.test / Secret@123');
    }
}
