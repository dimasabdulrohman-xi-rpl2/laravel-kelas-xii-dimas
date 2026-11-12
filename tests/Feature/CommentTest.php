<?php

namespace Tests\Feature;

use App\Models\Film;
use App\Models\Genre;
use App\Models\Kritik;
use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_link_instead_of_comment_form(): void
    {
        $film = $this->createFilm();
        view()->share('genres', collect());

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Login untuk berkomentar')
            ->assertSee(route('login', ['film_id' => $film->id]), false)
            ->assertDontSee('name="comment"', false);
    }

    public function test_guest_cannot_store_a_comment_with_a_direct_request(): void
    {
        $film = $this->createFilm();

        $this->post(route('comments.store', $film), [
            'comment' => 'Komentar anonim',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('kritiks', 0);
    }

    public function test_login_from_a_film_comment_link_returns_to_that_film(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser();

        $this->get(route('login', ['film_id' => $film->id]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('url.intended', route('movies.show', $film));

        $this->post(route('login.authenticate'), [
            'email' => $user->email,
            'password' => 'secure-pass-123',
        ])->assertRedirect(route('movies.show', $film));
    }

    public function test_authenticated_user_comment_uses_session_user_id(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser();
        $otherUser = $this->createUser('other@example.com');

        $this->actingAs($user)
            ->post(route('comments.store', $film), [
                'comment' => 'Film yang bagus.',
                'user_id' => $otherUser->id,
                'film_id' => 999,
                'rating' => 5,
            ])
            ->assertRedirect(route('movies.show', $film).'#comments');

        $this->assertDatabaseHas('kritiks', [
            'comment' => 'Film yang bagus.',
            'film_id' => $film->id,
            'user_id' => $user->id,
            'rating' => 0,
        ]);
        $this->assertDatabaseMissing('kritiks', [
            'user_id' => $otherUser->id,
            'comment' => 'Film yang bagus.',
        ]);
    }

    public function test_comment_must_not_be_empty_and_cannot_exceed_500_characters(): void
    {
        $film = $this->createFilm();
        $this->actingAs($this->createUser());

        $this->from(route('movies.show', $film))
            ->post(route('comments.store', $film), ['comment' => ''])
            ->assertRedirect(route('movies.show', $film))
            ->assertSessionHasErrorsIn('comment', ['comment']);

        $this->from(route('movies.show', $film))
            ->post(route('comments.store', $film), ['comment' => str_repeat('a', 501)])
            ->assertRedirect(route('movies.show', $film))
            ->assertSessionHasErrorsIn('comment', ['comment']);

        $this->assertDatabaseCount('kritiks', 0);
    }

    public function test_comment_errors_and_user_names_are_shown_on_the_film_page(): void
    {
        $film = $this->createFilm();
        $user = $this->createUser();
        Kritik::create([
            'comment' => 'Komentar tersimpan.',
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => $user->id,
        ]);
        view()->share('genres', collect());

        $this->actingAs($user)
            ->from(route('movies.show', $film))
            ->post(route('comments.store', $film), ['comment' => ''])
            ->assertRedirect(route('movies.show', $film))
            ->assertSessionHasErrorsIn('comment', ['comment']);

        $this->get(route('movies.show', $film))
            ->assertOk()
            ->assertSee('Komentar tidak boleh kosong.')
            ->assertSee('Test User')
            ->assertSee('Komentar tersimpan.')
            ->assertDontSee("$('#myModal').modal('show');", false);
    }

    private function createFilm(): Film
    {
        $genre = Genre::create(['name' => 'Drama']);

        return Film::create([
            'title' => 'Film Komentar',
            'sinopsis' => 'Sinopsis film.',
            'year' => now()->year,
            'poster' => 'posters/komentar.jpg',
            'genre_id' => $genre->id,
        ]);
    }

    private function createUser(string $email = 'user@example.com'): User
    {
        $role = Role::firstOrCreate(['name' => 'user']);
        $profile = Profile::create(['umur' => 0, 'bio' => '', 'alamat' => '']);

        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => Hash::make('secure-pass-123'),
            'role' => 'user',
            'role_id' => $role->id,
            'profile_id' => $profile->id,
        ]);
    }
}
