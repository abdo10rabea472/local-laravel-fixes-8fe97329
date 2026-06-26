<?php

namespace Database\Seeders;

use App\Models\Admin;
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
     *
     * Admin / test-user credentials:
     *  - In local/testing: weak default "password" for developer convenience.
     *  - In any other environment: a strong random password is generated and
     *    printed ONCE to the console. Capture it then; it is not stored anywhere
     *    else in plain text. Re-running the seeder will create a new password
     *    only if the row does not already exist (firstOrCreate).
     */
    public function run(): void
    {
        $useDevDefault = app()->environment(['local', 'testing']);

        $adminPlain = $useDevDefault ? 'password' : Str::password(20, true, true, true, false);
        $userPlain = $useDevDefault ? 'password' : Str::password(20, true, true, true, false);

        $admin = Admin::firstOrCreate(
            ['email' => 'admin@uni.com'],
            [
                'name' => 'Admin Manager',
                'password' => Hash::make($adminPlain),
            ]
        );

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make($userPlain),
            ]
        );

        if (! $useDevDefault) {
            if ($admin->wasRecentlyCreated) {
                $this->command?->warn("Admin password (save this now, shown only once): {$adminPlain}");
            }
            if ($user->wasRecentlyCreated) {
                $this->command?->warn("Test user password (save this now, shown only once): {$userPlain}");
            }
        }

        // Seed Categories & Products
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);

        // Seed Default Static Pages
        $this->call(PageSeeder::class);

        // Seed Default Header Menu
        $this->call(HeaderMenuSeeder::class);
    }
}
