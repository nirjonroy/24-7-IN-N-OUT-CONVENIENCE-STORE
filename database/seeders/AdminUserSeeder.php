<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the single admin account intentionally.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('admin.email')],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );
    }
}
