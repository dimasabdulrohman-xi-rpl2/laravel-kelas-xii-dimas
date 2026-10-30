@extends('admin.layout')

@section('page-title', 'Cast & Aktor')
@section('page-description', 'Kelola data aktor yang dapat ditambahkan ke film.')

@section('admin-content')
@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif
<div class="admin-panel" style="margin-bottom: 22px;">
  <div class="admin-panel-heading"><span><i class="fa fa-plus"></i>Tambah Cast</span></div>
  <div class="admin-panel-body">
    <form action="{{ route('admin.casts.store') }}" method="post">
      @csrf
      <div class="row">
        <div class="col-sm-4 form-group"><label for="name">Nama</label><input class="form-control" id="name" name="name" value="{{ old('name') }}" maxlength="45" required></div>
        <div class="col-sm-2 form-group"><label for="age">Umur</label><input class="form-control" id="age" name="age" type="number" min="0" max="150" value="{{ old('age') }}" required></div>
        <div class="col-sm-6 form-group"><label for="bio">Bio</label><input class="form-control" id="bio" name="bio" value="{{ old('bio') }}" required></div>
      </div>
      <button class="btn btn-warning" type="submit"><i class="fa fa-plus"></i> Simpan Cast</button>
    </form>
  </div>
</div>
<div class="admin-panel">
  <div class="admin-panel-heading"><span><i class="fa fa-users"></i>Daftar Cast</span><span>{{ $casts->total() }} data</span></div>
  <div class="admin-panel-body">
    <form action="{{ route('admin.casts.index') }}" method="get" class="admin-search">
      <label class="sr-only" for="search">Cari cast</label>
      <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama cast">
      <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
      @if ($search !== '')<a class="btn btn-link" href="{{ route('admin.casts.index') }}">Reset</a>@endif
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Nama</th><th>Umur</th><th>Bio</th><th>Film</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($casts as $cast)
          <tr>
            <td>
              <form id="update-cast-{{ $cast->id }}" action="{{ route('admin.casts.update', $cast) }}" method="post">
                @csrf
                @method('PUT')
              </form>
              <label class="sr-only" for="cast-name-{{ $cast->id }}">Nama</label>
              <input class="form-control" id="cast-name-{{ $cast->id }}" name="name" form="update-cast-{{ $cast->id }}" value="{{ $cast->name }}" maxlength="45" required>
            </td>
            <td><label class="sr-only" for="cast-age-{{ $cast->id }}">Umur</label><input class="form-control" id="cast-age-{{ $cast->id }}" name="age" form="update-cast-{{ $cast->id }}" type="number" min="0" max="150" value="{{ $cast->age }}" required></td>
            <td><label class="sr-only" for="cast-bio-{{ $cast->id }}">Bio</label><input class="form-control" id="cast-bio-{{ $cast->id }}" name="bio" form="update-cast-{{ $cast->id }}" value="{{ $cast->bio }}" required></td>
            <td>{{ $cast->films_count }}</td>
            <td>
              <button class="btn btn-default btn-sm" type="submit" form="update-cast-{{ $cast->id }}">Simpan</button>
              <form id="delete-cast-{{ $cast->id }}" action="{{ route('admin.casts.destroy', $cast) }}" method="post" class="admin-inline-form" onsubmit="return confirm('Hapus cast ini? Cast akan dilepas dari semua film.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td class="admin-empty" colspan="5">Data cast belum tersedia.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="admin-panel-body">{{ $casts->links() }}</div>
</div>
@endsection
