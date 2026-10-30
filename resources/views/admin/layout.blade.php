<!DOCTYPE html>
<html lang="id">
@include('templates.component.head')
<style>
  :root {
    color-scheme: dark;
    --admin-bg: #0f172a;
    --admin-panel: #1e293b;
    --admin-panel-light: #263449;
    --admin-border: #334155;
    --admin-text: #f8fafc;
    --admin-muted: #94a3b8;
    --admin-orange: #f97316;
    --admin-blue: #3b82f6;
  }
  body.admin-body { min-width: 320px; margin: 0; background: var(--admin-bg); color: var(--admin-text); font-family: Arial, sans-serif; }
  .admin-shell { min-height: 100vh; }
  .admin-sidebar { position: fixed; inset: 0 auto 0 0; z-index: 20; display: flex; width: 276px; flex-direction: column; overflow-y: auto; background: var(--admin-panel); border-right: 1px solid var(--admin-border); }
  .admin-brand { display: flex; min-height: 88px; align-items: center; gap: 12px; padding: 18px 22px; border-bottom: 1px solid var(--admin-border); color: var(--admin-text); font-size: 24px; font-weight: 700; }
  .admin-brand .fa { color: var(--admin-orange); }
  .admin-brand span span { color: var(--admin-orange); }
  .admin-badge { margin-left: auto; padding: 4px 9px; border-radius: 20px; background: #422b24; color: #fb923c; font-size: 11px; }
  .admin-nav { padding: 24px 14px; }
  .admin-nav-label { margin: 12px 12px 8px; color: var(--admin-muted); font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
  .admin-nav-link { display: flex; align-items: center; gap: 13px; margin: 4px 0; padding: 13px 15px; border-radius: 9px; color: var(--admin-muted); font-size: 15px; font-weight: 600; text-decoration: none; }
  .admin-nav-link:hover, .admin-nav-link:focus { background: var(--admin-panel-light); color: var(--admin-text); text-decoration: none; }
  .admin-nav-link.active { background: linear-gradient(110deg, #f97316, #fb8500); box-shadow: 0 5px 16px #f9731638; color: white; }
  .admin-nav-link .fa { width: 18px; text-align: center; }
  .admin-sidebar-user { display: flex; align-items: center; gap: 12px; margin-top: auto; padding: 18px; border-top: 1px solid var(--admin-border); }
  .admin-avatar { display: grid; width: 44px; height: 44px; flex: 0 0 44px; place-items: center; border-radius: 50%; background: var(--admin-orange); color: white; font-size: 18px; font-weight: 700; }
  .admin-user-name { color: var(--admin-text); font-weight: 700; }
  .admin-user-role { color: #fb923c; font-size: 12px; }
  .admin-logout { margin-left: auto; border: 0; background: none; color: #f87171; }
  .admin-main { min-height: 100vh; margin-left: 276px; }
  .admin-topbar { position: sticky; top: 0; z-index: 10; display: flex; min-height: 88px; align-items: center; justify-content: space-between; gap: 20px; padding: 16px 32px; border-bottom: 1px solid var(--admin-border); background: #1e293bf2; }
  .admin-topbar h1 { margin: 0 0 4px; color: var(--admin-text); font-size: 23px; font-weight: 700; }
  .admin-topbar p { margin: 0; color: var(--admin-muted); }
  .admin-topbar-actions { display: flex; flex-wrap: wrap; gap: 10px; }
  .admin-content { padding: 28px 32px 48px; }
  .admin-content .latest-text { margin: 0 0 18px; color: var(--admin-text); }
  .admin-content .form-control { min-height: 40px; border-color: var(--admin-border); background: #111c30; color: var(--admin-text); }
  .admin-content .form-control::placeholder { color: var(--admin-muted); }
  .admin-content .table { margin-bottom: 0; color: var(--admin-text); }
  .admin-content .table > thead > tr > th { border-color: var(--admin-border); background: #172235; color: var(--admin-muted); }
  .admin-content .table > tbody > tr > td { vertical-align: middle; border-color: var(--admin-border); }
  .admin-content .table > tbody > tr:hover { background: #26344980; }
  .admin-panel { overflow: hidden; border: 1px solid var(--admin-border); border-radius: 12px; background: var(--admin-panel); }
  .admin-panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 20px; border-bottom: 1px solid var(--admin-border); font-size: 17px; font-weight: 700; }
  .admin-panel-heading .fa { margin-right: 8px; color: var(--admin-orange); }
  .admin-panel-body { padding: 18px 20px; }
  .admin-stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin-bottom: 26px; }
  .admin-stat { display: flex; min-height: 128px; align-items: center; gap: 17px; padding: 20px 22px; border: 1px solid var(--admin-border); border-radius: 13px; background: var(--admin-panel); }
  .admin-stat-icon { display: grid; width: 60px; height: 60px; flex: 0 0 60px; place-items: center; border-radius: 14px; background: #f9731624; color: var(--admin-orange); font-size: 24px; }
  .admin-stat-icon.blue { background: #3b82f624; color: var(--admin-blue); }
  .admin-stat-icon.green { background: #10b98124; color: #10b981; }
  .admin-stat-icon.purple { background: #a855f724; color: #c084fc; }
  .admin-stat-value { color: var(--admin-text); font-size: 32px; font-weight: 700; line-height: 1.1; }
  .admin-stat-label { margin-top: 5px; color: var(--admin-muted); }
  .admin-overview-grid { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(280px, .9fr); gap: 22px; }
  .admin-film-poster { width: 42px; height: 58px; border-radius: 5px; object-fit: cover; background: var(--admin-panel-light); }
  .admin-film-title { color: var(--admin-text); font-weight: 700; }
  .admin-film-synopsis { display: block; max-width: 250px; overflow: hidden; color: var(--admin-muted); text-overflow: ellipsis; white-space: nowrap; }
  .admin-pill { display: inline-block; padding: 5px 11px; border: 1px solid #f9731655; border-radius: 20px; background: #f9731617; color: #fb923c; }
  .admin-comment { padding: 14px 0; border-bottom: 1px solid var(--admin-border); }
  .admin-comment:last-child { padding-bottom: 0; border-bottom: 0; }
  .admin-comment-meta { display: flex; justify-content: space-between; gap: 8px; color: var(--admin-text); font-weight: 700; }
  .admin-comment-film { margin-top: 5px; color: var(--admin-muted); font-size: 13px; }
  .admin-comment-text { margin: 7px 0 0; color: #cbd5e1; font-style: italic; }
  .admin-search { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
  .admin-search .form-control { width: auto; min-width: 230px; }
  .admin-inline-form { display: inline; }
  .admin-empty { padding: 24px !important; color: var(--admin-muted); text-align: center; }
  .admin-content .pagination > li > a, .admin-content .pagination > li > span { border-color: var(--admin-border); background: var(--admin-panel); color: var(--admin-text); }
  .admin-content .pagination > .active > span { border-color: var(--admin-orange); background: var(--admin-orange); }
  @media (max-width: 1100px) {
    .admin-sidebar { width: 235px; }
    .admin-main { margin-left: 235px; }
    .admin-stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .admin-overview-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 700px) {
    .admin-sidebar { position: relative; width: 100%; max-height: none; }
    .admin-brand { min-height: 66px; }
    .admin-nav { display: flex; flex-wrap: wrap; gap: 4px; padding: 10px; }
    .admin-nav-label { display: none; }
    .admin-nav-link { margin: 0; padding: 10px; }
    .admin-sidebar-user { display: none; }
    .admin-main { margin-left: 0; }
    .admin-topbar { position: static; align-items: flex-start; flex-direction: column; padding: 16px; }
    .admin-content { padding: 18px 14px 32px; }
    .admin-stat-grid { grid-template-columns: 1fr; gap: 10px; }
    .admin-stat { min-height: 100px; }
    .admin-content .table { min-width: 620px; }
    .admin-search .form-control { width: 100%; }
  }
</style>
<body class="admin-body">
  <div class="admin-shell">
    <aside class="admin-sidebar">
      <a class="admin-brand" href="{{ route('admin.dashboard') }}">
        <i class="fa fa-film" aria-hidden="true"></i>
        <span>One<span>Movies</span></span>
        <span class="admin-badge">ADMIN</span>
      </a>
      <nav class="admin-nav" aria-label="Navigasi admin">
        <div class="admin-nav-label">Menu utama</div>
        <a class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fa fa-tachometer"></i>Dashboard</a>
        <a class="admin-nav-link {{ request()->routeIs('admin.films.*') ? 'active' : '' }}" href="{{ route('admin.films.index') }}"><i class="fa fa-film"></i>Kelola Film</a>
        <a class="admin-nav-link {{ request()->routeIs('admin.casts.*') ? 'active' : '' }}" href="{{ route('admin.casts.index') }}"><i class="fa fa-users"></i>Cast &amp; Aktor</a>
        <div class="admin-nav-label">Interaksi &amp; user</div>
        <a class="admin-nav-link {{ request()->routeIs('admin.comments.*') ? 'active' : '' }}" href="{{ route('admin.comments.index') }}"><i class="fa fa-comments"></i>Komentar</a>
        <a class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fa fa-user"></i>Kelola User</a>
        <div class="admin-nav-label">Aksi cepat</div>
        <a class="admin-nav-link" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fa fa-external-link"></i>Lihat Website</a>
      </nav>
      <div class="admin-sidebar-user">
        <div class="admin-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
        <div>
          <div class="admin-user-name">{{ Auth::user()->name }}</div>
          <div class="admin-user-role"><i class="fa fa-shield"></i> Administrator</div>
        </div>
        <form action="{{ route('logout') }}" method="post">
          @csrf
          <button class="admin-logout" type="submit" title="Logout" aria-label="Logout"><i class="fa fa-sign-out"></i></button>
        </form>
      </div>
    </aside>
    <div class="admin-main">
      <header class="admin-topbar">
        <div>
          <h1>@yield('page-title', 'Dashboard Ringkasan')</h1>
          <p>@yield('page-description', 'Pantau statistik film, cast, komentar, dan aktivitas terkini.')</p>
        </div>
        <div class="admin-topbar-actions">
          <a class="btn btn-warning" href="{{ route('admin.films.create') }}"><i class="fa fa-plus"></i> Tambah Film</a>
          <a class="btn btn-default" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="fa fa-globe"></i> Buka Website</a>
        </div>
      </header>
      <main class="admin-content">
        @if (session('status'))
          <div class="alert alert-success" role="alert">{{ session('status') }}</div>
        @endif
        @yield('admin-content')
      </main>
    </div>
  </div>
  <script src="{{ asset('template/js/bootstrap.min.js') }}"></script>
  @stack('script')
</body>
</html>
