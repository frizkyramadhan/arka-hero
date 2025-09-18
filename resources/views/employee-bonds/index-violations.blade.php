@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee-bonds.index') }}">Employee Bonds</a></li>
                        <li class="breadcrumb-item active">Violations</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left col -->
                <div class="col-lg-12">
                    <!-- Custom tabs (Charts with tabs)-->
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <div class="d-flex flex-row justify-content-between align-items-start mb-1">
                                    <h3 class="card-title mb-0">
                                        <strong>{{ $subtitle }}</strong>
                                    </h3>
                                    <div class="d-flex flex-column flex-md-row ms-auto gap-2">
                                        <a href="{{ route('bond-violations.create') }}"
                                            class="btn btn-warning mb-md-0 ml-1 mb-1">
                                            <i class="fas fa-plus"></i> Add Violation
                                        </a>
                                        <a href="{{ route('employee-bonds.index') }}"
                                            class="btn btn-info mb-md-0 ml-1 mb-1">
                                            <i class="fas fa-handshake"></i> All Bonds
                                        </a>
                                    </div>
                                </div>
                            </div><!-- /.card-header -->
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="violations-table" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center">No</th>
                                                <th class="align-middle">Employee</th>
                                                <th class="align-middle">Bond Name</th>
                                                <th class="align-middle text-center">Violation Date</th>
                                                <th class="align-middle">Reason</th>
                                                <th class="align-middle text-center">Days Worked</th>
                                                <th class="align-middle text-center">Days Remaining</th>
                                                <th class="align-middle text-center">Penalty Amount</th>
                                                <th class="align-middle text-center">Paid Amount</th>
                                                <th class="align-middle text-center">Status</th>
                                                <th class="align-middle text-center" width="12%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($violations as $violation)
                                                <tr>
                                                    <td class="align-middle text-center">{{ $loop->iteration }}</td>
                                                    <td class="align-middle">
                                                        <strong>{{ $violation->employeeBond->employee->fullname }}</strong><br>
                                                        <small
                                                            class="text-muted">{{ $violation->employeeBond->employee->identity_card }}</small>
                                                    </td>
                                                    <td class="align-middle">
                                                        <strong>{{ $violation->employeeBond->bond_name }}</strong>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        {{ $violation->violation_date->format('d/m/Y') }}</td>
                                                    <td class="align-middle">
                                                        {{ $violation->reason ? Str::limit($violation->reason, 30) : '-' }}
                                                    </td>
                                                    <td class="align-middle text-center">{{ $violation->days_worked }} days
                                                    </td>
                                                    <td class="align-middle text-center">{{ $violation->days_remaining }}
                                                        days</td>
                                                    <td class="align-middle text-center">
                                                        {{ $violation->formatted_calculated_penalty }}</td>
                                                    <td class="align-middle text-center">
                                                        {{ $violation->formatted_paid_penalty }}</td>
                                                    <td class="align-middle text-center">
                                                        @if ($violation->payment_status == 'paid')
                                                            <span class="badge badge-success">Paid</span>
                                                        @elseif($violation->payment_status == 'partial')
                                                            <span class="badge badge-warning">Partial</span>
                                                        @else
                                                            <span class="badge badge-danger">Pending</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <a href="{{ route('bond-violations.show', $violation->id) }}"
                                                            class="btn btn-sm btn-info" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('bond-violations.edit', $violation->id) }}"
                                                            class="btn btn-sm btn-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button class="btn btn-sm btn-danger"
                                                            onclick="deleteViolation({{ $violation->id }})" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <div class="d-flex justify-content-center">
                                    {{ $violations->links() }}
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this bond violation? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        function deleteViolation(violationId) {
            document.getElementById('deleteForm').action = `{{ route('bond-violations.destroy', '') }}/${violationId}`;
            $('#deleteModal').modal('show');
        }

        $(function() {
            // Initialize Select2 Elements with Bootstrap4 theme
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option',
                allowClear: true
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize DataTable
            var table = $("#violations-table").DataTable({
                responsive: true,
                autoWidth: true,
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10', '25', '50', '100', 'Show all']
                ],
                dom: 'rtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: false,
                paging: false,
                lengthChange: false,
                searching: false,
                ordering: true,
                info: false,
                autoWidth: false,
                responsive: true,
            });
        });
    </script>
@endsection
