<?php

namespace App\Http\Controllers;

use App\Models\{
    Cast,
    Film,
    Genre,
    Kritik,
};
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FilmController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function movies()
    {
        //
        $genreFilm = null;
        $films = Film::select('id','title', 'poster','year')
                ->orderByDesc('year')
                ->OrderBy('created_at', 'asc')
                ->paginate(18);
        return view('components/movies', compact('films', 'genreFilm'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->ensureAdmin();

        return view('admin.films.form', [
            'film' => null,
            'genres' => Genre::orderBy('name')->get(),
            'casts' => Cast::orderBy('name')->get(),
            'selectedCasts' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureAdmin();
        $validated = $this->validateFilm($request);

        $film = DB::transaction(function () use ($validated): Film {
            $film = Film::create($validated['film']);
            $film->castMembers()->sync($validated['casts']);

            return $film;
        });

        return redirect()->route('admin.dashboard')->with('status', 'Film berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Film $film)
    {
        //
        $film->load('castMembers:id,name');
        $filmByGenre    = Film::select('id','title', 'poster','year', 'sinopsis')
                        ->where('genre_id', '=', $film->genre_id)
                        ->OrderBy('created_at', 'asc')
                        ->limit(7)
                        ->get();
        $filmByRelease  = Film::select('id','title', 'poster','year')
                        ->where('year', '=', Carbon::now()->format('Y'))
                        ->OrderBy('created_at', 'asc')
                        ->limit(9)
                        ->get();
        $comments = Kritik::query()
                        ->with('user:id,name')
                        ->select('id', 'comment', 'user_id')
                        ->where('film_id', $film->id)
                        ->orderBy('created_at', 'asc')
                        ->limit(10)
                        ->get();
        return view('components.movie-show', compact('film','filmByGenre','filmByRelease','comments'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Film $film)
    {
        $this->ensureAdmin();

        return view('admin.films.form', [
            'film' => $film->load('castMembers:id,name'),
            'genres' => Genre::orderBy('name')->get(),
            'casts' => Cast::orderBy('name')->get(),
            'selectedCasts' => $film->castMembers->map(fn (Cast $cast): array => [
                'cast_id' => $cast->id,
                'actor' => $cast->pivot->actor,
            ])->all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Film $film): RedirectResponse
    {
        $this->ensureAdmin();
        $validated = $this->validateFilm($request);

        DB::transaction(function () use ($film, $validated): void {
            $film->update($validated['film']);
            $film->castMembers()->sync($validated['casts']);
        });

        return redirect()->route('admin.dashboard')->with('status', 'Film berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Film $film): RedirectResponse
    {
        $this->ensureAdmin();

        DB::transaction(function () use ($film): void {
            $film->castMembers()->detach();
            Kritik::where('film_id', $film->id)->delete();
            $film->delete();
        });

        return redirect()->route('admin.dashboard')->with('status', 'Film berhasil dihapus.');
    }

    public function movieHome()
    {
        $films = Film::select('id','title', 'poster','year')
                ->OrderBy('created_at', 'asc')
                ->limit(9)->get();
        $filmFutured = Film::select('id','title', 'poster','year')
                        ->orderByDesc('year')
                        ->OrderBy('created_at', 'asc')
                        ->limit(6)->get();
        $filmRecentAdded = Film::select('id','title', 'poster','year')
                            ->orderByDesc('created_at', 'asc')
                            ->limit(6)->get();
        $filmSlidey = Film::select('title', 'poster','sinopsis')
                        ->orderByDesc('year')
                        ->OrderBy('created_at', 'asc')
                        ->limit(6)->get();
        return view('components/home', compact('filmSlidey','films','filmFutured','filmRecentAdded'));
    }

    public function moviesByGenre($genre)
    {
        //
        $genreFilm = Genre::where('id', '=', $genre)->first();
        $films = Film::select('id','title', 'poster','year')
                ->where('genre_id', '=', $genre)
                ->orderByDesc('year')
                ->OrderBy('created_at', 'asc')
                ->paginate(18);
        return view('components/movies', compact('films','genreFilm'));
    }

    public function adminDashboard(): View
    {
        $this->ensureAdmin();

        $validated = request()->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $search = trim($validated['search'] ?? '');
        $films = Film::query()
            ->withCount('castMembers')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%');
                if (ctype_digit($search)) {
                    $query->orWhere('year', (int) $search);
                }
            }))
            ->orderBy('title')
            ->paginate(20)
            ->withQueryString();

        return view('admin.films.index', compact('films', 'search'));
    }

    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()?->role === 'admin', 403);
    }

    /**
     * @return array{film: array<string, mixed>, casts: array<int, array{actor: string}>}
     */
    private function validateFilm(Request $request): array
    {
        $castIds = $request->input('cast_ids', []);
        $request->merge([
            'cast_ids' => array_filter(
                is_array($castIds) ? $castIds : [],
                fn ($castId): bool => $castId !== null && $castId !== ''
            ),
        ]);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'sinopsis' => ['required', 'string'],
            'year' => ['required', 'integer', 'min:1888', 'max:'.(now()->year + 10)],
            'poster' => ['required', 'string', 'max:2048'],
            'genre_id' => ['required', 'integer', 'exists:genres,id'],
            'cast_ids' => ['nullable', 'array'],
            'cast_ids.*' => ['integer', 'distinct', 'exists:casts,id'],
            'actors' => ['nullable', 'array'],
            'actors.*' => ['nullable', 'string', 'max:255'],
        ], [
            'cast_ids.*.distinct' => 'Cast yang sama tidak boleh dipilih lebih dari sekali.',
        ]);

        $characters = $request->input('actors', []);

        return [
            'film' => [
                'title' => $validated['title'],
                'sinopsis' => $validated['sinopsis'],
                'year' => $validated['year'],
                'poster' => $validated['poster'],
                'genre_id' => $validated['genre_id'],
            ],
            'casts' => collect($validated['cast_ids'] ?? [])
                ->mapWithKeys(fn (int $castId, int|string $index): array => [
                    $castId => ['actor' => trim((string) ($characters[$index] ?? ''))],
                ])
                ->all(),
        ];
    }
}
