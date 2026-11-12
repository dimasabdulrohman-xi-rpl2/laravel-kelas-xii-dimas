<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $admin = User::where('email', $email)->first();
        $profile = $admin?->profile_id
            ? Profile::find($admin->profile_id)
            : null;
        $profile ??= Profile::create([
            'alamat' => 'Administrator',
            'umur' => 0,
            'bio' => 'administrator',
        ]);

        User::updateOrCreate([
            'email' => $email,
        ], [
            'name' => env('ADMIN_NAME', 'admin'),
            'email_verified_at' => now(),
            'password' => Hash::make(env('ADMIN_PASSWORD', '12345678')),
            'role' => 'admin',
            'role_id' => Role::firstOrCreate(['name' => 'admin'])->id,
            'profile_id' => $profile->id,
        ]);
    }
}
