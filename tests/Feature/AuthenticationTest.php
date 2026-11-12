<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_a_hashed_password_and_user_role(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();
        $user = User::where('email', 'new-user@example.com')->firstOrFail();
        $this->assertSame('user', $user->role);
        $this->assertNotSame('secure-pass-123', $user->password);
        $this->assertTrue(Hash::check('secure-pass-123', $user->password));
    }

    public function test_registration_validates_name_email_password_and_confirmation(): void
    {
        $this->from('/')
            ->post(route('register.store'), [
                'auth_form' => 'register',
                'name' => '',
                'email' => 'not-an-email',
                'password' => 'short',
                'password_confirmation' => 'different',
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['name', 'email', 'password']);

        $this->assertGuest();
    }

    public function test_registration_rejects_duplicate_email_and_short_password(): void
    {
        $this->post(route('register.store'), [
            'name' => 'First User',
            'email' => 'duplicate@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ]);
        $this->post(route('logout'));

        $this->from('/')
            ->post(route('register.store'), [
                'auth_form' => 'register',
                'name' => 'Second User',
                'email' => 'duplicate@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_login_returns_a_generic_error_for_invalid_credentials(): void
    {
        $this->from('/')
            ->post(route('login.authenticate'), [
                'auth_form' => 'login',
                'email' => 'unknown@example.com',
                'password' => 'incorrect',
            ])
            ->assertRedirect('/')
            ->assertSessionHasErrors(['email']);

        $this->assertSame(
            'Email atau password yang Anda masukkan salah.',
            session('errors')->getBag('default')->first('email')
        );
        $this->assertGuest();
    }

    public function test_admin_is_redirected_to_the_admin_dashboard_after_login(): void
    {
        $role = Role::create(['name' => 'admin']);
        $profile = Profile::create(['umur' => 0, 'bio' => '', 'alamat' => '']);
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('secure-pass-123'),
            'role' => 'admin',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]);

        $this->post(route('login.authenticate'), [
            'email' => $admin->email,
            'password' => 'secure-pass-123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        view()->share('genres', collect());
        $this->get(route('admin.dashboard'))->assertOk();
    }

    public function test_regular_user_is_redirected_home_after_login(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ]);
        $user = User::where('email', 'new-user@example.com')->firstOrFail();
        $this->post(route('logout'));

        $this->post(route('login.authenticate'), [
            'email' => $user->email,
            'password' => 'secure-pass-123',
        ])->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_logout_ends_the_authenticated_session(): void
    {
        $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'secure-pass-123',
            'password_confirmation' => 'secure-pass-123',
        ])->assertRedirect(route('home'));

        $this->post(route('logout'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('status', 'Anda berhasil keluar.');

        $this->assertGuest();
    }
}
