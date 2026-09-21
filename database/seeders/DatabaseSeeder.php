<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $password = env('JANKATHA_SUPER_ADMIN_PASSWORD') ?: Str::random(32);

        User::updateOrCreate(['email' => env('JANKATHA_SUPER_ADMIN_EMAIL', 'superadmin@jankatha.test')], [
            'name' => env('JANKATHA_SUPER_ADMIN_NAME', 'Jankatha Super Admin'),
            'mobile' => env('JANKATHA_SUPER_ADMIN_MOBILE'),
            'password' => Hash::make($password),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $this->command?->info('Development Super Admin credentials:');
        $this->command?->info('Email: '.env('JANKATHA_SUPER_ADMIN_EMAIL', 'superadmin@jankatha.test'));
        $this->command?->info('Password: '.$password);
    }
}
