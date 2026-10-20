<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\TestWith;
use RuntimeException;
use Tests\TestCase;

class AdminUserSeederTest extends TestCase
{
    use RefreshDatabase;

    #[TestWith([null])]
    #[TestWith([''])]
    #[TestWith(['kort'])]
    public function test_seeder_rejects_an_unusable_admin_password(?string $password): void
    {
        config([
            'salon.admin.email' => 'admin@kapsalondeknip.nl',
            'salon.admin.password' => $password,
        ]);

        $this->expectException(RuntimeException::class);

        $this->seed(AdminUserSeeder::class);
    }

    public function test_seeder_rejects_a_missing_admin_email(): void
    {
        config([
            'salon.admin.email' => null,
            'salon.admin.password' => 'een-veilig-wachtwoord',
        ]);

        $this->expectException(RuntimeException::class);

        $this->seed(AdminUserSeeder::class);
    }

    public function test_seeder_creates_an_admin_from_configuration(): void
    {
        config([
            'salon.admin.email' => 'beheer@example.com',
            'salon.admin.password' => 'een-veilig-wachtwoord',
        ]);

        $this->seed(AdminUserSeeder::class);

        $admin = User::query()->where('email', 'beheer@example.com')->firstOrFail();

        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('een-veilig-wachtwoord', $admin->password));
    }
}
