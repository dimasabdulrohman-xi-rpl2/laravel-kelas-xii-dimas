@extends('admin.layout')

@section('page-title', 'Kelola Film')
@section('page-description', 'Cari, lihat, tambah, ubah, dan hapus data film.')

@section('admin-content')
<div class="admin-panel">
  <div class="admin-panel-heading">
    <span><i class="fa fa-film"></i>Daftar Film</span>
    <a class="btn btn-warning btn-sm" href="{{ route('admin.films.create') }}"><i class="fa fa-plus"></i> Tambah Film</a>
  </div>
  <div class="admin-panel-body">
    <form action="{{ route('admin.films.index') }}" method="get" class="admin-search">
      <label class="sr-only" for="search">Cari film atau tahun</label>
      <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari judul atau tahun">
      <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
      @if ($search !== '')
        <a class="btn btn-link" href="{{ route('admin.films.index') }}">Reset</a>
      @endif
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr><th>Poster</th><th>Judul</th><th>Tahun</th><th>Cast</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        @forelse ($films as $film)
          <tr>
            <td><img class="admin-film-poster" src="{{ asset($film->poster) }}" alt="Poster {{ $film->title }}"></td>
            <td><span class="admin-film-title">{{ $film->title }}</span></td>
            <td>{{ $film->year }}</td>
            <td>{{ $film->cast_members_count }}</td>
            <td>
              <a class="btn btn-default btn-sm" href="{{ route('admin.films.edit', $film) }}">Edit</a>
              <form action="{{ route('admin.films.destroy', $film) }}" method="post" class="admin-inline-form" onsubmit="return confirm('Yakin ingin menghapus film ini? Komentar dan data cast film juga akan dihapus.');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td class="admin-empty" colspan="5">Tidak ada film yang cocok dengan pencarian.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="admin-panel-body">{{ $films->links() }}</div>
</div>
@endsection
