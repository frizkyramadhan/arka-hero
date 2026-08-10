@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Disciplinary</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">
                                    <strong>{{ $subtitle }}</strong>
                                </h3>
                                <div class="card-tools">
                                    @can('employee-disciplinaries.show')
                                        <a href="{{ route('employee-disciplinaries.export') }}" id="btn-export-disciplinary"
                                            class="btn btn-success">
                                            <i class="fas fa-download"></i> Export
                                        </a>
                                    @endcan
                                    @can('employee-disciplinaries.create')
                                        <button type="button" class="btn btn-info" data-toggle="modal"
                                            data-target="#importModal">
                                            <i class="fas fa-upload"></i> Import
                                        </button>
                                        <a href="{{ route('employee-disciplinaries.create') }}" class="btn btn-warning">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <div class="card-body">
                                @if (session()->has('failures'))
                                    <div class="card card-danger">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="icon fas fa-exclamation-triangle"></i> Import Validation Errors
                                            </h3>
                                        </div>
                                        <div class="card-body p-0">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th style="width: 10%">Sheet</th>
                                                            <th style="width: 8%" class="text-center">Row</th>
                                                            <th style="width: 18%">Attribute</th>
                                                            <th style="width: 20%">Value</th>
                                                            <th>Error Message</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach (session()->get('failures') as $failure)
                                                            <tr>
                                                                <td>{{ $failure['sheet'] }}</td>
                                                                <td class="text-center">{{ $failure['row'] }}</td>
                                                                <td>
                                                                    <strong>{{ ucwords(str_replace('_', ' ', $failure['attribute'])) }}</strong>
                                                                </td>
                                                                <td>{{ is_scalar($failure['value'] ?? null) ? $failure['value'] : '' }}</td>
                                                                <td>{{ $failure['errors'] }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="p-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Please correct these errors in your Excel file and try importing again.
                                                    Successfully created rows (if any) are already saved.
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block w-100" data-toggle="collapse" href="#filterDisciplinary">
                                                <i class="fas fa-filter"></i> Filter
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="filterDisciplinary" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status-filter">
                                                            <option value="">All Status</option>
                                                            <option value="active">Active</option>
                                                            <option value="expired">Expired</option>
                                                            <option value="superseded">Superseded</option>
                                                            <option value="terminated">Terminated</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Type</label>
                                                        <select class="form-control select2bs4" id="type-filter">
                                                            <option value="">All Types</option>
                                                            @foreach ($typeOptions as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>PP Criteria</label>
                                                        <select class="form-control select2bs4" id="criterion-filter">
                                                            <option value="">All PP Criteria</option>
                                                            @foreach ($criteria as $criterion)
                                                                <option value="{{ $criterion->id }}">
                                                                    {{ $criterion->display_label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Employee</label>
                                                        <select class="form-control select2bs4" id="employee-filter">
                                                            <option value="">All Employees</option>
                                                            @foreach ($employees as $employee)
                                                                <option value="{{ $employee->id }}">
                                                                    {{ $employee->display_label ?? $employee->fullname }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" id="btn-reset"
                                                            class="btn btn-danger btn-block">
                                                            <i class="fas fa-times"></i> Reset Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="disciplinary-table" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>Employee</th>
                                                <th class="text-center">Type</th>
                                                <th>PP Criteria</th>
                                                <th class="text-center">Effective Date</th>
                                                <th class="text-center">Valid Until</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Remaining</th>
                                                <th class="text-center" width="12%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @can('employee-disciplinaries.create')
        <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">
                            <i class="fas fa-file-import mr-1"></i> Import Disciplinary Records
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('employee-disciplinaries.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                                <p class="mb-2 mb-md-0 text-muted small">
                                    Use the template or an Export file as a starting point.
                                </p>
                                <a href="{{ route('employee-disciplinaries.template') }}"
                                    class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download mr-1"></i> Download template
                                </a>
                            </div>

                            <div class="form-group">
                                <label for="import_file">Excel file (.xls / .xlsx) <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="import_file" name="file"
                                            accept=".xls,.xlsx" required>
                                        <label class="custom-file-label" for="import_file">Choose file</label>
                                    </div>
                                </div>
                            </div>

                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header py-2">
                                    <strong class="small text-uppercase text-muted mb-0">Column guide</strong>
                                </div>
                                <div class="card-body py-3">
                                    <div class="row">
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="border rounded h-100 p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge badge-danger mr-2">Required</span>
                                                </div>
                                                <ul class="mb-2 pl-3 small">
                                                    <li><code>type</code>
                                                        <br><span class="text-muted">Use:
                                                            <code>coaching</code>,
                                                            <code>counseling</code>,
                                                            <code>sp1</code>,
                                                            <code>sp2</code>, or
                                                            <code>sp3</code></span>
                                                    </li>
                                                    <li><code>effective_date</code></li>
                                                    <li><code>reason</code></li>
                                                </ul>
                                                <p class="mb-0 small text-muted">
                                                    Plus employee identity:
                                                    <code>identity_card</code> <em>and/or</em> <code>nik</code>
                                                    (at least one).
                                                </p>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3 mb-md-0">
                                            <div class="border rounded h-100 p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge badge-info mr-2">Optional</span>
                                                </div>
                                                <ul class="mb-0 pl-3 small">
                                                    <li><code>pp_notes</code></li>
                                                    <li>
                                                        <code>criterion_codes</code>
                                                        <br>
                                                        <span class="text-muted">Comma-separated, must match type:</span>
                                                        <ul class="pl-3 mb-0 mt-1 text-muted">
                                                            <li><code>PP-22.5.*</code> → coaching / counseling</li>
                                                            <li><code>PP-22.6.*</code> → <code>sp1</code></li>
                                                            <li><code>PP-22.8.*</code> → <code>sp3</code></li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded h-100 p-3 bg-light">
                                                <div class="d-flex align-items-center mb-2">
                                                    <span class="badge badge-secondary mr-2">Export-only</span>
                                                </div>
                                                <p class="small text-muted mb-2">
                                                    Present in Export / template for reference.
                                                    <strong>Ignored on import.</strong>
                                                </p>
                                                <ul class="mb-0 pl-3 small">
                                                    <li><code>end_date</code></li>
                                                    <li><code>remaining_days</code></li>
                                                    <li><code>status</code></li>
                                                    <li><code>Imported (doc later)</code></li>
                                                    <li><code>full_name</code></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-warning mb-0">
                                <h6 class="alert-heading mb-2">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    What does <code>Imported (doc later)</code> mean?
                                </h6>
                                <ul class="mb-0 small pl-3">
                                    <li class="mb-1">
                                        <strong>Yes</strong> — row was created via Excel import.
                                        Supporting document is <em>not</em> required at import time;
                                        upload it later with <strong>Upload Document</strong>.
                                    </li>
                                    <li class="mb-0">
                                        <strong>No</strong> (or blank on new import rows) — normal create in the app
                                        still requires a supporting document at create time.
                                        This column is export-only and ignored when you re-import a file.
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan

    @can('employee-disciplinaries.edit')
        <div class="modal fade" id="uploadDocumentModal" tabindex="-1" role="dialog"
            aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Supporting Document</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="upload-document-form" method="POST" enctype="multipart/form-data" action="#">
                        @csrf
                        <div class="modal-body">
                            <p class="mb-2">
                                <strong id="upload-document-employee"></strong>
                                <br>
                                <span class="text-muted" id="upload-document-type"></span>
                            </p>
                            <div class="form-group mb-0">
                                <label for="upload_document_file">Document <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file" id="upload_document_file" name="document"
                                    accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                <small class="form-text text-muted">Allowed: pdf, doc, docx, jpg, jpeg, png (max 5MB).</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-file-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
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
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%'
            });

            $('.custom-file-input').on('change', function() {
                var fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName || 'Choose file');
            });

            var table = $("#disciplinary-table").DataTable({
                responsive: true,
                autoWidth: true,
                searching: false,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('employee-disciplinaries.data')),
                    data: function(d) {
                        d.status = $('#status-filter').val();
                        d.type = $('#type-filter').val();
                        d.criterion_id = $('#criterion-filter').val();
                        d.employee_id = $('#employee-filter').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'employee',
                        orderable: false
                    },
                    {
                        data: 'type_label',
                        className: 'text-center'
                    },
                    {
                        data: 'criteria_list',
                        orderable: false
                    },
                    {
                        data: 'effective_date_fmt',
                        className: 'text-center'
                    },
                    {
                        data: 'end_date_fmt',
                        className: 'text-center'
                    },
                    {
                        data: 'status_badge',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'remaining_days',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: []
            });

            $('#status-filter, #type-filter, #criterion-filter, #employee-filter, #date_from, #date_to')
                .on('change', function() {
                    table.ajax.reload();
                });

            $('#btn-reset').on('click', function() {
                $('#status-filter, #type-filter, #criterion-filter, #employee-filter').val('').trigger('change');
                $('#date_from, #date_to').val('');
                table.ajax.reload();
            });

            $('#btn-export-disciplinary').on('click', function(e) {
                e.preventDefault();
                var filters = {
                    status: $('#status-filter').val(),
                    type: $('#type-filter').val(),
                    criterion_id: $('#criterion-filter').val(),
                    employee_id: $('#employee-filter').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val()
                };
                // Drop empty filters so export matches "all" when nothing is selected
                Object.keys(filters).forEach(function(key) {
                    if (filters[key] === null || filters[key] === undefined || filters[key] === '') {
                        delete filters[key];
                    }
                });
                var qs = $.param(filters);
                window.location.href = "{{ route('employee-disciplinaries.export') }}" + (qs ? '?' + qs : '');
            });

            var uploadUrlTemplate = @json(url('employee-disciplinaries/__ID__/upload-document'));

            $(document).on('click', '.btn-upload-document', function() {
                var id = $(this).data('id');
                $('#upload-document-employee').text($(this).data('employee'));
                $('#upload-document-type').text($(this).data('type'));
                $('#upload-document-form').attr('action', uploadUrlTemplate.replace('__ID__', id));
                $('#upload_document_file').val('');
                $('#uploadDocumentModal').modal('show');
            });
        });
    </script>
@endsection
