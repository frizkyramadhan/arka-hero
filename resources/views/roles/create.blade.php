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
    <form action="{{ url('roles') }}" method="POST">
      @csrf
      <div class="row">
        <!-- Left col -->
        <div class="col-md-8">
          <!-- Role Information -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <strong>Role Information</strong>
              </h3>
              <div class="card-tools">
                <a class="btn btn-warning btn-sm" href="{{ url('roles') }}">
                  <i class="fas fa-undo-alt"></i> Back
                </a>
              </div>
            </div>
            <div class="card-body">
              <div class="form-group">
                <label>Role Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Enter role name">
                @error('name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
          </div>
        </div>

        <!-- Right col -->
        <div class="col-md-4">
          <!-- Permissions Card -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">
                <strong>Permissions</strong>
              </h3>
              <div class="card-tools">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="select_all_permissions">
                  <label class="form-check-label" for="select_all_permissions">Select All</label>
                </div>
              </div>
            </div>
            <div class="card-body" style="max-height: 600px; overflow-y: auto;">
              @error('permissions')
              <div class="alert alert-danger py-2">
                {{ $message }}
              </div>
              @enderror
              <div class="row">
                @foreach($permissions as $permission)
                <div class="col-12">
                  <div class="form-check mb-2">
                    <input class="form-check-input permission-checkbox" type="checkbox" id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->name }}" {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) ? 'checked' : '' }}>
                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                      {{ $permission->name }}
                    </label>
                  </div>
                </div>
                @endforeach
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Role
              </button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</section>
<!-- /.content -->
@endsection

@section('styles')
<style>
  .card-body::-webkit-scrollbar {
    width: 6px;
  }
  .card-body::-webkit-scrollbar-track {
    background: #f1f1f1;
  }
  .card-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
  }
  .card-body::-webkit-scrollbar-thumb:hover {
    background: #555;
  }
</style>
@endsection

@section('scripts')
<script>
  $(document).ready(function() {
    // Handle select all click
    $('#select_all_permissions').click(function() {
      $('.permission-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Update select all checkbox jika ada perubahan pada permission checkbox
    $('.permission-checkbox').click(function() {
      if($('.permission-checkbox:checked').length == $('.permission-checkbox').length) {
        $('#select_all_permissions').prop('checked', true);
      } else {
        $('#select_all_permissions').prop('checked', false);
      }
    });

    // Set initial state of select all checkbox
    if($('.permission-checkbox:checked').length == $('.permission-checkbox').length && $('.permission-checkbox').length > 0) {
      $('#select_all_permissions').prop('checked', true);
    }
  });
</script>
@endsection
