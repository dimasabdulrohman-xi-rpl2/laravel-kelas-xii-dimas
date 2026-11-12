@extends('admin.layout')

@section('page-title', 'Komentar')
@section('page-description', 'Lihat dan moderasi ulasan dari pengguna.')

@section('admin-content')
<div class="admin-panel">
  <div class="admin-panel-heading">
    <span><i class="fa fa-comments" style="color: #c084fc;"></i>Semua Komentar</span>
    <span>{{ $comments->total() }} komentar</span>
  </div>
  <div class="admin-panel-body">
    <form action="{{ route('admin.comments.index') }}" method="get" class="admin-search">
      <label class="sr-only" for="search">Cari komentar</label>
      <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari isi, user, atau judul film">
      <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
      @if ($search !== '')<a class="btn btn-link" href="{{ route('admin.comments.index') }}">Reset</a>@endif
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Pengguna</th><th>Film</th><th>Komentar</th><th>Tanggal</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($comments as $comment)
          <tr>
            <td>{{ $comment->user?->name ?? 'Pengguna dihapus' }}</td>
            <td>
              @if ($comment->film)
                <a href="{{ route('movies.show', $comment->film) }}" target="_blank" rel="noopener">{{ $comment->film->title }}</a>
              @else
                Film dihapus
              @endif
            </td>
            <td>{{ $comment->comment }}</td>
            <td>{{ $comment->created_at?->format('d/m/Y H:i') }}</td>
            <td>
              <form action="{{ route('admin.comments.destroy', $comment) }}" method="post" onsubmit="return confirm('Yakin ingin menghapus komentar ini?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td class="admin-empty" colspan="5">Belum ada komentar yang cocok.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="admin-panel-body">{{ $comments->links() }}</div>
</div>
@endsection
