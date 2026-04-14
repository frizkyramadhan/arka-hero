@extends('layouts.main')

@section('title', $subtitle ?? ($title ?? 'Overtime'))

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        #lines-table .select2-container {
            min-width: 220px;
        }

        #lines-table .select2-container--bootstrap4 .select2-selection--single {
            min-height: calc(1.8125rem + 2px);
        }
    </style>
@endsection

@section('content')
    @php
        $details = $details ?? [
            ['administration_id' => '', 'time_in' => '', 'time_out' => '', 'work_description' => ''],
        ];
    @endphp

    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('overtime.requests.index') }}">{{ $title }}</a>
                        </li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            @if (session('toast_error'))
                <div class="alert alert-danger alert-dismissible fade show">{{ session('toast_error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <form method="POST" action="{{ $formAction }}" id="overtimeRequestForm">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-business-time mr-2"></i>
                                    <strong>Overtime Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="project_id">Project <span class="text-danger">*</span></label>
                                            <select name="project_id" id="project_id"
                                                class="form-control select2bs4 @error('project_id') is-invalid @enderror"
                                                required style="width: 100%;">
                                                <option value="">— Select project —</option>
                                                @foreach ($projects as $p)
                                                    <option value="{{ $p->id }}"
                                                        {{ (string) old('project_id', isset($overtimeRequest) ? $overtimeRequest->project_id : '') === (string) $p->id ? 'selected' : '' }}>
                                                        {{ $p->project_code }} - {{ $p->project_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('project_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="overtime_date">Overtime date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="overtime_date" id="overtime_date"
                                                    class="form-control @error('overtime_date') is-invalid @enderror"
                                                    value="{{ old('overtime_date', isset($overtimeRequest) && $overtimeRequest->overtime_date ? $overtimeRequest->overtime_date->format('Y-m-d') : '') }}"
                                                    required>
                                                @error('overtime_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="remarks">Remarks (optional)</label>
                                    <textarea name="remarks" id="remarks" class="form-control" rows="3" maxlength="2000"
                                        placeholder="Additional notes for this overtime request">{{ old('remarks', isset($overtimeRequest) ? $overtimeRequest->remarks : '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Employee Details</strong>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="btn-add-line" title="Add row">
                                        <i class="fas fa-plus"></i> Add Row
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0" id="lines-table">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Employee (NIK - Name)</th>
                                                <th style="width:10%" class="align-middle">IN</th>
                                                <th style="width:10%" class="align-middle">OUT</th>
                                                <th class="align-middle">Work description</th>
                                                <th style="width:50px" class="text-center align-middle">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lines-body">
                                            @foreach ($details as $idx => $line)
                                                <tr class="line-row" data-index="{{ $idx }}">
                                                    <td>
                                                        <select name="details[{{ $idx }}][administration_id]"
                                                            class="form-control  line-admin select2bs4"
                                                            data-selected="{{ $line['administration_id'] ?? '' }}" required
                                                            style="width: 100%;">
                                                            <option value="">— Select project first —</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="time" name="details[{{ $idx }}][time_in]"
                                                            class="form-control"
                                                            value="{{ old('details.' . $idx . '.time_in', $line['time_in'] ?? '') }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="time"
                                                            name="details[{{ $idx }}][time_out]"
                                                            class="form-control"
                                                            value="{{ old('details.' . $idx . '.time_out', $line['time_out'] ?? '') }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="text"
                                                            name="details[{{ $idx }}][work_description]"
                                                            class="form-control"
                                                            value="{{ old('details.' . $idx . '.work_description', $line['work_description'] ?? '') }}"
                                                            placeholder="Description">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <a href="javascript:void(0)" class="text-danger btn-remove-line"
                                                            title="Remove row">
                                                            <i class="fas fa-times-circle"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @error('details')
                                <div class="card-footer text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Approver Selection</strong>
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => old('manual_approvers', []),
                                    'required' => true,
                                    'multiple' => true,
                                    'documentType' => 'overtime_request',
                                ])
                            </div>
                        </div>

                        <div class="card elevation-3">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <button type="submit" name="submit_action" value="draft"
                                            class="btn btn-warning btn-block">
                                            <i class="fas fa-save mr-2"></i> Save as Draft
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="submit_action" value="submit"
                                            class="btn btn-success btn-block">
                                            <i class="fas fa-paper-plane mr-2"></i> Save & Submit
                                        </button>
                                    </div>
                                </div>
                                <a href="{{ $cancelRoute }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle mr-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        (function() {
            const urlTpl = @json(url('/overtime/ajax/administrations-by-project/__PID__'));
            let lineIndex = {{ count($details) }};

            function csrfHeaders() {
                return {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                };
            }

            function focusOpenSelect2Search() {
                setTimeout(function() {
                    const el = document.querySelector('.select2-container--open .select2-search__field');
                    if (el) {
                        el.focus();
                    }
                }, 0);
            }

            function destroyEmployeeSelect2($els) {
                $els.each(function() {
                    const $s = $(this);
                    if ($s.hasClass('select2-hidden-accessible')) {
                        $s.select2('destroy');
                    }
                });
            }

            function initEmployeeSelect2($els) {
                destroyEmployeeSelect2($els);
                $els.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    minimumResultsForSearch: 0
                }).off('select2:open.otOvertimeLine').on('select2:open.otOvertimeLine', focusOpenSelect2Search);
            }

            function fillAdminOptions(projectId) {
                destroyEmployeeSelect2($('.line-admin'));
                if (!projectId) {
                    $('.line-admin').each(function() {
                        this.innerHTML = '<option value="">— Select project first —</option>';
                    });
                    initEmployeeSelect2($('.line-admin'));
                    return;
                }
                const url = urlTpl.replace('__PID__', projectId);
                fetch(url, {
                        headers: csrfHeaders()
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(rows) {
                        $('.line-admin').each(function() {
                            const sel = this;
                            const selected = sel.getAttribute('data-selected') || sel.value || '';
                            sel.innerHTML = '<option value="">— Select Employee —</option>';
                            rows.forEach(function(row) {
                                const opt = document.createElement('option');
                                opt.value = row.id;
                                opt.textContent = row.label;
                                if (String(row.id) === String(selected)) {
                                    opt.selected = true;
                                }
                                sel.appendChild(opt);
                            });
                        });
                        initEmployeeSelect2($('.line-admin'));
                    })
                    .catch(function() {});
            }

            $('#project_id').on('change', function() {
                $('.line-admin').removeAttr('data-selected');
                fillAdminOptions(this.value);
            });

            $(function() {
                $('#project_id').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    minimumResultsForSearch: 0
                }).on('select2:open', focusOpenSelect2Search);

                if ($('#project_id').val()) {
                    fillAdminOptions($('#project_id').val());
                } else {
                    initEmployeeSelect2($('.line-admin'));
                }
            });

            $('#btn-add-line').on('click', function() {
                const tbody = document.getElementById('lines-body');
                const tr = document.createElement('tr');
                tr.className = 'line-row';
                tr.innerHTML =
                    '<td><select name="details[' + lineIndex +
                    '][administration_id]" class="form-control line-admin select2bs4" style="width:100%;" required><option value="">— Select employee —</option></select></td>' +
                    '<td><input type="time" name="details[' + lineIndex +
                    '][time_in]" class="form-control" required></td>' +
                    '<td><input type="time" name="details[' + lineIndex +
                    '][time_out]" class="form-control" required></td>' +
                    '<td><input type="text" name="details[' + lineIndex +
                    '][work_description]" class="form-control" placeholder="Description"></td>' +
                    '<td class="text-center align-middle"><a href="javascript:void(0)" class="text-danger btn-remove-line" title="Remove"><i class="fas fa-times-circle"></i></a></td>';
                tbody.appendChild(tr);
                lineIndex++;
                const pid = $('#project_id').val();
                if (pid) {
                    fillAdminOptions(pid);
                } else {
                    initEmployeeSelect2($(tr).find('.line-admin'));
                }
            });

            $(document).on('click', '.btn-remove-line', function() {
                const tbody = document.getElementById('lines-body');
                if (tbody.querySelectorAll('tr').length <= 1) {
                    alert('At least one line is required.');
                    return;
                }
                const $tr = $(this).closest('tr');
                destroyEmployeeSelect2($tr.find('.line-admin'));
                $tr.remove();
            });
        })();
    </script>
@endsection
