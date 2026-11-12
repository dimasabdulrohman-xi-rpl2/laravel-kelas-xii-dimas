<?php

namespace Tests\Feature;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Kritik;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        view()->share('genres', collect());
    }

    public function test_guest_is_redirected_and_regular_user_is_forbidden_from_admin_routes(): void
    {
        $film = $this->createFilm();
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->post(route('admin.films.store'))->assertRedirect(route('login'));

        $this->actingAs($this->createUser());
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.films.create'))->assertForbidden();
        $this->post(route('admin.films.store'))->assertForbidden();
        $this->get(route('admin.films.edit', $film))->assertForbidden();
        $this->put(route('admin.films.update', $film))->assertForbidden();
        $this->delete(route('admin.films.destroy', $film))->assertForbidden();
    }

    public function test_admin_sees_dashboard_link_in_public_site_header_but_regular_user_does_not(): void
    {
        $this->actingAs($this->createAdmin());
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('admin.dashboard'), false)
            ->assertSee('>Dashboard</a>', false);

        $this->actingAs($this->createUser());
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('admin.dashboard'), false);
    }

    public function test_admin_can_create_update_and_delete_a_film_and_its_related_data(): void
    {
        $this->actingAs($this->createAdmin());
        $genre = Genre::create(['name' => 'Drama']);
        $cast = Cast::create(['name' => 'Ayu Pratiwi', 'age' => 28, 'bio' => 'Aktor']);

        $this->post(route('admin.films.store'), [
            'title' => 'Film CRUD',
            'sinopsis' => 'Sinopsis awal.',
            'year' => now()->year,
            'poster' => 'posters/crud.jpg',
            'genre_id' => $genre->id,
            'cast_ids' => [$cast->id],
            'actors' => ['Sari'],
        ])->assertRedirect(route('admin.dashboard'));

        $film = Film::where('title', 'Film CRUD')->firstOrFail();
        $this->assertDatabaseHas('perans', [
            'film_id' => $film->id,
            'cast_id' => $cast->id,
            'actor' => 'Sari',
        ]);
        $this->get(route('movies.show', $film))->assertOk()->assertSee('Ayu Pratiwi');

        $this->put(route('admin.films.update', $film), [
            'title' => 'Film Diperbarui',
            'sinopsis' => 'Sinopsis baru.',
            'year' => now()->year,
            'poster' => 'posters/crud-baru.jpg',
            'genre_id' => $genre->id,
            'cast_ids' => [$cast->id],
            'actors' => ['Bima'],
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('films', ['id' => $film->id, 'title' => 'Film Diperbarui']);
        $this->assertDatabaseHas('perans', ['film_id' => $film->id, 'cast_id' => $cast->id, 'actor' => 'Bima']);

        $commenter = $this->createUser();
        Kritik::create([
            'comment' => 'Komentar terkait film.',
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => $commenter->id,
        ]);

        $this->from(route('admin.dashboard'))
            ->delete(route('admin.films.destroy', $film))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('status', 'Film berhasil dihapus.');

        $this->assertDatabaseMissing('films', ['id' => $film->id]);
        $this->assertDatabaseMissing('perans', ['film_id' => $film->id]);
        $this->assertDatabaseMissing('kritiks', ['film_id' => $film->id]);
    }

    public function test_dashboard_search_filters_films_and_pagination_preserves_search(): void
    {
        $this->actingAs($this->createAdmin());
        $target = $this->createFilm('Film Target 2024');
        $this->createFilm('Film Lain 2022', 2022);

        $this->get(route('admin.films.index', ['search' => 'Target']))
            ->assertOk()
            ->assertSee('Film Target 2024')
            ->assertDontSee('Film Lain 2022');

        $this->get(route('admin.films.index', ['search' => '2024']))
            ->assertOk()
            ->assertSee('Film Target 2024')
            ->assertDontSee('Film Lain 2022');

        $this->assertNotNull($target->id);
    }

    public function test_admin_film_form_validates_required_fields_and_cast_ids(): void
    {
        $this->actingAs($this->createAdmin());

        $this->from(route('admin.films.create'))
            ->post(route('admin.films.store'), [
                'title' => '',
                'year' => 'not-a-year',
                'genre_id' => 999,
                'cast_ids' => [999],
            ])
            ->assertRedirect(route('admin.films.create'))
            ->assertSessionHasErrors(['title', 'sinopsis', 'year', 'poster', 'genre_id', 'cast_ids.0']);

        $this->assertDatabaseCount('films', 0);
    }

    public function test_dashboard_paginates_film_list(): void
    {
        $this->actingAs($this->createAdmin());
        for ($index = 1; $index <= 21; $index++) {
            $this->createFilm(sprintf('Film %02d', $index));
        }

        $this->get(route('admin.films.index'))
            ->assertOk()
            ->assertSee('Film 01')
            ->assertDontSee('Film 21');

        $this->get(route('admin.films.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('Film 21');
    }

    public function test_dashboard_renders_live_summary_and_recent_activity(): void
    {
        $this->actingAs($this->createAdmin());
        $film = $this->createFilm('Film Ringkasan');
        $cast = Cast::create(['name' => 'Aktor Ringkasan', 'age' => 30, 'bio' => 'Bio']);
        $film->castMembers()->attach($cast->id, ['actor' => 'Tokoh']);
        $user = $this->createUser();
        Kritik::create([
            'comment' => 'Komentar ringkasan.',
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => $user->id,
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Ringkasan')
            ->assertSee('Total Film')
            ->assertSee('Film Ringkasan')
            ->assertSee('Komentar ringkasan.')
            ->assertSee(route('admin.casts.index'), false)
            ->assertSee(route('admin.comments.index'), false)
            ->assertSee(route('admin.users.index'), false);
    }

    public function test_admin_can_manage_casts_comments_and_user_roles(): void
    {
        $this->actingAs($this->createAdmin());
        $film = $this->createFilm();
        $user = $this->createUser();
        $cast = Cast::create(['name' => 'Aktor Tes', 'age' => 30, 'bio' => 'Bio awal']);
        $film->castMembers()->attach($cast->id, ['actor' => 'Tokoh']);
        $comment = Kritik::create([
            'comment' => 'Komentar untuk moderasi.',
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => $user->id,
        ]);

        $this->get(route('admin.casts.index'))->assertOk()->assertSee('Aktor Tes');
        $this->put(route('admin.casts.update', $cast), [
            'name' => 'Aktor Diperbarui',
            'age' => 31,
            'bio' => 'Bio baru',
        ])->assertRedirect(route('admin.casts.index'));
        $this->assertDatabaseHas('casts', ['id' => $cast->id, 'name' => 'Aktor Diperbarui']);

        $this->get(route('admin.comments.index'))->assertOk()->assertSee('Komentar untuk moderasi.');
        $this->delete(route('admin.comments.destroy', $comment))->assertRedirect(route('admin.comments.index'));
        $this->assertDatabaseMissing('kritiks', ['id' => $comment->id]);

        $this->get(route('admin.users.index'))->assertOk()->assertSee($user->email);
        $this->put(route('admin.users.role', $user), ['role' => 'admin'])
            ->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin',
            'role_id' => Role::where('name', 'admin')->value('id'),
        ]);
    }

    public function test_admin_cannot_demote_themselves_or_the_last_admin(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->put(route('admin.users.role', $admin), ['role' => 'user'])
            ->assertSessionHasErrors('role');

        $otherAdmin = User::create([
            'name' => 'Second Admin',
            'email' => 'second-admin@example.com',
            'password' => Hash::make('secure-pass-123'),
            'role' => 'admin',
            'role_id' => $admin->role_id,
            'profile_id' => $admin->profile_id,
        ]);
        User::whereKey($admin->id)->update(['role' => 'user']);
        $this->put(route('admin.users.role', $otherAdmin), ['role' => 'user'])
            ->assertSessionHasErrors('role');
    }

    public function test_admin_seeder_creates_one_repeatable_admin_account(): void
    {
        $seeder = new AdminSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('users', 1);
        $admin = User::where('email', env('ADMIN_EMAIL', 'admin@example.com'))->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check(env('ADMIN_PASSWORD', '12345678'), $admin->password));
    }

    private function createFilm(string $title = 'Film Contoh', int $year = 2024): Film
    {
        $genre = Genre::firstOrCreate(['name' => 'Drama']);

        return Film::create([
            'title' => $title,
            'sinopsis' => 'Sinopsis film.',
            'year' => $year,
            'poster' => 'posters/contoh.jpg',
            'genre_id' => $genre->id,
        ]);
    }

    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin']);
        $profile = Profile::create(['umur' => 0, 'bio' => '', 'alamat' => '']);

        return User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('secure-pass-123'),
            'role' => 'admin',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]);
    }

    private function createUser(): User
    {
        $role = Role::firstOrCreate(['name' => 'user']);
        $profile = Profile::create(['umur' => 0, 'bio' => '', 'alamat' => '']);

        return User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => Hash::make('secure-pass-123'),
            'role' => 'user',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]);
    }
}
