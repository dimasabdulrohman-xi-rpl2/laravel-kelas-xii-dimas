<?php

namespace App\Http\Controllers;

use App\Models\Cast;
use App\Models\Film;
use App\Models\Genre;
use App\Models\Kritik;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'filmCount' => Film::count(),
            'castCount' => Cast::count(),
            'genreCount' => Genre::count(),
            'commentCount' => Kritik::count(),
            'userCount' => User::count(),
            'recentFilms' => Film::query()
                ->with('genre:id,name')
                ->latest()
                ->limit(5)
                ->get(),
            'recentComments' => Kritik::query()
                ->with(['user:id,name', 'film:id,title'])
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function casts(): View
    {
        $validated = request()->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');

        $casts = Cast::query()
            ->withCount('films')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.casts.index', compact('casts', 'search'));
    }

    public function storeCast(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:45'],
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'bio' => ['required', 'string', 'max:5000'],
        ]);

        Cast::create($validated);

        return redirect()->route('admin.casts.index')->with('status', 'Data cast berhasil ditambahkan.');
    }

    public function updateCast(Request $request, Cast $cast): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:45'],
            'age' => ['required', 'integer', 'min:0', 'max:150'],
            'bio' => ['required', 'string', 'max:5000'],
        ]);

        $cast->update($validated);

        return redirect()->route('admin.casts.index')->with('status', 'Data cast berhasil diperbarui.');
    }

    public function destroyCast(Cast $cast): RedirectResponse
    {
        DB::transaction(function () use ($cast): void {
            $cast->films()->detach();
            $cast->delete();
        });

        return redirect()->route('admin.casts.index')->with('status', 'Data cast berhasil dihapus.');
    }

    public function comments(): View
    {
        $validated = request()->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);
        $search = trim($validated['search'] ?? '');

        $comments = Kritik::query()
            ->with(['user:id,name', 'film:id,title'])
            ->when($search !== '', fn ($query) => $query->where('comment', 'like', '%'.$search.'%')
                ->orWhereHas('user', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orWhereHas('film', fn ($query) => $query->where('title', 'like', '%'.$search.'%')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'search'));
    }

    public function destroyComment(Kritik $kritik): RedirectResponse
    {
        $kritik->delete();

        return redirect()->route('admin.comments.index')->with('status', 'Komentar berhasil dihapus.');
    }

    public function users(): View
    {
        $validated = request()->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'role' => ['nullable', 'in:admin,user'],
        ]);
        $search = trim($validated['search'] ?? '');
        $role = $validated['role'] ?? '';

        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->when($role !== '', fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function updateUserRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        if ($user->is(auth()->user()) && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'Anda tidak dapat mengubah role akun admin yang sedang digunakan.',
            ]);
        }

        if ($user->role === 'admin' && $validated['role'] === 'user') {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->withErrors([
                    'role' => 'Minimal harus ada satu akun admin.',
                ]);
            }
        }

        $user->update([
            'role' => $validated['role'],
            'role_id' => Role::firstOrCreate(['name' => $validated['role']])->id,
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Role pengguna berhasil diperbarui.');
    }
}
