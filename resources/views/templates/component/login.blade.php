<div class="modal video-modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModal">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        Sign In & Sign Up
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>						
      </div>
      <section>
        <div class="modal-body">
          @if ($errors->getBag('default')->any())
            <div class="alert alert-danger" role="alert">
              <ul class="mb-0">
                @foreach ($errors->getBag('default')->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif
          <div class="w3_login_module">
            <div class="module form-module">
              <div class="toggle"><i class="fa fa-times fa-pencil"></i>
              <div class="tooltip">Click Me</div>
              </div>
              <div class="form" id="login-form">
              <h3>Login to your account</h3>
              <form action="{{ route('login.authenticate') }}" method="post">
                @csrf
                <input type="hidden" name="auth_form" value="login">
                <input type="email" name="email" placeholder="Email Address" value="{{ old('email') }}" autocomplete="email" required>
                <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                <label><input type="checkbox" name="remember" value="1"> Remember me</label>
                <input type="submit" value="Login">
              </form>
              </div>
              <div class="form" id="register-form">
              <h3>Create an account</h3>
              <form action="{{ route('register.store') }}" method="post">
                @csrf
                <input type="hidden" name="auth_form" value="register">
                <input type="text" name="name" placeholder="Name / Username" value="{{ old('name') }}" autocomplete="name" required>
                <input type="email" name="email" placeholder="Email Address" value="{{ old('auth_form') === 'register' ? old('email') : '' }}" autocomplete="email" required>
                <input type="password" name="password" placeholder="Password (at least 8 characters)" autocomplete="new-password" required>
                <input type="password" name="password_confirmation" placeholder="Confirm Password" autocomplete="new-password" required>
                <input type="submit" value="Register">
              </form>
              </div>
              <div class="cta"><a href="#">Forgot your password?</a></div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>
<script>
  $('.toggle').click(function(){
    // Switches the Icon
    $(this).children('i').toggleClass('fa-pencil');
    // Switches the forms  
    $('.form').animate({
    height: "toggle",
    'padding-top': 'toggle',
    'padding-bottom': 'toggle',
    opacity: "toggle"
    }, "slow");
  });
</script>
@push('script')
  @if ($errors->getBag('default')->any() || session('auth_modal'))
    @if (old('auth_form') === 'register' || session('auth_modal') === 'register')
      $('.toggle').trigger('click');
    @endif
    $('#myModal').modal('show');
  @endif
@endpush