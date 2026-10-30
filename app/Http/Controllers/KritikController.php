<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKritikRequest;
use App\Http\Requests\UpdateKritikRequest;
use App\Models\Film;
use App\Models\Kritik;
use Illuminate\Http\RedirectResponse;

class KritikController extends Controller
{
    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(StoreKritikRequest $request, Film $film): RedirectResponse
    {
        Kritik::create([
            'comment' => $request->validated('comment'),
            'rating' => 0,
            'film_id' => $film->id,
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('movies.show', $film)
            ->withFragment('comments')
            ->with('status', 'Komentar berhasil dikirim.');
    }

    public function show(Kritik $kritik)
    {
        //
    }

    public function edit(Kritik $kritik)
    {
        //
    }

    public function update(UpdateKritikRequest $request, Kritik $kritik)
    {
        //
    }

    public function destroy(Kritik $kritik)
    {
        //
    }
}
