@extends('layouts.main')

@section('title', $title)

@section('styles')
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

        .card.elevation-2 .card-body {
            padding: 0.75rem;
        }

        #request_number_preview.alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        #request_number_preview.alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        @media (max-width: 767.98px) {
            .card-body .row .col-md-6:first-child {
                margin-bottom: 1rem;
            }

            .btn-block {
                padding: 0.75rem 1rem;
                font-size: 1rem;
                font-weight: 500;
            }
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ $cancelRoute }}">{{ $title }}</a></li>
                        <li class="breadcrumb-item active">{{ $doc ? 'Edit' : 'Add New' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ $formAction }}" method="POST" id="rcr-form">
                @csrf
                @if ($method === 'PUT')
                    @method('PUT')
                @endif

                <div class="row">
                    {{-- Left Column --}}
                    <div class="col-md-8">
                        {{-- Letter Number (create only) --}}
                        @if (! $doc)
                            <div class="card card-info card-outline elevation-2">
                                <div class="card-header py-2">
                                    <h3 class="card-title">
                                        <i class="fas fa-hashtag mr-2"></i>
                                        <strong>Letter Number</strong>
                                    </h3>
                                </div>
                                <div class="card-body py-2">
                                    @include('components.smart-letter-number-selector', [
                                        'categoryCode' => 'RCR',
                                        'required' => false,
                                        'selectedValue' => old('letter_number_id'),
                                    ])
                                </div>
                            </div>
                        @else
                            <input type="hidden" name="letter_number_id"
                                value="{{ old('letter_number_id', $doc->letter_number_id) }}">
                        @endif

                        {{-- Meeting Information --}}
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-door-open mr-2"></i>
                                    <strong>Meeting Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="request_number_preview">Reg. No</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text" id="request_number_preview"
                                                    class="form-control {{ $doc && $doc->request_number ? 'alert-success' : 'alert-warning' }}"
                                                    readonly
                                                    value="{{ old('request_number', $doc->request_number ?? '') }}"
                                                    placeholder="{{ $doc ? '—' : 'Select Letter Number to Generate Reg. No' }}">
                                            </div>
                                            @if (! $doc)
                                                <small class="form-text text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Reg. No will be auto-generated when you select a letter number above
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="meeting_date">Meeting Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date" name="meeting_date" id="meeting_date"
                                                    class="form-control" required
                                                    value="{{ old('meeting_date', optional($doc->meeting_date ?? null)->format('Y-m-d')) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="project_id">Location (Project) <span
                                                    class="text-danger">*</span></label>
                                            <select name="project_id" id="project_id" class="form-control select2bs4"
                                                required style="width: 100%;">
                                                <option value="">— Select —</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        data-code="{{ $project->project_code }}"
                                                        {{ old('project_id', $doc->project_id ?? '') == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="meeting_room_id">Room <span class="text-danger">*</span></label>
                                            <select name="meeting_room_id" id="meeting_room_id"
                                                class="form-control select2bs4" required style="width: 100%;">
                                                <option value="">— Select project first —</option>
                                                @foreach ($rooms as $room)
                                                    <option value="{{ $room->id }}"
                                                        data-facilities="{{ e($room->facilities) }}"
                                                        {{ old('meeting_room_id', $doc->meeting_room_id ?? '') == $room->id ? 'selected' : '' }}>
                                                        {{ $room->room_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="department_id">Division / Department</label>
                                            <select name="department_id" id="department_id" class="form-control select2bs4"
                                                style="width: 100%;">
                                                <option value="">— Optional —</option>
                                                @foreach ($departments as $dept)
                                                    <option value="{{ $dept->id }}"
                                                        {{ old('department_id', $doc->department_id ?? '') == $dept->id ? 'selected' : '' }}>
                                                        {{ $dept->department_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="meeting_title">Meeting Title <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-heading"></i></span>
                                                </div>
                                                <input type="text" name="meeting_title" id="meeting_title"
                                                    class="form-control" required
                                                    value="{{ old('meeting_title', $doc->meeting_title ?? '') }}"
                                                    placeholder="Meeting title">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="start_time">Start Time <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                </div>
                                                <input type="time" name="start_time" id="start_time"
                                                    class="form-control" required
                                                    value="{{ old('start_time', isset($doc) && $doc->start_time ? \Carbon\Carbon::parse($doc->start_time)->format('H:i') : '') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="end_time">End Time <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                </div>
                                                <input type="time" name="end_time" id="end_time"
                                                    class="form-control" required
                                                    value="{{ old('end_time', isset($doc) && $doc->end_time ? \Carbon\Carbon::parse($doc->end_time)->format('H:i') : '') }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label for="attendees_count">Attendees <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-users"></i></span>
                                                </div>
                                                <input type="number" name="attendees_count" id="attendees_count"
                                                    class="form-control" min="1" required
                                                    value="{{ old('attendees_count', $doc->attendees_count ?? 1) }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="facilities">Facilities</label>
                                            <textarea name="facilities" id="facilities" class="form-control" rows="3"
                                                placeholder="Projector, whiteboard, etc.">{{ old('facilities', $doc->facilities ?? '') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Consumption --}}
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-utensils mr-2"></i>
                                    <strong>Consumption</strong>
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width:5%">✓</th>
                                                <th style="width:25%">Type</th>
                                                <th>Deskripsi / Jenis Makanan / Minuman</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($consumption as $type => $row)
                                                <tr>
                                                    <td class="text-center align-middle">
                                                        <input type="checkbox"
                                                            name="consumption[{{ $type }}][is_selected]"
                                                            value="1" class="consumption-check"
                                                            {{ !empty($row['is_selected']) ? 'checked' : '' }}>
                                                    </td>
                                                    <td class="align-middle">{{ $row['label'] }}</td>
                                                    <td>
                                                        <input type="text"
                                                            name="consumption[{{ $type }}][description]"
                                                            class="form-control form-control-sm"
                                                            value="{{ $row['description'] }}"
                                                            placeholder="Optional description">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column --}}
                    <div class="col-md-4">
                        {{-- Options --}}
                        <div class="card card-warning card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-cog mr-2"></i>
                                    <strong>Options</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="need_zoom"
                                            name="need_zoom" value="1"
                                            {{ old('need_zoom', $doc->need_zoom ?? false) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="need_zoom">
                                            Need Zoom Meeting ID
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Integrasi IT Work Order (Phase 2)
                                    </small>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="notes">Notes</label>
                                    <textarea name="notes" id="notes" class="form-control" rows="4" placeholder="Additional notes">{{ old('notes', $doc->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Approvers --}}
                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Approver Selection</strong>
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => old('manual_approvers', $doc->manual_approvers ?? []),
                                    'required' => false,
                                    'multiple' => true,
                                    'documentType' => 'room_consumption_request',
                                ])
                            </div>
                        </div>

                        {{-- Actions --}}
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
                                            class="btn btn-success btn-block"
                                            onclick="return confirm('Submit this request for approval?')">
                                            <i class="fas fa-paper-plane mr-2"></i> Save &amp; Submit
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
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        (function() {
            const romanMap = {
                1: 'I',
                2: 'II',
                3: 'III',
                4: 'IV',
                5: 'V',
                6: 'VI',
                7: 'VII',
                8: 'VIII',
                9: 'IX',
                10: 'X',
                11: 'XI',
                12: 'XII'
            };

            function initSelect2($el) {
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }
                $el.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        placeholder: 'Select an option'
                    })
                    .on('select2:open', function() {
                        var field = document.querySelector('.select2-search__field');
                        if (field) field.focus();
                    });
            }

            function pad4(n) {
                return String(n).padStart(4, '0');
            }

            function getLetterDataFromOption($option) {
                if (!$option.length || !$option.val()) {
                    return null;
                }
                const letterNumber = $option.attr('data-letter-number') || $option.data('letterNumber');
                if (!letterNumber) {
                    return null;
                }
                return {
                    letter_number: String(letterNumber),
                    project_code: $option.attr('data-project-code') || $option.data('projectCode') || null,
                };
            }

            const isEdit = @json((bool) $doc);

            function updatePreview() {
                if (isEdit) {
                    return;
                }

                const $preview = $('#request_number_preview');
                const $option = $('select[name="letter_number_id"] option:selected');
                const letterData = getLetterDataFromOption($option);
                const existingRegNo = @json(old('request_number', $doc->request_number ?? ''));

                if (!$option.val()) {
                    $preview.val(existingRegNo || '').removeClass('alert-success').addClass('alert-warning');
                    return;
                }

                if (!letterData) {
                    return;
                }

                let numeric = letterData.letter_number;
                if (/^RCR/i.test(numeric)) {
                    numeric = numeric.replace(/^RCR/i, '');
                }
                const parsed = parseInt(numeric, 10);
                if (isNaN(parsed)) {
                    return;
                }

                const projectCode = (letterData.project_code && String(letterData.project_code).trim()) ?
                    String(letterData.project_code).trim() :
                    '000H';
                const now = new Date();
                const roman = romanMap[now.getMonth() + 1] || '';
                const year = now.getFullYear();
                const regNo = pad4(parsed) + '/HCS-' + projectCode + '/RCR/' + roman + '/' + year;

                $preview.val(regNo).removeClass('alert-warning').addClass('alert-success');
            }

            function loadRooms(projectId, selectedId) {
                const $room = $('#meeting_room_id');
                if ($room.hasClass('select2-hidden-accessible')) {
                    $room.select2('destroy');
                }
                $room.html('<option value="">Loading...</option>');
                if (!projectId) {
                    $room.html('<option value="">— Select project first —</option>');
                    initSelect2($room);
                    return;
                }
                $.get("{{ route('meeting-rooms.by-project') }}", {
                    project_id: projectId
                }, function(rooms) {
                    let html = '<option value="">— Select —</option>';
                    rooms.forEach(function(r) {
                        const sel = selectedId && selectedId == r.id ? 'selected' : '';
                        html += '<option value="' + r.id + '" data-facilities="' + $('<div>').text(r
                            .facilities || '').html() + '" ' + sel + '>' + r.room_name + '</option>';
                    });
                    $room.html(html);
                    initSelect2($room);
                    if (selectedId) {
                        $room.val(selectedId).trigger('change');
                    }
                });
            }

            $('#project_id, #department_id, #meeting_room_id').each(function() {
                initSelect2($(this));
            });

            $('#project_id').on('change', function() {
                loadRooms($(this).val(), null);
            });

            $('#meeting_room_id').on('change', function() {
                const fac = $(this).find('option:selected').data('facilities');
                if (fac && !$('#facilities').val()) {
                    $('#facilities').val(fac);
                }
            });

            $(document).on('change', 'select[name="letter_number_id"]', updatePreview);
            $(document).on('letter-number-options:updated', updatePreview);

            const initialProject = $('#project_id').val();
            const initialRoom = @json(old('meeting_room_id', $doc->meeting_room_id ?? null));
            if (initialProject) {
                loadRooms(initialProject, initialRoom);
            }
            if (!isEdit) {
                updatePreview();
            }
        })();
    </script>
@endsection
