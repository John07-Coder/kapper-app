<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Maakt het beheerdersaccount.
 * E-mail en wachtwoord komen uit .env (ADMIN_EMAIL en ADMIN_PASSWORD).
 * updateOrCreate zoekt op e-mail: bestaat de gebruiker al, dan wordt het wachtwoord bijgewerkt.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => env('ADMIN_EMAIL') ?: 'admin@kapsalondeknip.nl'],
            [
                'name' => 'Kapper',
                'password' => Hash::make(env('ADMIN_PASSWORD') ?: 'wachtwoord'),
                'email_verified_at' => now(),
            ],
        );
    }
}
