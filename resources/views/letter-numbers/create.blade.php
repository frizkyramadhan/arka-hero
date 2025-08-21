@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Letter Number</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('letter-numbers.index') }}">Letter Administration</a>
                        </li>
                        <li class="breadcrumb-item active">Create Letter Number</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('letter-numbers.store') }}" method="POST" id="letter-number-form">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Basic Information</h3>
                            </div>
                            <div class="card-body">
                                <!-- Next Number Preview -->
                                <div id="form-next-number-preview" style="display: none;" class="alert alert-info mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="mb-1">
                                                <i class="fas fa-eye"></i> Next Number Preview
                                            </h6>
                                            <p class="mb-0">
                                                The next letter number for this category will be:
                                                <strong id="form-next-letter-number" class="text-warning"></strong>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <span class="badge badge-info">
                                                Sequence: <span id="form-next-sequence"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Letter Category <span class="text-danger">*</span></label>
                                            <select class="form-control select2bs4" name="letter_category_id"
                                                id="letter_category_id" required {{ $selectedCategory ? 'disabled' : '' }}>
                                                <option value="">- Select Category -</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        data-code="{{ $category->category_code }}"
                                                        {{ $selectedCategory && $selectedCategory->id === $category->id ? 'selected' : '' }}>
                                                        {{ $category->category_code }} - {{ $category->category_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($selectedCategory)
                                                <input type="hidden" name="letter_category_id"
                                                    value="{{ $selectedCategory->id }}">
                                            @endif
                                            @error('letter_category_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Letter Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="letter_date" id="letter_date"
                                                value="{{ old('letter_date', date('Y-m-d')) }}" required>
                                            @error('letter_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Subject</label>
                                            <select class="form-control select2bs4" name="subject_id" id="subject_id">
                                                <option value="">- Select Subject -</option>
                                                @foreach ($subjects as $subject)
                                                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Custom Subject</label>
                                            <input type="text" class="form-control" name="custom_subject"
                                                id="custom_subject" value="{{ old('custom_subject') }}"
                                                placeholder="Fill if not available in dropdown">
                                            @error('custom_subject')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Destination/Address/Project</label>
                                            <input type="text" class="form-control" name="destination" id="destination"
                                                value="{{ old('destination') }}" placeholder="Letter destination">
                                            @error('destination')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Additional remarks">{{ old('remarks') }}</textarea>
                                            @error('remarks')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic Fields based on category -->
                        <div id="dynamic-fields"></div>

                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Additional Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Information</h5>
                                    <ul class="mb-0">
                                        <li>Letter number will be auto-generated</li>
                                        <li>Format: [CATEGORY][SEQUENCE]</li>
                                        <li>Initial status: <strong>Reserved</strong></li>
                                    </ul>
                                </div>

                                <div id="category-info" style="display: none;">
                                    <div class="alert alert-warning">
                                        <h6><i class="icon fas fa-exclamation-triangle"></i> Category Information</h6>
                                        <p id="category-description" class="mb-0"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save"></i> Save Letter Number
                                </button>
                                <a href="{{ route('letter-numbers.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Templates untuk Dynamic Fields -->
    <script type="text/template" id="employee-template">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Employee Data</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Select Employee</label>
                        <select class="form-control select2bs4" name="administration_id" id="administration_id">
                            <option value="">- Select Employee -</option>
                            @foreach($administrations as $admin)
                                <option value="{{ $admin->id }}"
                                        data-nik="{{ $admin->nik }}"
                                        data-employee-name="{{ $admin->employee->fullname ?? '' }}"
                                        data-project-name="{{ $admin->project->project_name ?? '' }}">
                                    {{ $admin->nik }} - {{ $admin->employee->fullname ?? 'N/A' }}
                                    ({{ $admin->project->project_name ?? 'No Project' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>NIK</label>
                        <input type="text" class="form-control" id="display_nik" readonly>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Project</label>
                        <input type="text" class="form-control" id="display_project" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

    <script type="text/template" id="pkwt-template">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Agreement Data</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Type</label>
                        <select class="form-control" name="pkwt_type">
                            <option value="">- Select Type -</option>
                            <option value="PKWT">PKWT</option>
                            <option value="PKWTT">PKWTT</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" class="form-control" name="duration" placeholder="Example: 12 months">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date">
                    </div>
                </div>
            </div>
        </div>
    </div>
</script>

    <script type="text/template" id="par-template">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PAR Data</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>PAR Type <span class="text-danger">*</span></label>
                <select class="form-control" name="par_type" required>
                    <option value="">- Select Type -</option>
                    <option value="new hire">New Hire</option>
                    <option value="promosi">Promosi</option>
                    <option value="mutasi">Mutasi</option>
                    <option value="demosi">Demosi</option>
                </select>
            </div>
        </div>
    </div>
</script>

    <script type="text/template" id="fr-template">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ticket Classification Data</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Ticket Classification <span class="text-danger">*</span></label>
                <select class="form-control" name="ticket_classification" required>
                    <option value="">- Select Type -</option>
                    <option value="Pesawat">Pesawat</option>
                    <option value="Kereta Api">Kereta Api</option>
                    <option value="Bus">Bus</option>
                </select>
            </div>
        </div>
    </div>
</script>

    <script type="text/template" id="classification-template">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Letter Classification</h3>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label>Classification</label>
                <select class="form-control" name="classification">
                    <option value="">- Select Classification -</option>
                    <option value="Umum">Umum</option>
                    <option value="Lembaga Pendidikan">Lembaga Pendidikan</option>
                    <option value="Pemerintah">Pemerintah</option>
                </select>
            </div>
        </div>
    </div>
</script>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .info-box {
            min-height: 80px;
            box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
            border-radius: 0.25rem;
            background-color: #fff;
            display: flex;
            margin-bottom: 1rem;
            position: relative;
        }

        .info-box-icon {
            border-radius: 0.25rem 0 0 0.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.875rem;
            font-weight: 300;
            text-align: center;
            width: 70px;
            color: #fff;
        }

        .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            height: auto;
            min-height: 80px;
            flex: 1;
            padding: 0 10px;
        }

        .info-box-text {
            display: block;
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #6c757d;
        }

        .info-box-number {
            display: block;
            font-weight: 700;
            font-size: 18px;
        }

        .progress {
            height: 3px;
            margin: 5px 0;
        }

        .progress-description {
            display: block;
            font-size: 12px;
            color: #6c757d;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .text-primary {
            color: #007bff !important;
        }

        .text-info {
            color: #17a2b8 !important;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .alert-secondary {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            color: #495057;
        }

        .text-success {
            color: #28a745 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .badge-info {
            background-color: #17a2b8;
            color: #fff;
        }

        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        .ml-2 {
            margin-left: 0.5rem !important;
        }

        .badge-light {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .badge-sm {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }

        .mr-1 {
            margin-right: 0.25rem !important;
        }

        .mt-2 {
            margin-top: 0.5rem !important;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-success {
            background-color: #28a745;
            color: #fff;
        }

        .badge-secondary {
            background-color: #6c757d;
            color: #fff;
        }

        .text-dark {
            color: #343a40 !important;
        }

        .mt-1 {
            margin-top: 0.25rem !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            var categories = @json($categories->keyBy('id'));
            var estimatedNextNumbers = @json($estimatedNextNumbers);

            function updateDynamicFields(categoryId) {
                var categoryCode = categories[categoryId] ? categories[categoryId].category_code : null;
                var dynamicFieldsContainer = $('#dynamic-fields');
                dynamicFieldsContainer.empty();

                if (categoryCode) {
                    if (categoryCode === 'PKWT') {
                        dynamicFieldsContainer.append($('#pkwt-template').html());
                    } else if (categoryCode === 'PAR') {
                        dynamicFieldsContainer.append($('#par-template').html());
                    } else if (categoryCode === 'FR') {
                        dynamicFieldsContainer.append($('#fr-template').html());
                    } else if (['A'].includes(categoryCode)) {
                        dynamicFieldsContainer.append($('#classification-template').html());
                    }
                    // Employee selection for PKWT, PAR, CRTE - nullable (not required) for manual processes
                    if (['PKWT', 'PAR', 'CRTE', 'SKPK'].includes(categoryCode)) {
                        dynamicFieldsContainer.append($('#employee-template').html());
                        $('#administration_id').select2({
                            theme: 'bootstrap4'
                        });
                    }
                }
            }

            function updateCategoryInfo(categoryId) {
                var category = categories[categoryId];
                if (category && category.description) {
                    $('#category-description').text(category.description);
                    $('#category-info').show();
                } else {
                    $('#category-info').hide();
                }
            }

            function updateNextNumberInfo(categoryId) {
                var estimate = estimatedNextNumbers[categoryId];
                if (estimate) {
                    // Update sidebar info
                    $('#next-letter-number').text(estimate.next_letter_number);
                    $('#next-sequence').text(estimate.next_sequence);
                    $('#next-number-info').show();

                    // Update form preview
                    $('#form-next-letter-number').text(estimate.next_letter_number);
                    $('#form-next-sequence').text(estimate.next_sequence);
                    $('#form-next-number-preview').show();
                } else {
                    $('#next-number-info').hide();
                    $('#form-next-number-preview').hide();
                }
            }

            function updateSubjects(categoryId) {
                var subjectSelect = $('#subject_id');
                subjectSelect.empty().append('<option value="">- Select Subject -</option>');

                if (categoryId) {
                    var url = "{{ route('api.letter-subjects.by-category', ['categoryId' => ':id']) }}";
                    url = url.replace(':id', categoryId);

                    $.get(url, function(data) {
                        if (data) {
                            data.forEach(function(subject) {
                                subjectSelect.append($('<option>', {
                                    value: subject.id,
                                    text: subject.subject_name
                                }));
                            });
                        }
                    });
                }
            }

            $('#letter_category_id').change(function() {
                var categoryId = $(this).val();
                updateDynamicFields(categoryId);
                updateCategoryInfo(categoryId);
                updateSubjects(categoryId);
                updateNextNumberInfo(categoryId); // Call this function here
            });

            // Initial call if a category is pre-selected
            if ($('#letter_category_id').val()) {
                $('#letter_category_id').trigger('change');
            }

            $('#dynamic-fields').on('change', '#administration_id', function() {
                var selectedOption = $(this).find('option:selected');
                $('#display_nik').val(selectedOption.data('nik'));
                $('#display_project').val(selectedOption.data('project-name'));
            });
        });
    </script>
@endsection
