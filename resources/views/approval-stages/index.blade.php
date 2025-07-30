@extends('layouts.main')

@section('title', 'Approval Stages Management')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Approval Stages Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Approval Stages</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Approval Stages Configuration</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.stages.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Create New
                                </a>

                            </div>
                        </div>
                        <div class="card-body">
                            <table id="approval-stages-table" class="table table-bordered table-striped"
                                style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Approver</th>
                                        <th>Projects</th>
                                        <th>Departments</th>
                                        <th>Document Types</th>
                                        <th class="text-center" style="width: 12%;">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#approval-stages-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('approval.stages.data') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'approver',
                        name: 'approver'
                    },
                    {
                        data: 'projects',
                        name: 'projects'
                    },
                    {
                        data: 'departments',
                        name: 'departments'
                    },
                    {
                        data: 'documents',
                        name: 'documents'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ]
            });

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Delete approval stage function
            window.deleteApprovalStage = function(id) {
                if (confirm(
                        'Are you sure you want to delete all approval stages for this approver? This action cannot be undone.'
                    )) {
                    $.ajax({
                        url: "{{ route('approval.stages.destroy', '') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            // Reload the page to show toast messages
                            window.location.reload();
                        },
                        error: function(xhr) {
                            let errorMessage =
                                'An error occurred while deleting the approval stages.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }
                            toastr.error(errorMessage);
                        }
                    });
                }
            };
        });
    </script>
@endsection
