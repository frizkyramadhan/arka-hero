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
                        <label>Select Employee <span class="text-danger">*</span></label>
                        <select class="form-control select2bs4" name="administration_id" id="administration_id" required>
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
            <h3 class="card-title">PKWT Data</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>PKWT Type <span class="text-danger">*</span></label>
                        <select class="form-control" name="pkwt_type" required>
                            <option value="">- Select Type -</option>
                            <option value="PKWT I">PKWT I</option>
                            <option value="PKWT II">PKWT II</option>
                            <option value="PKWT III">PKWT III</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Duration <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="duration" placeholder="Example: 12 months" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Start Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="start_date" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>End Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" name="end_date" required>
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
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            var categories = @json($categories->keyBy('id'));

            function updateDynamicFields(categoryId) {
                var categoryCode = categories[categoryId] ? categories[categoryId].category_code : null;
                var dynamicFieldsContainer = $('#dynamic-fields');
                dynamicFieldsContainer.empty();

                if (categoryCode) {
                    if (['PKWT', 'CRTE', 'SKPK'].includes(categoryCode)) {
                        dynamicFieldsContainer.append($('#employee-template').html());
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
                    } else if (['A'].includes(categoryCode)) {
                        dynamicFieldsContainer.append($('#classification-template').html());
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
