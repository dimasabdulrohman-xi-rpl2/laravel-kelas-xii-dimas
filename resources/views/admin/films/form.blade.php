@extends('admin.layout')

@section('admin-content')
    <h2 class="latest-text">{{ $film ? 'Edit film' : 'Tambah film' }}</h2>
    <p><a href="{{ route('admin.dashboard') }}">Kembali ke dashboard</a></p>

    @if ($errors->any())
      <div class="alert alert-danger" role="alert">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @php
      $defaultCastIds = array_column($selectedCasts, 'cast_id');
      $defaultActors = array_column($selectedCasts, 'actor');
      $castIds = old('cast_ids', $defaultCastIds);
      $actors = old('actors', $defaultActors);
      $castIds = is_array($castIds)
        ? array_map(fn ($value) => is_scalar($value) ? (string) $value : '', $castIds)
        : [];
      $actors = is_array($actors)
        ? array_map(fn ($value) => is_scalar($value) ? (string) $value : '', $actors)
        : [];
      $rowCount = max(count($castIds), 1);
    @endphp

    <form action="{{ $film ? route('admin.films.update', $film) : route('admin.films.store') }}" method="post">
      @csrf
      @if ($film)
        @method('PUT')
      @endif

      <div class="form-group">
        <label for="title">Judul film</label>
        <input class="form-control" id="title" name="title" value="{{ old('title', $film?->title) }}" required>
      </div>
      <div class="form-group">
        <label for="sinopsis">Sinopsis</label>
        <textarea class="form-control" id="sinopsis" name="sinopsis" rows="4" required>{{ old('sinopsis', $film?->sinopsis) }}</textarea>
      </div>
      <div class="form-group">
        <label for="year">Tahun</label>
        <input class="form-control" id="year" name="year" type="number" min="1888" max="{{ now()->year + 10 }}" value="{{ old('year', $film?->year) }}" required>
      </div>
      <div class="form-group">
        <label for="poster">Path poster</label>
        <input class="form-control" id="poster" name="poster" value="{{ old('poster', $film?->poster) }}" required>
      </div>
      <div class="form-group">
        <label for="genre_id">Genre</label>
        <select class="form-control" id="genre_id" name="genre_id" required>
          <option value="">Pilih genre</option>
          @foreach ($genres as $genre)
            <option value="{{ $genre->id }}" @selected((string) old('genre_id', $film?->genre_id) === (string) $genre->id)>{{ $genre->name }}</option>
          @endforeach
        </select>
      </div>

      <h3>Cast dan peran</h3>
      <div id="cast-rows">
        @for ($index = 0; $index < $rowCount; $index++)
          <div class="row cast-row" style="margin-bottom: 10px;">
            <div class="col-sm-6">
              <label>Nama cast</label>
              <select class="form-control" name="cast_ids[]">
                <option value="">Pilih cast</option>
                @foreach ($casts as $cast)
                  <option value="{{ $cast->id }}" @selected((string) ($castIds[$index] ?? '') === (string) $cast->id)>{{ $cast->name }}</option>
                @endforeach
              </select>
            </div>
            <div class="col-sm-5">
              <label>Peran/karakter</label>
              <input class="form-control" name="actors[]" value="{{ $actors[$index] ?? '' }}" placeholder="Contoh: tokoh utama">
            </div>
            <div class="col-sm-1">
              <label>&nbsp;</label>
              <button class="btn btn-danger remove-cast" type="button" aria-label="Hapus cast">Hapus</button>
            </div>
          </div>
        @endfor
      </div>
      <p><button class="btn btn-default" id="add-cast" type="button">Tambah cast</button></p>
      <button class="btn btn-primary" type="submit">Simpan film</button>
    </form>

    <template id="cast-row-template">
      <div class="row cast-row" style="margin-bottom: 10px;">
        <div class="col-sm-6">
          <label>Nama cast</label>
          <select class="form-control" name="cast_ids[]">
            <option value="">Pilih cast</option>
            @foreach ($casts as $cast)
              <option value="{{ $cast->id }}">{{ $cast->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-sm-5">
          <label>Peran/karakter</label>
          <input class="form-control" name="actors[]" placeholder="Contoh: tokoh utama">
        </div>
        <div class="col-sm-1">
          <label>&nbsp;</label>
          <button class="btn btn-danger remove-cast" type="button" aria-label="Hapus cast">Hapus</button>
        </div>
      </div>
    </template>
@endsection

@push('script')
  document.getElementById('add-cast').addEventListener('click', function () {
    document.getElementById('cast-rows').appendChild(
      document.getElementById('cast-row-template').content.cloneNode(true)
    );
  });
  document.getElementById('cast-rows').addEventListener('click', function (event) {
    if (event.target.classList.contains('remove-cast')) {
      const rows = document.querySelectorAll('#cast-rows .cast-row');
      if (rows.length === 1) {
        rows[0].querySelector('select').value = '';
        rows[0].querySelector('input').value = '';
      } else {
        event.target.closest('.cast-row').remove();
      }
    }
  });
@endpush
