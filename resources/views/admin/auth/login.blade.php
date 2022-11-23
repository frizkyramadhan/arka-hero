@include('layouts.partials.header')

<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <div class="login-logo">
        <a href="{{ url('login') }}"><b>HCS</b>SIS</a>
      </div>
            @if (session('error'))
                <div class="text-danger text-center"> {{session('error')}}</div>
            @endif
            @if (session('success'))
                <div class="text-success text-center"> {{session('success')}}</div>
            @endif

      <form action="{{route('postLogin') }}" method="post">
        @csrf
        <div class="input-group mb-3">
          <input type="email" name="email" class="form-control" placeholder="Email">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        @error('email')
            <div class="text-danger">{{$message }}</div>
        @enderror
        <div class="input-group mb-3">
          <input type="password" name="password" class="form-control" placeholder="Password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        @error('password')
            <div class="text-danger">{{$message }}</div>
        @enderror
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block">Sign In</button>
          </div>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.login-card-body -->
  </div>
</div>
<!-- /.login-box -->

	@include('layouts.partials.scripts')