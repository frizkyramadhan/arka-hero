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
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Project <span class="text-danger">*</span></label>
                                            <select class="form-control select2bs4" name="project_id" id="project_id"
                                                required>
                                                <option value="">- Select Project -</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('project_id', $letterNumber->project_id) == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Each project has its own sequence number.
                                                Required for generating letter numbers.
                                            </small>
                                            @error('project_id')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

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
                                            <input type="hidden" name="letter_category_id" id="letter_category_id"
                                                value="{{ $letterNumber->letter_category_id }}">
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
                        <label>Select Employee</label>
                        <select class="form-control select2bs4" name="administration_id" id="administration_id">
                            <option value="">- Select Employee -</option>
                            @foreach($administrations as $admin)
                                <option value="{{ $admin->id }}"
                                        data-nik="{{ $admin->nik }}"
                                        data-employee-name="{{ $admin->employee->fullname ?? '' }}"
                                        data-project-id="{{ $admin->project_id ?? '' }}"
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
            <h3 class="card-title">Agreement Data</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Type</label>
                        <select class="form-control" name="pkwt_type">
                            <option value="">- Select Type -</option>
                            <option value="PKWT" {{ old('pkwt_type', $letterNumber->pkwt_type) == 'PKWT' ? 'selected' : '' }}>PKWT</option>
                            <option value="PKWTT" {{ old('pkwt_type', $letterNumber->pkwt_type) == 'PKWTT' ? 'selected' : '' }}>PKWTT</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Duration</label>
                        <input type="text" class="form-control" name="duration"
                               value="{{ old('duration', $letterNumber->duration) }}"
                               placeholder="Example: 12 months">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" class="form-control" name="start_date"
                               value="{{ old('start_date', $letterNumber->start_date ? $letterNumber->start_date->format('Y-m-d') : '') }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" class="form-control" name="end_date"
                               value="{{ old('end_date', $letterNumber->end_date ? $letterNumber->end_date->format('Y-m-d') : '') }}">
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
                    <option value="Pesawat" {{ old('ticket_classification', $letterNumber->ticket_classification) == 'Pesawat' ? 'selected' : '' }}>Pesawat</option>
                    <option value="Kereta Api" {{ old('ticket_classification', $letterNumber->ticket_classification) == 'Kereta Api' ? 'selected' : '' }}>Kereta Api</option>
                    <option value="Bus" {{ old('ticket_classification', $letterNumber->ticket_classification) == 'Bus' ? 'selected' : '' }}>Bus</option>
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
        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // Initial dynamic fields based on existing category
            var categoryId = $('#letter_category_id').val();
            if (categoryId) {
                updateDynamicFields(categoryId);
                updateCategoryInfo(categoryId);
            }

            // Employee dropdown change handler
            $('#dynamic-fields').on('change', '#administration_id', function() {
                var selectedOption = $(this).find('option:selected');
                var categoryCode = getCategoryCodeById($('#letter_category_id').val());
                $('#display_nik').val(selectedOption.data('nik'));
                $('#display_project').val(selectedOption.data('project-name'));

                // CRITICAL: For categories with employee-template (PKWT, PAR, CRTE, SKPK),
                // project_id MUST come from select project, NOT from employee
                // Do NOT change project_id when selecting employee for these categories
                // For other categories, auto-set project_id from employee's project
                var categoriesWithEmployeeTemplate = ['PKWT', 'PAR', 'CRTE', 'SKPK'];
                if (selectedOption.data('project-id') && !categoriesWithEmployeeTemplate.includes(
                        categoryCode)) {
                    $('#project_id').val(selectedOption.data('project-id')).trigger('change');
                }
                // For categories with employee-template, project_id remains unchanged (from select project dropdown)
            });
            // Trigger change on load if an employee is selected (but won't change project_id for categories with employee-template)
            if ($('#administration_id').val()) {
                $('#administration_id').trigger('change');
            }

        });

        function updateDynamicFields(categoryId) {
            var categoryCode = getCategoryCodeById(categoryId);
            var dynamicFieldsContainer = $('#dynamic-fields');
            dynamicFieldsContainer.empty(); // Kosongkan field

            // Tampilkan field berdasarkan kategori
            // Employee selection for PKWT, PAR, CRTE - nullable (not required) for manual processes
            if (categoryCode === 'PKWT' || categoryCode === 'PAR' || categoryCode === 'CRTE') {
                dynamicFieldsContainer.append($('#employee-template').html());
                // Inisialisasi Select2 untuk field employee
                $('#administration_id').select2({
                    theme: 'bootstrap4'
                });
            }
            if (categoryCode === 'PKWT') {
                dynamicFieldsContainer.append($('#pkwt-template').html());
            } else if (categoryCode === 'PAR') {
                dynamicFieldsContainer.append($('#par-template').html());
            } else if (categoryCode === 'FR') {
                dynamicFieldsContainer.append($('#fr-template').html());
            } else if (['A', 'B'].includes(categoryCode)) {
                dynamicFieldsContainer.append($('#classification-template').html());
            }
        }

        function updateCategoryInfo(categoryId) {
            // Anda mungkin perlu AJAX call untuk mendapatkan deskripsi kategori
            // Untuk sekarang, kita bisa gunakan data yang sudah ada jika memungkinkan
            var category = categories.find(c => c.id == categoryId);
            if (category && category.description) {
                $('#category-description').text(category.description);
                $('#category-info').show();
            } else {
                $('#category-info').hide();
            }
        }

        function getCategoryCodeById(categoryId) {
            var category = categories.find(c => c.id == categoryId);
            return category ? category.category_code : null;
        }

        // Definisikan variabel categories dari data blade
        var categories = @json($categories);
    </script>
@endsection
