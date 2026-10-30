@extends('admin.layout')

@section('page-title', 'Kelola User')
@section('page-description', 'Lihat pengguna terdaftar dan atur role akses mereka.')

@section('admin-content')
@if ($errors->any())
  <div class="alert alert-danger" role="alert">
    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
  </div>
@endif
<div class="admin-panel">
  <div class="admin-panel-heading"><span><i class="fa fa-users"></i>Pengguna Terdaftar</span><span>{{ $users->total() }} user</span></div>
  <div class="admin-panel-body">
    <form action="{{ route('admin.users.index') }}" method="get" class="admin-search">
      <label class="sr-only" for="search">Cari pengguna</label>
      <input class="form-control" id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama atau email">
      <label class="sr-only" for="role">Filter role</label>
      <select class="form-control" id="role" name="role">
        <option value="">Semua role</option>
        <option value="admin" @selected($role === 'admin')>Admin</option>
        <option value="user" @selected($role === 'user')>User</option>
      </select>
      <button class="btn btn-default" type="submit"><i class="fa fa-search"></i> Cari</button>
      @if ($search !== '' || $role !== '')<a class="btn btn-link" href="{{ route('admin.users.index') }}">Reset</a>@endif
    </form>
  </div>
  <div class="table-responsive">
    <table class="table">
      <thead><tr><th>Nama</th><th>Email</th><th>Terdaftar</th><th>Role</th><th>Aksi</th></tr></thead>
      <tbody>
        @forelse ($users as $user)
          <tr>
            <td>{{ $user->name }} @if ($user->is(auth()->user()))<span class="admin-pill">Anda</span>@endif</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->created_at?->format('d/m/Y') }}</td>
            <td><span class="admin-pill">{{ ucfirst($user->role) }}</span></td>
            <td>
              @if (! $user->is(auth()->user()))
                <form action="{{ route('admin.users.role', $user) }}" method="post" class="admin-search" style="margin: 0;">
                  @csrf
                  @method('PUT')
                  <select class="form-control" name="role" aria-label="Role {{ $user->name }}">
                    <option value="user" @selected($user->role === 'user')>User</option>
                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                  </select>
                  <button class="btn btn-default btn-sm" type="submit">Simpan</button>
                </form>
              @else
                <span class="text-muted">Akun aktif</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td class="admin-empty" colspan="5">Tidak ada pengguna yang cocok.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="admin-panel-body">{{ $users->links() }}</div>
</div>
@endsection
