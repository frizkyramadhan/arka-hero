@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-0">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.mpp.index') }}">{{ $title }}</a>
                        </li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('recruitment.mpp.store') }}" method="POST" id="mpp-form">
                @csrf
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <!-- MPP Information Card -->
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-alt mr-2"></i>
                                    <strong>MPP Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="project_id">Project <span class="text-danger">*</span></label>
                                            <select name="project_id" id="project_id"
                                                class="form-control select2-primary @error('project_id') is-invalid @enderror"
                                                style="width: 100%;" required>
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('project_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="title">Title <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                                </div>
                                                <input type="text" name="title" id="title"
                                                    class="form-control @error('title') is-invalid @enderror"
                                                    value="{{ old('title') }}" required
                                                    placeholder="e.g., MPP Site 027C CEP Project">
                                                @error('title')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                        rows="3" placeholder="Additional information about this MPP (optional)">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-4">
                        <!-- Info Card -->
                        <div class="card card-info card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <h5><i class="icon fas fa-exclamation-triangle"></i> Instructions</h5>
                                    <ul class="mb-0 pl-3">
                                        <li>Input <strong>Qty Unit</strong> (opsional)</li>
                                        <li>Input jumlah <strong>Existing</strong> (yang sudah ada)</li>
                                        <li>Input jumlah <strong>Plan</strong> (yang dibutuhkan)</li>
                                        <li>Diff akan dihitung otomatis</li>
                                        <li>Klik <strong>Add Position</strong> untuk menambah baris</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Position Details Card - Full Width -->
                <div class="row">
                    <div class="col-12">
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Position Details</strong>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-sm btn-success" id="btn-add-row">
                                        <i class="fas fa-plus mr-1"></i> Add Position
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="p-3 bg-light border-bottom">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>S</strong> = Staff | <strong>NS</strong> = Non-Staff |
                                        Diff akan dihitung otomatis (Existing - Plan)
                                    </small>
                                </div>
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                    <table class="table table-bordered table-sm mb-0" id="details-table">
                                        <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                                            <tr>
                                                <th width="3%">#</th>
                                                <th width="20%">Position <span class="text-danger">*</span></th>
                                                <th width="6%" class="text-center">Qty<br>Unit</th>
                                                <th width="6%" class="text-center plan-header" title="Plan Staff">
                                                    Plan<br><small>S</small></th>
                                                <th width="6%" class="text-center plan-header" title="Plan Non-Staff">
                                                    Plan<br><small>NS</small></th>
                                                <th width="6%" class="text-center plan-header">
                                                    Plan<br><small>Total</small></th>
                                                <th width="6%" class="text-center existing-header"
                                                    title="Existing Staff">
                                                    Existing<br><small>S</small></th>
                                                <th width="6%" class="text-center existing-header"
                                                    title="Existing Non-Staff">
                                                    Existing<br><small>NS</small></th>
                                                <th width="6%" class="text-center existing-header">
                                                    Existing<br><small>Total</small></th>
                                                <th width="6%" class="text-center diff-header">
                                                    Diff<br><small>S</small>
                                                </th>
                                                <th width="6%" class="text-center diff-header">
                                                    Diff<br><small>NS</small>
                                                </th>
                                                <th width="6%" class="text-center diff-header">
                                                    Diff<br><small>Total</small></th>
                                                <th width="6%" class="text-center">Theory<br>Test</th>
                                                <th width="10%" class="text-center">Agreement<br>Type</th>
                                                <th width="4%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="details-tbody">
                                            <tr class="detail-row">
                                                <td class="row-number">
                                                    <span class="badge badge-secondary">1</span>
                                                </td>
                                                <td>
                                                    <select name="details[0][position_id]"
                                                        class="form-control form-control-sm select2-position" required
                                                        style="width: 100%;">
                                                        <option value="">Select Position</option>
                                                        @foreach ($positions as $position)
                                                            <option value="{{ $position->id }}">
                                                                {{ $position->position_name }}@if ($position->department)
                                                                    - {{ $position->department->department_name }}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="number" name="details[0][qty_unit]"
                                                        class="form-control form-control-sm text-center qty-unit"
                                                        value="" min="0" placeholder="0">
                                                </td>
                                                <td class="plan-cell">
                                                    <input type="number" name="details[0][plan_qty_s]"
                                                        class="form-control form-control-sm text-center plan-s"
                                                        value="0" min="0" required>
                                                </td>
                                                <td class="plan-cell">
                                                    <input type="number" name="details[0][plan_qty_ns]"
                                                        class="form-control form-control-sm text-center plan-ns"
                                                        value="0" min="0" required>
                                                </td>
                                                <td class="text-center plan-cell">
                                                    <span class="badge badge-info plan-total">0</span>
                                                </td>
                                                <td class="existing-cell">
                                                    <input type="number" name="details[0][existing_qty_s]"
                                                        class="form-control form-control-sm text-center existing-s"
                                                        value="0" min="0" required>
                                                </td>
                                                <td class="existing-cell">
                                                    <input type="number" name="details[0][existing_qty_ns]"
                                                        class="form-control form-control-sm text-center existing-ns"
                                                        value="0" min="0" required>
                                                </td>
                                                <td class="text-center existing-cell">
                                                    <span class="badge badge-warning existing-total">0</span>
                                                </td>
                                                <td class="text-center diff-cell">
                                                    <span class="badge diff-s diff-zero">0</span>
                                                </td>
                                                <td class="text-center diff-cell">
                                                    <span class="badge diff-ns diff-zero">0</span>
                                                </td>
                                                <td class="text-center diff-cell">
                                                    <span class="badge diff-total diff-zero">0</span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                            id="requires_theory_test_0"
                                                            name="details[0][requires_theory_test]" value="1">
                                                        <label class="custom-control-label" for="requires_theory_test_0">
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <select name="details[0][agreement_type]"
                                                        class="form-control form-control-sm" required>
                                                        <option value="pkwt" selected>PKWT</option>
                                                        <option value="pkwtt">PKWTT</option>
                                                        <option value="magang">Magang</option>
                                                        <option value="harian">Harian</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger btn-remove-row"
                                                        title="Remove this row">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr class="bg-light font-weight-bold">
                                                <td colspan="2" class="text-right">
                                                    <i class="fas fa-calculator mr-1"></i>TOTAL:
                                                </td>
                                                <td></td>
                                                <td class="text-center plan-cell" id="summary-plan-s">0</td>
                                                <td class="text-center plan-cell" id="summary-plan-ns">0</td>
                                                <td class="text-center plan-cell" id="summary-plan-total">
                                                    <span class="badge badge-info">0</span>
                                                </td>
                                                <td class="text-center existing-cell" id="summary-existing-s">0</td>
                                                <td class="text-center existing-cell" id="summary-existing-ns">0</td>
                                                <td class="text-center existing-cell" id="summary-existing-total">
                                                    <span class="badge badge-warning">0</span>
                                                </td>
                                                <td class="text-center diff-cell" id="summary-diff-s">
                                                    <span class="badge">0</span>
                                                </td>
                                                <td class="text-center diff-cell" id="summary-diff-ns">
                                                    <span class="badge">0</span>
                                                </td>
                                                <td class="text-center diff-cell" id="summary-diff-total">
                                                    <span class="badge">0</span>
                                                </td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="row">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save mr-2"></i> Save MPP
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('recruitment.mpp.index') }}"
                                            class="btn btn-secondary btn-block">
                                            <i class="fas fa-times-circle mr-2"></i> Cancel
                                        </a>
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
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 2.25rem !important;
        }

        .card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        }

        .input-group-text {
            border-radius: 0.25rem;
        }

        .btn {
            border-radius: 0.25rem;
        }

        /* Custom colors for select2 */
        .select2-container--bootstrap4.select2-container--primary .select2-selection {
            border-color: #007bff;
        }

        /* Table styling */
        #details-table thead th {
            background-color: #28a745;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Plan section - Blue/Info color */
        #details-table thead th.plan-header {
            background-color: #17a2b8;
        }

        /* Existing section - Orange/Warning color */
        #details-table thead th.existing-header {
            background-color: #ffc107;
            color: #212529;
        }

        /* Diff section - Purple/Secondary color */
        #details-table thead th.diff-header {
            background-color: #6c757d;
        }

        /* Plan cells - Light blue background */
        #details-table tbody td.plan-cell,
        #details-table tfoot td.plan-cell {
            background-color: #d1ecf1;
        }

        /* Existing cells - Light yellow/orange background */
        #details-table tbody td.existing-cell,
        #details-table tfoot td.existing-cell {
            background-color: #fff3cd;
        }

        /* Diff cells - Light gray background */
        #details-table tbody td.diff-cell,
        #details-table tfoot td.diff-cell {
            background-color: #e2e3e5;
        }

        /* Hover effect for colored cells */
        #details-table tbody tr:hover td.plan-cell {
            background-color: #bee5eb;
        }

        #details-table tbody tr:hover td.existing-cell {
            background-color: #ffeaa7;
        }

        #details-table tbody tr:hover td.diff-cell {
            background-color: #d6d8db;
        }

        #details-table tbody tr:hover {
            background-color: transparent;
        }

        #details-table tbody tr:nth-child(even) {
            background-color: transparent;
        }

        #details-table tbody tr:nth-child(even):hover {
            background-color: transparent;
        }

        /* Row number and action column alignment */
        #details-table td.row-number {
            text-align: left;
            padding-left: 0.75rem;
        }

        #details-table td:last-child {
            text-align: left;
            padding-left: 0.5rem;
        }

        #details-table input[type="number"],
        #details-table input[type="text"] {
            padding: 0.4rem 0.5rem;
            font-size: 0.9rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: all 0.2s;
            width: 100%;
        }

        #details-table input[type="number"]:focus,
        #details-table input[type="text"]:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        #details-table .form-control-sm {
            height: calc(1.5em + 0.8rem + 2px);
        }

        /* Diff Colors */
        .diff-positive {
            background-color: #28a745 !important;
            color: white;
        }

        .diff-negative {
            background-color: #dc3545 !important;
            color: white;
        }

        .diff-zero {
            background-color: #6c757d !important;
            color: white;
        }

        /* Summary row */
        #details-table tfoot tr {
            background-color: #e9ecef !important;
            font-weight: 700;
        }

        #details-table tfoot td {
            padding: 0.75rem 0.5rem;
            border: 1px solid #dee2e6;
        }

        /* Content header styling */
        .content-header {
            margin-bottom: 1rem;
        }

        /* Button styling */
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            border-color: #545b62;
        }

        .form-control {
            border-radius: 0.25rem;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
    </style>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

    <script>
        $(function() {
            // Initialize Select2
            $('.select2-primary').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize Select2 for position selects
            $('.select2-position').select2({
                theme: 'bootstrap4',
                placeholder: 'Select Position',
                width: '100%'
            });

            // Auto-focus search field when any select2-position opens (using event delegation)
            $(document).on('select2:open', '.select2-position', function(e) {
                const $select2 = $(e.target);
                // Use requestAnimationFrame for better timing
                requestAnimationFrame(function() {
                    requestAnimationFrame(function() {
                        const select2Data = $select2.data('select2');
                        if (select2Data && select2Data.$dropdown) {
                            const $searchField = select2Data.$dropdown.find(
                                '.select2-search__field');
                            if ($searchField.length) {
                                $searchField[0].focus();
                            }
                        }
                    });
                });
            });

            let rowIndex = 0;
            const positions = @json($positions);

            // Add new row
            $('#btn-add-row').on('click', function() {
                rowIndex++;
                let positionOptions = '<option value="">Select Position</option>';
                positions.forEach(function(position) {
                    const deptName = position.department ? ' - ' + position.department
                        .department_name : '';
                    positionOptions +=
                        `<option value="${position.id}">${position.position_name}${deptName}</option>`;
                });
                const newRow = `
            <tr class="detail-row">
                <td class="row-number">
                    <span class="badge badge-secondary">${rowIndex + 1}</span>
                </td>
                <td>
                    <select name="details[${rowIndex}][position_id]" class="form-control form-control-sm select2-position" required style="width: 100%;">
                        ${positionOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="details[${rowIndex}][qty_unit]" class="form-control form-control-sm text-center qty-unit" value="" min="0" placeholder="0">
                </td>
                <td class="plan-cell">
                    <input type="number" name="details[${rowIndex}][plan_qty_s]" class="form-control form-control-sm text-center plan-s" value="0" min="0" required>
                </td>
                <td class="plan-cell">
                    <input type="number" name="details[${rowIndex}][plan_qty_ns]" class="form-control form-control-sm text-center plan-ns" value="0" min="0" required>
                </td>
                <td class="text-center plan-cell">
                    <span class="badge badge-info plan-total">0</span>
                </td>
                <td class="existing-cell">
                    <input type="number" name="details[${rowIndex}][existing_qty_s]" class="form-control form-control-sm text-center existing-s" value="0" min="0" required>
                </td>
                <td class="existing-cell">
                    <input type="number" name="details[${rowIndex}][existing_qty_ns]" class="form-control form-control-sm text-center existing-ns" value="0" min="0" required>
                </td>
                <td class="text-center existing-cell">
                    <span class="badge badge-warning existing-total">0</span>
                </td>
                <td class="text-center diff-cell">
                    <span class="badge diff-s diff-zero">0</span>
                </td>
                <td class="text-center diff-cell">
                    <span class="badge diff-ns diff-zero">0</span>
                </td>
                <td class="text-center diff-cell">
                    <span class="badge diff-total diff-zero">0</span>
                </td>
                <td class="text-center">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input"
                            id="requires_theory_test_${rowIndex}" name="details[${rowIndex}][requires_theory_test]"
                            value="1">
                        <label class="custom-control-label" for="requires_theory_test_${rowIndex}">
                        </label>
                    </div>
                </td>
                <td>
                    <select name="details[${rowIndex}][agreement_type]"
                        class="form-control form-control-sm" required>
                        <option value="pkwt" selected>PKWT</option>
                        <option value="pkwtt">PKWTT</option>
                        <option value="magang">Magang</option>
                        <option value="harian">Harian</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger btn-remove-row" title="Remove this row">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
                $('#details-tbody').append(newRow);

                // Initialize Select2 for the new select
                const $newSelect = $('#details-tbody tr:last .select2-position');
                $newSelect.select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select Position',
                    width: '100%'
                }).on('select2:open', function() {
                    // Use requestAnimationFrame for better timing
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            const select2Data = $newSelect.data('select2');
                            if (select2Data && select2Data.$dropdown) {
                                const $searchField = select2Data.$dropdown.find(
                                    '.select2-search__field');
                                if ($searchField.length) {
                                    $searchField[0].focus();
                                }
                            }
                        });
                    });
                });

                updateRowNumbers();
                calculateSummary();

                // Scroll to new row
                $('html, body').animate({
                    scrollTop: $('#details-tbody tr:last').offset().top - 100
                }, 300);

                // Focus on position select
                $('#details-tbody tr:last .select2-position').focus();
            });

            // Remove row
            $(document).on('click', '.btn-remove-row', function() {
                if ($('.detail-row').length > 1) {
                    $(this).closest('tr').remove();
                    updateRowNumbers();
                    calculateSummary();
                } else {
                    Swal.fire('Warning', 'At least one position detail is required', 'warning');
                }
            });

            // Calculate on input change
            $(document).on('input', '.existing-s, .existing-ns, .plan-s, .plan-ns', function() {
                const row = $(this).closest('tr');
                calculateRow(row);
                calculateSummary();
            });

            // Calculate individual row
            function calculateRow(row) {
                const existingS = parseInt(row.find('.existing-s').val()) || 0;
                const existingNs = parseInt(row.find('.existing-ns').val()) || 0;
                const planS = parseInt(row.find('.plan-s').val()) || 0;
                const planNs = parseInt(row.find('.plan-ns').val()) || 0;

                const existingTotal = existingS + existingNs;
                const planTotal = planS + planNs;
                const diffS = existingS - planS;
                const diffNs = existingNs - planNs;
                const diffTotal = existingTotal - planTotal;

                row.find('.existing-total').text(existingTotal);
                row.find('.plan-total').text(planTotal);

                // Apply color to diff badges
                const diffSClass = diffS > 0 ? 'diff-positive' : (diffS < 0 ? 'diff-negative' : 'diff-zero');
                row.find('.diff-s').text(diffS > 0 ? '+' + diffS : diffS)
                    .removeClass('diff-positive diff-negative diff-zero')
                    .addClass(diffSClass);

                const diffNsClass = diffNs > 0 ? 'diff-positive' : (diffNs < 0 ? 'diff-negative' : 'diff-zero');
                row.find('.diff-ns').text(diffNs > 0 ? '+' + diffNs : diffNs)
                    .removeClass('diff-positive diff-negative diff-zero')
                    .addClass(diffNsClass);

                const diffTotalClass = diffTotal > 0 ? 'diff-positive' : (diffTotal < 0 ? 'diff-negative' :
                    'diff-zero');
                row.find('.diff-total').text(diffTotal > 0 ? '+' + diffTotal : diffTotal)
                    .removeClass('diff-positive diff-negative diff-zero')
                    .addClass(diffTotalClass);
            }

            // Calculate summary totals
            function calculateSummary() {
                let sumExistingS = 0,
                    sumExistingNs = 0,
                    sumPlanS = 0,
                    sumPlanNs = 0;

                $('.detail-row').each(function() {
                    sumExistingS += parseInt($(this).find('.existing-s').val()) || 0;
                    sumExistingNs += parseInt($(this).find('.existing-ns').val()) || 0;
                    sumPlanS += parseInt($(this).find('.plan-s').val()) || 0;
                    sumPlanNs += parseInt($(this).find('.plan-ns').val()) || 0;
                });

                const sumExistingTotal = sumExistingS + sumExistingNs;
                const sumPlanTotal = sumPlanS + sumPlanNs;
                const sumDiffS = sumExistingS - sumPlanS;
                const sumDiffNs = sumExistingNs - sumPlanNs;
                const sumDiffTotal = sumExistingTotal - sumPlanTotal;

                $('#summary-existing-s').text(sumExistingS);
                $('#summary-existing-ns').text(sumExistingNs);
                $('#summary-existing-total').text(sumExistingTotal);
                $('#summary-plan-s').text(sumPlanS);
                $('#summary-plan-ns').text(sumPlanNs);
                $('#summary-plan-total').text(sumPlanTotal);

                // Apply color to summary diff
                const summaryDiffSClass = sumDiffS > 0 ? 'diff-positive' : (sumDiffS < 0 ? 'diff-negative' :
                    'diff-zero');
                $('#summary-diff-s span').text(sumDiffS > 0 ? '+' + sumDiffS : sumDiffS)
                    .removeClass('diff-positive diff-negative diff-zero')
                    .addClass(summaryDiffSClass);

                const summaryDiffNsClass = sumDiffNs > 0 ? 'diff-positive' : (sumDiffNs < 0 ? 'diff-negative' :
                    'diff-zero');
                $('#summary-diff-ns span').text(sumDiffNs > 0 ? '+' + sumDiffNs : sumDiffNs)
                    .removeClass('diff-positive diff-negative diff-zero')
                    .addClass(summaryDiffNsClass);

                const summaryDiffTotalClass = sumDiffTotal > 0 ? 'diff-positive' : (sumDiffTotal < 0 ?
                    'diff-negative' : 'diff-zero');
                $('#summary-diff-total span').text(sumDiffTotal > 0 ? '+' + sumDiffTotal : sumDiffTotal)
                    .removeClass('diff-positive diff-negative diff-zero')
                    .addClass(summaryDiffTotalClass);
            }

            // Update row numbers
            function updateRowNumbers() {
                $('.detail-row').each(function(index) {
                    $(this).find('.row-number').html('<span class="badge badge-secondary">' + (index + 1) +
                        '</span>');

                    // Update input and select names
                    $(this).find('input, select').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const newName = name.replace(/details\[\d+\]/, 'details[' + index +
                                ']');
                            $(this).attr('name', newName);
                        }
                    });
                });

                rowIndex = $('.detail-row').length - 1;
            }

            // Form validation before submit
            $('#mpp-form').on('submit', function(e) {
                let hasError = false;
                let errorMessage = '';

                // Check if at least one position has data
                let hasPositionData = false;
                $('.detail-row').each(function() {
                    const positionId = $(this).find('select[name*="[position_id]"]').val();
                    const planS = parseInt($(this).find('.plan-s').val()) || 0;
                    const planNs = parseInt($(this).find('.plan-ns').val()) || 0;

                    if (positionId && (planS > 0 || planNs > 0)) {
                        hasPositionData = true;
                    }
                });

                if (!hasPositionData) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Validation Error',
                        text: 'Please add at least one position with plan quantity greater than 0',
                    });
                    return false;
                }

                return true;
            });

            // Initial calculation
            calculateSummary();
        });
    </script>
@endsection
