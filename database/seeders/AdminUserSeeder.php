<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * Maakt het beheerdersaccount.
 * E-mail en wachtwoord komen uit config (ADMIN_EMAIL en ADMIN_PASSWORD in .env).
 * Zonder een zelf gekozen wachtwoord van minimaal 12 tekens stopt de seeder.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('salon.admin.email');
        $password = config('salon.admin.password');

        if (! is_string($email) || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new RuntimeException('Zet een geldig ADMIN_EMAIL in .env voordat je de beheerder seedt.');
        }

        if (! is_string($password) || strlen($password) < 12) {
            throw new RuntimeException('Zet ADMIN_PASSWORD in .env. Kies een wachtwoord van minimaal 12 tekens. Er is geen standaardwachtwoord.');
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Kapper',
                'password' => $password,
                'email_verified_at' => now(),
                'is_admin' => true,
            ],
        );
    }
}
