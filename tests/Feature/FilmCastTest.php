<?php

namespace Tests\Feature;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PeranSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class FilmCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_movie_detail_displays_cast_names_and_characters(): void
    {
        $film = $this->createFilm();
        $cast = Cast::create(['name' => 'Ayu Pratiwi', 'age' => 28, 'bio' => 'Aktor']);
        $film->castMembers()->attach($cast->id, ['actor' => 'Sari']);
        view()->share('genres', collect());

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Ayu Pratiwi')
            ->assertSee('Sari')
            ->assertViewHas('film', fn (Film $displayedFilm): bool => $displayedFilm->relationLoaded('castMembers'));
    }

    public function test_movie_detail_shows_message_when_cast_is_empty(): void
    {
        $film = $this->createFilm();
        view()->share('genres', collect());

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Data cast belum tersedia.');
    }

    public function test_admin_can_add_and_remove_film_cast_and_characters(): void
    {
        $this->actingAs($this->createAdmin());
        $genre = Genre::create(['name' => 'Drama']);
        $firstCast = Cast::create(['name' => 'Ayu Pratiwi', 'age' => 28, 'bio' => 'Aktor']);
        $secondCast = Cast::create(['name' => 'Budi Santoso', 'age' => 34, 'bio' => 'Aktor']);
        view()->share('genres', collect());

        $this->get(route('admin.films.create'))
            ->assertOk()
            ->assertSee('Ayu Pratiwi');

        $this->post(route('admin.films.store'), [
            'title' => 'Film Contoh',
            'sinopsis' => 'Sinopsis film.',
            'year' => now()->year,
            'poster' => 'posters/contoh.jpg',
            'genre_id' => $genre->id,
            'cast_ids' => [$firstCast->id],
            'actors' => ['Sari'],
        ])->assertRedirect(route('admin.dashboard'));

        $film = Film::where('title', 'Film Contoh')->firstOrFail();
        $this->assertDatabaseHas('perans', [
            'film_id' => $film->id,
            'cast_id' => $firstCast->id,
            'actor' => 'Sari',
        ]);

        $this->get(route('admin.films.edit', $film))
            ->assertOk()
            ->assertSee('Ayu Pratiwi')
            ->assertSee('Sari');

        $this->put(route('admin.films.update', $film), [
            'title' => 'Film Contoh',
            'sinopsis' => 'Sinopsis diperbarui.',
            'year' => now()->year,
            'poster' => 'posters/contoh.jpg',
            'genre_id' => $genre->id,
            'cast_ids' => ['', $secondCast->id],
            'actors' => ['', 'Bima'],
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseMissing('perans', [
            'film_id' => $film->id,
            'cast_id' => $firstCast->id,
        ]);
        $this->assertDatabaseHas('perans', [
            'film_id' => $film->id,
            'cast_id' => $secondCast->id,
            'actor' => 'Bima',
        ]);
    }

    public function test_non_admin_cannot_manage_film_cast(): void
    {
        $role = Role::create(['name' => 'user']);
        $profile = Profile::create(['umur' => 0, 'bio' => '', 'alamat' => '']);
        $this->actingAs(User::create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]));

        $this->get(route('admin.films.create'))->assertForbidden();
    }

    public function test_peran_seeder_creates_dummy_film_cast_relations(): void
    {
        $film = $this->createFilm();
        $cast = Cast::create(['name' => 'Ayu Pratiwi', 'age' => 28, 'bio' => 'Aktor']);

        (new PeranSeeder)->run();

        $this->assertDatabaseHas('perans', [
            'film_id' => $film->id,
            'cast_id' => $cast->id,
        ]);
    }

    private function createFilm(): Film
    {
        $genre = Genre::create(['name' => 'Drama']);

        return Film::create([
            'title' => 'Film Contoh',
            'sinopsis' => 'Sinopsis film.',
            'year' => now()->year,
            'poster' => 'posters/contoh.jpg',
            'genre_id' => $genre->id,
        ]);
    }

    private function createAdmin(): User
    {
        $role = Role::create(['name' => 'admin']);
        $profile = Profile::create(['umur' => 0, 'bio' => '', 'alamat' => '']);

        return User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]);
    }
}
