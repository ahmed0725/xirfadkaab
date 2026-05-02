<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@xirfadkaab.test'],
            [
                'name' => 'System Admin',
                'role' => 'admin',
                'password' => bcrypt('password'),
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'user@xirfadkaab.test'],
            [
                'name' => 'Default User',
                'role' => 'user',
                'password' => bcrypt('password'),
            ]
        );

        $this->call(SchoolDataSeeder::class);
    }
}
