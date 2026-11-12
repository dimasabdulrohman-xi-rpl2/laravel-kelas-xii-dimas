@extends('admin.layout')

@section('page-title', 'Dashboard Ringkasan')
@section('page-description', 'Pantau statistik film, cast, komentar, dan aktivitas terkini.')

@section('admin-content')
<section class="admin-stat-grid" aria-label="Statistik ringkasan">
  <div class="admin-stat">
    <div class="admin-stat-icon"><i class="fa fa-film"></i></div>
    <div><div class="admin-stat-value">{{ number_format($filmCount) }}</div><div class="admin-stat-label">Total Film</div></div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-icon blue"><i class="fa fa-users"></i></div>
    <div><div class="admin-stat-value">{{ number_format($castCount) }}</div><div class="admin-stat-label">Total Cast / Aktor</div></div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-icon green"><i class="fa fa-tags"></i></div>
    <div><div class="admin-stat-value">{{ number_format($genreCount) }}</div><div class="admin-stat-label">Total Genre</div></div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-icon purple"><i class="fa fa-comments"></i></div>
    <div><div class="admin-stat-value">{{ number_format($commentCount) }}</div><div class="admin-stat-label">Total Ulasan / Komentar</div></div>
  </div>
  <div class="admin-stat">
    <div class="admin-stat-icon blue"><i class="fa fa-user"></i></div>
    <div><div class="admin-stat-value">{{ number_format($userCount) }}</div><div class="admin-stat-label">Pengguna Terdaftar</div></div>
  </div>
</section>

<section class="admin-overview-grid">
  <div class="admin-panel">
    <div class="admin-panel-heading">
      <span><i class="fa fa-film"></i>Film Terbaru</span>
      <a class="btn btn-default btn-sm" href="{{ route('admin.films.index') }}">Lihat Semua</a>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr><th>Poster</th><th>Judul</th><th>Genre</th><th>Tahun</th><th>Aksi</th></tr>
        </thead>
        <tbody>
          @forelse ($recentFilms as $film)
            <tr>
              <td><img class="admin-film-poster" src="{{ asset($film->poster) }}" alt="Poster {{ $film->title }}"></td>
              <td>
                <span class="admin-film-title">{{ $film->title }}</span>
                <span class="admin-film-synopsis">{{ $film->sinopsis }}</span>
              </td>
              <td><span class="admin-pill">{{ $film->genre?->name ?? 'Tanpa genre' }}</span></td>
              <td>{{ $film->year }}</td>
              <td>
                <a class="btn btn-default btn-sm" href="{{ route('admin.films.edit', $film) }}" aria-label="Edit {{ $film->title }}"><i class="fa fa-pencil"></i></a>
                <a class="btn btn-default btn-sm" href="{{ route('movies.show', $film) }}" target="_blank" rel="noopener" aria-label="Lihat {{ $film->title }}"><i class="fa fa-eye"></i></a>
              </td>
            </tr>
          @empty
            <tr><td class="admin-empty" colspan="5">Belum ada data film.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="admin-panel">
    <div class="admin-panel-heading">
      <span><i class="fa fa-comments" style="color: #c084fc;"></i>Komentar Terbaru</span>
      <a class="btn btn-default btn-sm" href="{{ route('admin.comments.index') }}">Semua</a>
    </div>
    <div class="admin-panel-body">
      @forelse ($recentComments as $comment)
        <article class="admin-comment">
          <div class="admin-comment-meta">
            <span>{{ $comment->user?->name ?? 'Pengguna' }}</span>
            @if ($comment->rating > 0)
              <span class="admin-pill"><i class="fa fa-star"></i> {{ $comment->rating }}</span>
            @endif
          </div>
          <div class="admin-comment-film">pada film: <a href="{{ route('movies.show', $comment->film_id) }}">{{ $comment->film?->title ?? 'Film dihapus' }}</a></div>
          <p class="admin-comment-text">"{{ \Illuminate\Support\Str::limit($comment->comment, 150) }}"</p>
        </article>
      @empty
        <p class="admin-empty">Belum ada komentar.</p>
      @endforelse
    </div>
  </div>
</section>
@endsection
