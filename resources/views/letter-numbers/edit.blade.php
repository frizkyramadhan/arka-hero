@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Letter Number</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('letter-numbers.index') }}">Letter Administration</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('letter-numbers.update', $letterNumber->id) }}" method="POST" id="letter-number-form">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Basic Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Letter Number</label>
                                            <input type="text" class="form-control"
                                                value="{{ $letterNumber->letter_number }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Letter Category</label>
                                            <input type="text" class="form-control"
                                                value="{{ $letterNumber->category->category_code }} - {{ $letterNumber->category->category_name }}"
                                                readonly>
                                            <input type="hidden" name="category_code" id="category_code"
                                                value="{{ $letterNumber->category_code }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Letter Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="letter_date" id="letter_date"
                                                value="{{ old('letter_date', $letterNumber->letter_date ? $letterNumber->letter_date->format('Y-m-d') : '') }}"
                                                required>
                                            @error('letter_date')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Status</label>
                                            <input type="text" class="form-control"
                                                value="{{ ucfirst($letterNumber->status) }}" readonly>
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
                                                    <option value="{{ $subject->id }}"
                                                        {{ old('subject_id', $letterNumber->subject_id) == $subject->id ? 'selected' : '' }}>
                                                        {{ $subject->subject_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Custom Subject</label>
                                            <input type="text" class="form-control" name="custom_subject"
                                                id="custom_subject"
                                                value="{{ old('custom_subject', $letterNumber->custom_subject) }}"
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
                                            <label>Destination/Address</label>
                                            <input type="text" class="form-control" name="destination" id="destination"
                                                value="{{ old('destination', $letterNumber->destination) }}"
                                                placeholder="Letter destination">
                                            @error('destination')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Remarks</label>
                                            <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Additional remarks">{{ old('remarks', $letterNumber->remarks) }}</textarea>
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
                                <h3 class="card-title">Letter Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Information</h5>
                                    <ul class="mb-0">
                                        <li>Letter Number: <strong>{{ $letterNumber->letter_number }}</strong></li>
                                        <li>Category: <strong>{{ $letterNumber->category->category_code }}</strong></li>
                                        <li>Status: <strong>{{ ucfirst($letterNumber->status) }}</strong></li>
                                        <li>Created: {{ $letterNumber->created_at->format('d M Y H:i') }}</li>
                                    </ul>
                                </div>

                                <div id="category-info">
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
                                    <i class="fas fa-save"></i> Update Letter Number
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
                        <label>Select Employee <span class="text-danger">*</span></label>
                        <select class="form-control select2bs4" name="administration_id" id="administration_id" required>
                            <option value="">- Select Employee -</option>
                            @foreach($administrations as $admin)
                                <option value="{{ $admin->id }}"
                                        data-nik="{{ $admin->nik }}"
                                        data-employee-name="{{ $admin->employee->fullname ?? '' }}"
                                        data-project-name="{{ $admin->project->project_name ?? '' }}"
                                        {{ old('administration_id', $letterNumber->administration_id) == $admin->id ? 'selected' : '' }}>
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
            <h3 class="card-title">PKWT Data</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>PKWT Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="pkwt_type" required>
                            <option value="">- Select Type -</option>
                            <option value="PKWT I" {{ old('pkwt_type', $letterNumber->pkwt_type) == 'PKWT I' ? 'selected' : '' }}>PKWT I</option>
                            <option value="PKWT II" {{ old('pkwt_type', $letterNumber->pkwt_type) == 'PKWT II' ? 'selected' : '' }}>PKWT II</option>
                            <option value="PKWT III" {{ old('pkwt_type', $letterNumber->pkwt_type) == 'PKWT III' ? 'selected' : '' }}>PKWT III</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Duration <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="duration"
                               value="{{ old('duration', $letterNumber->duration) }}"
                               placeholder="Example: 12 months" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date"
                               value="{{ old('start_date', $letterNumber->start_date ? $letterNumber->start_date->format('Y-m-d') : '') }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date"
                               value="{{ old('end_date', $letterNumber->end_date ? $letterNumber->end_date->format('Y-m-d') : '') }}" required>
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
                    <option value="new hire" {{ old('par_type', $letterNumber->par_type) == 'new hire' ? 'selected' : '' }}>New Hire</option>
                    <option value="promosi" {{ old('par_type', $letterNumber->par_type) == 'promosi' ? 'selected' : '' }}>Promosi</option>
                    <option value="mutasi" {{ old('par_type', $letterNumber->par_type) == 'mutasi' ? 'selected' : '' }}>Mutasi</option>
                    <option value="demosi" {{ old('par_type', $letterNumber->par_type) == 'demosi' ? 'selected' : '' }}>Demosi</option>
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
                    <option value="Umum" {{ old('classification', $letterNumber->classification) == 'Umum' ? 'selected' : '' }}>Umum</option>
                    <option value="Lembaga Pendidikan" {{ old('classification', $letterNumber->classification) == 'Lembaga Pendidikan' ? 'selected' : '' }}>Lembaga Pendidikan</option>
                    <option value="Pemerintah" {{ old('classification', $letterNumber->classification) == 'Pemerintah' ? 'selected' : '' }}>Pemerintah</option>
                </select>
            </div>
        </div>
    </div>
</script>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    <script>
        $(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // Category definitions
            var categoryInfo = {
                'A': {
                    description: 'External Letter - for communication with external parties',
                    needsEmployee: false,
                    needsClassification: true,
                    template: 'classification'
                },
                'B': {
                    description: 'Internal Letter - for internal company communication',
                    needsEmployee: false,
                    needsClassification: false,
                    template: null
                },
                'PKWT': {
                    description: 'Fixed-term Employment Contract - requires complete employee data',
                    needsEmployee: true,
                    needsClassification: false,
                    template: 'pkwt'
                },
                'PAR': {
                    description: 'Personal Action Request - for employee status changes',
                    needsEmployee: true,
                    needsClassification: false,
                    template: 'par'
                },
                'CRTE': {
                    description: 'Certificate of Employment - work experience certificate',
                    needsEmployee: true,
                    needsClassification: false,
                    template: 'employee'
                },
                'SKPK': {
                    description: 'Work Experience Certificate',
                    needsEmployee: true,
                    needsClassification: false,
                    template: 'employee'
                },
                'MEMO': {
                    description: 'Internal Memo - for brief internal communication',
                    needsEmployee: false,
                    needsClassification: false,
                    template: null
                },
                'FPTK': {
                    description: 'Workforce Request Form - for recruitment',
                    needsEmployee: false,
                    needsClassification: false,
                    template: null
                },
                'FR': {
                    description: 'Ticket Request Form - for travel ticket requests',
                    needsEmployee: false,
                    needsClassification: false,
                    template: null
                }
            };

            // Load dynamic fields based on current category
            function loadDynamicFields() {
                var categoryCode = $('#category_code').val();
                var dynamicFields = $('#dynamic-fields');

                // Clear existing dynamic fields
                dynamicFields.empty();

                if (categoryCode && categoryInfo[categoryCode]) {
                    var info = categoryInfo[categoryCode];

                    // Show category info
                    $('#category-description').text(info.description);
                    $('#category-info').show();

                    // Add employee fields if needed
                    if (info.needsEmployee) {
                        var employeeTemplate = $('#employee-template').html();
                        dynamicFields.append(employeeTemplate);
                    }

                    // Add specific template
                    if (info.template) {
                        var template = $('#' + info.template + '-template').html();
                        dynamicFields.append(template);
                    }

                    // Re-initialize Select2 for new elements
                    $('.select2bs4').select2({
                        theme: 'bootstrap4'
                    });

                    // Set initial employee data if exists
                    setTimeout(function() {
                        if ($('#administration_id').length) {
                            $('#administration_id').trigger('change');
                        }
                    }, 100);
                } else {
                    $('#category-info').hide();
                }
            }

            // Handle employee selection
            $(document).on('change', '#administration_id', function() {
                var selectedOption = $(this).find('option:selected');
                if (selectedOption.val()) {
                    $('#display_nik').val(selectedOption.data('nik'));
                    $('#display_project').val(selectedOption.data('project-name'));
                } else {
                    $('#display_nik').val('');
                    $('#display_project').val('');
                }
            });

            // Load dynamic fields on page load
            loadDynamicFields();

            // Form validation
            $('#letter-number-form').submit(function(e) {
                var categoryCode = $('#category_code').val();
                var isValid = true;
                var errorMessage = '';

                // Basic validation
                if (!$('#letter_date').val()) {
                    isValid = false;
                    errorMessage = 'Letter date is required';
                }

                // Category-specific validation
                if (categoryCode && categoryInfo[categoryCode] && categoryInfo[categoryCode]
                    .needsEmployee) {
                    if (!$('#administration_id').val()) {
                        isValid = false;
                        errorMessage = 'Employee data must be selected for this category';
                    }
                }

                // PKWT specific validation
                if (categoryCode === 'PKWT') {
                    if (!$('select[name="pkwt_type"]').val()) {
                        isValid = false;
                        errorMessage = 'PKWT type is required';
                    }
                    if (!$('input[name="duration"]').val()) {
                        isValid = false;
                        errorMessage = 'Duration is required';
                    }
                    if (!$('input[name="start_date"]').val()) {
                        isValid = false;
                        errorMessage = 'Start date is required';
                    }
                    if (!$('input[name="end_date"]').val()) {
                        isValid = false;
                        errorMessage = 'End date is required';
                    }
                }

                // PAR specific validation
                if (categoryCode === 'PAR') {
                    if (!$('select[name="par_type"]').val()) {
                        isValid = false;
                        errorMessage = 'PAR type is required';
                    }
                }

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: errorMessage,
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        });
    </script>
@endsection
