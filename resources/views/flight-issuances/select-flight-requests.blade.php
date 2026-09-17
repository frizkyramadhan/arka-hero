@extends('layouts.main')

@section('title', $title ?? 'Select Flight Requests for LG')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title ?? 'Select Flight Requests for LG' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('flight-issuances.index') }}">Flight Issuances</a></li>
                        <li class="breadcrumb-item active">Select FR</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('flight-issuances.store-selected-frs') }}" id="selectFrForm">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div id="accordion">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><strong>Select Flight Requests</strong></h3>
                                </div>
                                <div class="card-body">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h4 class="card-title w-100">
                                                <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                                                    <i class="fas fa-filter"></i> Filter
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapseOne" class="collapse" data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Status</label>
                                                            <select class="form-control select2bs4" id="status"
                                                                name="status" multiple>
                                                                <option value="approved" selected>Approved</option>
                                                                <option value="issued" selected>Issued</option>
                                                            </select>
                                                            <small class="text-muted">Default: Approved & Issued</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Form Number</label>
                                                            <input type="text" class="form-control" id="form_number"
                                                                name="form_number" placeholder="Search...">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Date From</label>
                                                            <input type="date" class="form-control" id="date_from"
                                                                name="date_from">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Date To</label>
                                                            <input type="date" class="form-control" id="date_to"
                                                                name="date_to">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-secondary w-100"
                                                                id="btn-reset" style="margin-bottom: 6px;">
                                                                <i class="fas fa-times"></i> Reset
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="flight-requests-table" class="table table-bordered table-striped"
                                            width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="align-middle text-center" width="5%">
                                                        <input type="checkbox" id="selectAllCheckbox">
                                                    </th>
                                                    <th class="align-middle text-center" width="5%">No</th>
                                                    <th class="align-middle">Form Number</th>
                                                    <th class="align-middle">Employee Name</th>
                                                    <th class="align-middle">NIK</th>
                                                    <th class="align-middle">Purpose of Travel</th>
                                                    <th class="align-middle text-center">Status</th>
                                                    <th class="align-middle text-center">Requested At</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" id="btnContinue" disabled>
                                            <i class="fas fa-arrow-right"></i> Continue with Selected FR
                                        </button>
                                        <a href="{{ route('flight-issuances.index') }}" class="btn btn-default">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <span class="ml-2 text-muted" id="selectedCount">0 selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Select Status'
            });

            // Persist selection across server-side pages AND filter reloads
            var selectedFrIds = {};

            function frId(value) {
                return String(value);
            }

            function isFrSelected(id) {
                return !!selectedFrIds[frId(id)];
            }

            function setFrSelected(id, selected) {
                id = frId(id);
                if (selected) {
                    selectedFrIds[id] = true;
                } else {
                    delete selectedFrIds[id];
                }
            }

            function restoreCheckboxState() {
                $('.fr-checkbox').each(function() {
                    $(this).prop('checked', isFrSelected($(this).val()));
                });
                updateSelectAllState();
                updateSelectedCount();
            }

            var table = $("#flight-requests-table").DataTable({
                scrollX: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flight-requests.data') }}",
                    data: function(d) {
                        var statusVal = $('#status').val();
                        // Send status array, or empty to use default (approved & issued)
                        d.status = statusVal && statusVal.length > 0 ? statusVal : null;
                        d.for_issuance = true; // Flag untuk default filter approved & issued
                        d.form_number = $('#form_number').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: null,
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            var id = frId(row.id);
                            var checked = isFrSelected(id) ? ' checked' : '';
                            return '<input type="checkbox" class="fr-checkbox" value="' +
                                id + '" data-id="' + id + '"' + checked + '>';
                        }
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'form_number',
                        name: 'form_number'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        orderable: false
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                        orderable: false
                    },
                    {
                        data: 'purpose_of_travel',
                        name: 'purpose_of_travel',
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'requested_at',
                        name: 'requested_at',
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'desc']
                ]
            });

            // After every ajax redraw (pagination, sort, filter): re-apply ticks from store
            table.on('draw', function() {
                restoreCheckboxState();
            });

            // Select All = current page only; merge into / remove from persisted set
            $(document).on('change', '#selectAllCheckbox', function() {
                var checked = this.checked;
                $('.fr-checkbox').each(function() {
                    setFrSelected($(this).val(), checked);
                    $(this).prop('checked', checked);
                });
                updateSelectedCount();
            });

            $(document).on('change', '.fr-checkbox', function() {
                setFrSelected($(this).val(), this.checked);
                updateSelectAllState();
                updateSelectedCount();
            });

            function updateSelectAllState() {
                var total = $('.fr-checkbox').length;
                var checked = $('.fr-checkbox:checked').length;
                $('#selectAllCheckbox').prop('checked', total > 0 && total === checked);
            }

            function updateSelectedCount() {
                var count = Object.keys(selectedFrIds).length;
                $('#selectedCount').text(count + ' selected');
                $('#btnContinue').prop('disabled', count === 0);
            }

            // Form submit - use persisted IDs (all pages), not only visible checkboxes
            $('#selectFrForm').on('submit', function(e) {
                var selectedIds = Object.keys(selectedFrIds);

                if (selectedIds.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one Flight Request.');
                    return false;
                }

                $('#selectFrForm').find('input[name="flight_request_ids[]"]').remove();

                selectedIds.forEach(function(id) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'flight_request_ids[]',
                        value: id
                    }).appendTo('#selectFrForm');
                });
            });

            // Filter: reload table only — do NOT clear selectedFrIds
            var filterReloadTimer = null;
            function reloadKeepingSelection() {
                clearTimeout(filterReloadTimer);
                filterReloadTimer = setTimeout(function() {
                    table.ajax.reload(null, false); // keep current page when possible
                }, 200);
            }

            $('#status, #date_from, #date_to').on('change', reloadKeepingSelection);
            $('#form_number').on('keyup change', reloadKeepingSelection);

            // Reset filter + clear selection (explicit user action)
            $('#btn-reset').on('click', function() {
                clearTimeout(filterReloadTimer);
                selectedFrIds = {};
                $('#status').val(['approved', 'issued']).trigger('change.select2');
                $('#form_number').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                table.ajax.reload(null, true);
            });
        });
    </script>
@endsection
