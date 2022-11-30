@extends('layouts.main')

@section('content')
<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">{{ $title }}</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
          <li class="breadcrumb-item active">{{ $title }}</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
  <div class="container-fluid">
    <!-- Main row -->
    <div class="row">
      <!-- Left col -->
      <section class="col-lg-12">
        <!-- Custom tabs (Charts with tabs)-->
        <div id="accordion">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <strong>{{ $subtitle }}</strong>
              </h3>
              <div class="card-tools">
                <ul class="nav nav-pills ml-auto">
                  <li class="nav-item mr-2">
                    <a class="btn btn-warning" href="{{ url('users') }}"><i class="fas fa-undo-alt"></i>
                      Back</a>
                  </li>
                </ul>
              </div>
            </div><!-- /.card-header -->

            <form class="form-horizontal" action="{{ url('users/' . $user->id) }}" method="POST">
              @method('PATCH')
              @csrf
              <div class="card-body">
                <div class="tab-content p-0">
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">User Name</label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" placeholder="User Name">
                      @error('name')
                      <div class="error invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Email</label>
                    <div class="col-sm-10">
                      <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $user->email) }}" placeholder="Email">
                      @error('email')
                      <div class="error invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Password</label>
                    <div class="col-sm-10">
                      <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" placeholder="Leave it blank if you don't want to change the password">
                      @error('password')
                      <div class="error invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Level</label>
                    <div class="col-sm-10">
                      <select name="level" class="form-control @error('level') is-invalid @enderror">
                        <option value="user" {{ old('level', $user->level) == 'user' ? 'selected' : '' }}>User
                        </option>
                        <option value="admin" {{ old('level', $user->level) == 'admin' ? 'selected' : '' }}>Administrator
                        </option>
                        <option value="superadmin" {{ old('level', $user->level) == 'superadmin' ? 'selected' : '' }}>
                          Super Administrator</option>
                      </select>
                      @error('level')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                  <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Status</label>
                    <div class="col-sm-10">
                      <select name="user_status" class="form-control @error('user_status') is-invalid @enderror">
                        <option value="1" {{ old('user_status', $user->user_status) == '1' ? 'selected' : '' }}>
                          Active</option>
                        <option value="0" {{ old('user_status', $user->user_status) == '0' ? 'selected' : '' }}>Inactive
                        </option>
                      </select>
                      @error('user_status')
                      <div class="invalid-feedback">
                        {{ $message }}
                      </div>
                      @enderror
                    </div>
                  </div>
                </div>
              </div><!-- /.card-body -->
              <div class="card-footer">
                <button type="submit" class="btn btn-info">Submit</button>
              </div>
            </form>
          </div>
        </div>
        <!-- /.card -->
      </section>
      <!-- right col -->
    </div>
    <!-- /.row (main row) -->
  </div><!-- /.container-fluid -->
</section>
<!-- /.content -->
@endsection
