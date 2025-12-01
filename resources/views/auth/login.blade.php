@include('layouts.partials.header')

<body class="hold-transition login-page pace-primary">
  <div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <a href="{{ url('/') }}" class="h1"><b>{{ $subtitle }}</b></a>
      </div>
      <div class="card-body">
        <form action="{{ route('login') }}" method="post">
          @csrf
          <div class="input-group mb-3">
            <input type="email" class="form-control" placeholder="Email" name="email" autofocus>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" class="form-control" placeholder="Password" name="password">
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
        <div class="mt-5 text-muted text-center">
          Don't have an account? <a href="{{ url('register') }}" tabindex="4"><b>Register!</b></a>
        </div>
      </div>
      <div class="card-footer text-muted text-center">
        Copyright &copy; IT Department 2022
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.login-box -->

  @include('layouts.partials.scripts')
