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
                    <div class="col-md-12">
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
                                    @php
                                        $isAdministrator = auth()->user()->hasRole('administrator');
                                    @endphp
                                    @if (!$isAdministrator)
                                        <div class="alert alert-warning py-2 mb-3">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Note:</strong> Only administrators can create administrator roles.
                                        </div>
                                    @endif
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" value="{{ old('name') }}" placeholder="Enter role name">
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
                    <div class="col-md-12">
                        <!-- Permissions Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <strong>Permissions</strong>
                                </h3>
                                <div class="card-tools">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select_all">
                                        <label class="form-check-label" for="select_all">Select All</label>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @error('permissions')
                                    <div class="alert alert-danger py-2">
                                        {{ $message }}
                                    </div>
                                @enderror

                                @php
                                    $isAdministrator = auth()->user()->hasRole('administrator');
                                @endphp
                                @if (!$isAdministrator)
                                    <div class="alert alert-info py-2 mb-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Note:</strong> Permission management features are only available to
                                        administrators.
                                    </div>
                                @endif

                                @php
                                    use Illuminate\Support\Str;
                                    $groupedPermissions = $permissions->groupBy(function ($permission) {
                                        return explode('.', $permission->name)[0];
                                    });
                                @endphp
                                <div class="row">
                                    @foreach ($groupedPermissions as $category => $permissionList)
                                        @php $catId = Str::slug($category, '_'); @endphp
                                        <div class="col-md-4 mb-3">
                                            <div class="card card-outline card-primary h-100">
                                                <div class="card-header">
                                                    <h3 class="card-title text-capitalize">
                                                        {{ str_replace('-', ' ', $category) }}</h3>
                                                    <div class="card-tools">
                                                        <div class="form-check">
                                                            <input type="checkbox" class="select-all-category"
                                                                data-category="{{ $catId }}"
                                                                id="select_all_{{ $catId }}">
                                                            <label class="mr-2 mb-0"
                                                                for="select_all_{{ $catId }}">Select All</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        @foreach ($permissionList as $permission)
                                                            <div class="col-12">
                                                                <div class="form-check mb-2">
                                                                    <input class="form-check-input permission-checkbox"
                                                                        data-category="{{ $catId }}" type="checkbox"
                                                                        name="permissions[]"
                                                                        value="{{ $permission->name }}"
                                                                        id="perm_{{ $permission->id }}"
                                                                        {{ is_array(old('permissions')) && in_array($permission->name, old('permissions')) ? 'checked' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
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
            // Global "Select All"
            $('#select_all').on('change', function() {
                var isChecked = $(this).is(':checked');
                $('.permission-checkbox').prop('checked', isChecked);
                $('.select-all-category').prop('checked', isChecked);
            });

            // Category-specific "Select All"
            $('.select-all-category').on('change', function(e) {
                e.preventDefault();
                var category = $(this).data('category');
                var isChecked = $(this).is(':checked');

                // Only affect permissions in this specific category
                $('.permission-checkbox[data-category="' + category + '"]').prop('checked', isChecked);

                // Update global select all state
                updateGlobalSelectAllState();
            });

            // Single permission checkbox
            $('.permission-checkbox').on('change', function() {
                var category = $(this).data('category');
                updateCategorySelectAllState(category);
                updateGlobalSelectAllState();
            });

            function updateCategorySelectAllState(category) {
                var totalInCategory = $('.permission-checkbox[data-category="' + category + '"]').length;
                var checkedInCategory = $('.permission-checkbox[data-category="' + category + '"]:checked').length;

                if (checkedInCategory === 0) {
                    $('.select-all-category[data-category="' + category + '"]').prop('checked', false);
                } else if (checkedInCategory === totalInCategory) {
                    $('.select-all-category[data-category="' + category + '"]').prop('checked', true);
                } else {
                    $('.select-all-category[data-category="' + category + '"]').prop('checked', false);
                }
            }

            function updateGlobalSelectAllState() {
                var totalPermissions = $('.permission-checkbox').length;
                var checkedPermissions = $('.permission-checkbox:checked').length;

                if (checkedPermissions === 0) {
                    $('#select_all').prop('checked', false);
                } else if (checkedPermissions === totalPermissions) {
                    $('#select_all').prop('checked', true);
                } else {
                    $('#select_all').prop('checked', false);
                }
            }

            // Initialize state on page load
            $('.select-all-category').each(function() {
                var category = $(this).data('category');
                updateCategorySelectAllState(category);
            });
            updateGlobalSelectAllState();
        });
    </script>
@endsection
