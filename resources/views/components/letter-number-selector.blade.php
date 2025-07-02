<div class="card card-info card-outline elevation-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-alt mr-2"></i>
            <strong>Letter Number Integration</strong>
        </h3>
    </div>
    <div class="card-body">
        <!-- Number Option Selection -->
        <div class="form-group">
            <label class="font-weight-bold">Number Option</label>
            <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="existing_number" name="number_option"
                    value="existing" checked>
                <label class="custom-control-label" for="existing_number">
                    <i class="fas fa-list mr-1"></i> Use Existing Reserved Number
                </label>
            </div>
            <div class="custom-control custom-radio">
                <input type="radio" class="custom-control-input" id="new_number" name="number_option" value="new">
                <label class="custom-control-label" for="new_number">
                    <i class="fas fa-plus mr-1"></i> Request New Number
                </label>
            </div>
        </div>

        <!-- Existing Number Selection -->
        <div id="existing_number_section">
            <div class="form-group">
                <label for="letter_number_id">Available Numbers <span class="text-danger">*</span></label>
                <select class="form-control select2" name="letter_number_id" id="letter_number_id" style="width: 100%;">
                    <option value="">Select Number</option>
                    @foreach ($availableNumbers ?? [] as $number)
                        <option value="{{ $number->id }}" data-number="{{ $number->letter_number }}">
                            {{ $number->letter_number }} - {{ Str::limit($number->purpose ?? 'No purpose', 50) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-info" id="refresh_numbers">
                    <i class="fas fa-sync-alt"></i> Refresh Numbers
                </button>
                <a href="{{ route('letter-numbers.create') }}?category={{ $category ?? 'B' }}"
                    class="btn btn-sm btn-outline-success" target="_blank">
                    <i class="fas fa-external-link-alt"></i> Create New in Letter Admin
                </a>
            </div>
        </div>

        <!-- New Number Request -->
        <div id="new_number_section" style="display: none;">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="letter_category">Category <span class="text-danger">*</span></label>
                        <select class="form-control" name="letter_category" id="letter_category">
                            <option value="A" {{ ($category ?? 'B') == 'A' ? 'selected' : '' }}>A - External
                                Letters</option>
                            <option value="B" {{ ($category ?? 'B') == 'B' ? 'selected' : '' }}>B - Internal
                                Letters</option>
                            <option value="PKWT" {{ ($category ?? 'B') == 'PKWT' ? 'selected' : '' }}>PKWT -
                                Employment Contract</option>
                            <option value="PAR" {{ ($category ?? 'B') == 'PAR' ? 'selected' : '' }}>PAR - Personal
                                Action Request</option>
                            <option value="CRTE" {{ ($category ?? 'B') == 'CRTE' ? 'selected' : '' }}>CRTE - Work
                                Experience Letter</option>
                            <option value="SKPK" {{ ($category ?? 'B') == 'SKPK' ? 'selected' : '' }}>SKPK - Work
                                Experience Certificate</option>
                            <option value="MEMO" {{ ($category ?? 'B') == 'MEMO' ? 'selected' : '' }}>MEMO - Internal
                                Memo</option>
                            <option value="FPTK" {{ ($category ?? 'B') == 'FPTK' ? 'selected' : '' }}>FPTK -
                                Workforce Request Form</option>
                            <option value="FR" {{ ($category ?? 'B') == 'FR' ? 'selected' : '' }}>FR - Ticket
                                Request Form</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="letter_subject">Subject</label>
                        <input type="text" class="form-control" name="letter_subject" id="letter_subject"
                            placeholder="Enter letter subject" value="{{ $defaultSubject ?? '' }}">
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                <div class="d-flex">
                    <i class="fas fa-info-circle mr-2 mt-1"></i>
                    <div>
                        <strong>Auto-populate from document:</strong><br>
                        Subject, employee data, dates, and other details will be automatically filled from this
                        document.
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Number Display -->
        <div id="selected_number_display" class="alert alert-success" style="display: none;">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <div>
                    <strong>Selected Number:</strong>
                    <span id="display_number_text"></span>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary ml-auto" id="change_number">
                    <i class="fas fa-edit"></i> Change
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#letter_number_id').select2({
            theme: 'bootstrap4',
            placeholder: 'Select Number',
            allowClear: true
        });

        // Handle number option change
        $('input[name="number_option"]').change(function() {
            if ($(this).val() === 'existing') {
                $('#existing_number_section').show();
                $('#new_number_section').hide();
                $('#letter_number_id').prop('required', true);
            } else {
                $('#existing_number_section').hide();
                $('#new_number_section').show();
                $('#letter_number_id').prop('required', false);
            }
            $('#selected_number_display').hide();
        });

        // Handle number selection
        $('#letter_number_id').on('change', function() {
            const selectedText = $(this).find(':selected').data('number');
            if (selectedText) {
                $('#display_number_text').text(selectedText);
                $('#selected_number_display').show();
            } else {
                $('#selected_number_display').hide();
            }
        });

        // Handle change number button
        $('#change_number').click(function() {
            $('#selected_number_display').hide();
            $('#letter_number_id').select2('open');
        });

        // Handle refresh numbers
        $('#refresh_numbers').click(function() {
            const btn = $(this);
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin"></i> Loading...');

            refreshAvailableNumbers().finally(() => {
                btn.html(originalText);
            });
        });

        // Refresh available numbers function
        function refreshAvailableNumbers() {
            const categoryCode = $('#letter_category').val() || '{{ $category ?? 'B' }}';

            return $.get(`/hcssis/api/letter-numbers/available/${categoryCode}`)
                .done(function(data) {
                    const select = $('#letter_number_id');
                    select.empty().append('<option value="">Select Number</option>');

                    data.forEach(function(number) {
                        const purpose = number.purpose ? String(number.purpose).substring(0, 50) :
                            'No purpose';
                        select.append(`<option value="${number.id}" data-number="${number.letter_number}">
                        ${number.letter_number} - ${purpose}
                    </option>`);
                    });

                    select.trigger('change');

                    if (data.length === 0) {
                        select.append('<option value="" disabled>No available numbers found</option>');
                    }
                })
                .fail(function(xhr) {
                    console.error('Failed to refresh numbers:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to refresh numbers. Please try again.'
                    });
                });
        }

        // Auto-refresh when category changes
        $('#letter_category').change(function() {
            if ($('input[name="number_option"]:checked').val() === 'existing') {
                refreshAvailableNumbers();
            }
        });
    });
</script>
