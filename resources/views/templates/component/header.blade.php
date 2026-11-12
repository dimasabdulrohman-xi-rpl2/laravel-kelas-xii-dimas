<div class="header">
  <div class="container">
    <div class="w3layouts_logo">
      <a href="{{ route('home') }}"><h1>One<span>Movies</span></h1></a>
    </div>
    <div class="w3_search">
      <form action="#" method="post">
        <input type="text" name="Search" placeholder="Search" required="">
        <input type="submit" value="Go">
      </form>
    </div>
    <div class="w3l_sign_in_register">
      <ul>
        @auth
          <li>{{ Auth::user()->name }}</li>
          @if (Auth::user()->role === 'admin')
            <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
          @endif
          <li>
            <form action="{{ route('logout') }}" method="post">
              @csrf
              <button type="submit" class="btn btn-link">Logout</button>
            </form>
          </li>
        @else
          <li><a href="#" data-toggle="modal" data-target="#myModal">Login / Register</a></li>
        @endauth
      </ul>
    </div>
    <div class="clearfix"> </div>
    @if (session('status'))
      <div class="alert alert-success" role="alert">{{ session('status') }}</div>
    @endif
  </div>
</div>