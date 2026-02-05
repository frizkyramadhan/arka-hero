{{--
    Letter Number Selector Component

    Reusable component for selecting existing reserved letter numbers

    Usage:
    @include('components.smart-letter-number-selector', [
        'categoryCode' => 'B',           // Required: Letter category (A, B, PKWT, etc.)
        'fieldName' => 'letter_number_id', // Optional: Field name (default: letter_number_id)
        'required' => true,              // Optional: Required field (default: false)
        'placeholder' => 'Select Number' // Optional: Placeholder text
    ])

    Examples:
    - Official Travel: categoryCode = 'B'
    - Internal Letter: categoryCode = 'A'
    - PKWT: categoryCode = 'PKWT'
--}}

@php
    $categoryCode = $categoryCode ?? 'B';
    $fieldName = $fieldName ?? 'letter_number_id';
    $required = $required ?? false;
    $placeholder = $placeholder ?? 'Select Letter Number';
    $selectedValue = old($fieldName) ?? ($selectedValue ?? null);

    // Component ID for JavaScript isolation
    $componentId = 'letter-selector-' . uniqid();
@endphp

<div id="{{ $componentId }}" class="letter-number-selector" data-category="{{ $categoryCode }}">
    <!-- Hidden input for number option, defaulted to existing -->
    <input type="hidden" name="number_option" value="existing">

    <!-- Letter Number Selection -->
    <div class="form-group">
        <div class="row">
            <div class="col-md-8">
                <label class="form-label mb-1">
                    <strong>Letter Number</strong>
                    @if ($required)
                        <span class="text-danger">*</span>
                    @endif
                </label>
                <select class="form-control letter-number-select @error($fieldName) is-invalid @enderror"
                    id="{{ $fieldName }}_{{ $componentId }}" name="{{ $fieldName }}"
                    {{ $required ? 'required' : '' }}>
                    <option value="">{{ $placeholder }}...</option>
                </select>
                @error($fieldName)
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">
                    Select from reserved letter numbers for category {{ $categoryCode }}
                </small>
            </div>
            <div class="col-md-4">
                <label class="form-label mb-1">&nbsp;</label>
                <div class="btn-group-vertical d-block">
                    <button type="button" class="btn btn-sm btn-outline-primary mb-1 refresh-btn">
                        <i class="fas fa-sync"></i> Refresh List
                    </button>
                    <a href="{{ route('letter-numbers.create', $categoryCode) }}" class="btn btn-sm btn-outline-success"
                        target="_blank">
                        <i class="fas fa-plus"></i> Create New
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Selection Status -->
    <div class="alert alert-info alert-sm py-2 mt-2 status-alert" style="display: none;">
        <i class="fas fa-info-circle"></i>
        <span class="status-message"></span>
    </div>
</div>

<!-- Self-contained JavaScript for this component instance -->
<script>
    // Wait for jQuery to be available
    (function() {
        function initWhenReady() {
            if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                // jQuery not loaded yet, wait and try again
                setTimeout(initWhenReady, 100);
                return;
            }

            // jQuery is available, proceed with initialization
            $(document).ready(function() {
                // Initialize component {{ $componentId }}
                initLetterNumberSelector();
            });
        }

        // Function to initialize letter number selector
        function initLetterNumberSelector() {
            const componentId = '{{ $componentId }}';
            const categoryCode = '{{ $categoryCode }}';
            const $component = $('#' + componentId);

            // Initialize Select2 for this component
            const $select = $component.find('.letter-number-select');
            $select.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: '{{ $placeholder }}'
            });

            // Event handlers
            setupEventHandlers();

            // Load available numbers on initialization
            loadAvailableNumbers();

            function setupEventHandlers() {
                // Letter number selection change
                $select.on('change', function() {
                    const selectedOption = $(this).find('option:selected');
                    if (selectedOption.val()) {
                        const info = `Selected: ${selectedOption.text()}`;
                        updateSelectionStatus('success', info);
                    } else {
                        updateSelectionStatus('warning', 'Please select a letter number');
                    }
                });

                // Refresh button click
                $component.find('.refresh-btn').on('click', function() {
                    loadAvailableNumbers();
                });
            }

            function loadAvailableNumbers() {
                const $btn = $component.find('.refresh-btn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

                updateSelectionStatus('info', 'Loading available letter numbers...');

                $.get("{{ route('api.letter-numbers.available', ['categoryCode' => ':categoryCode']) }}".replace(
                        ':categoryCode', categoryCode))
                    .done(function(response) {
                        console.log(response);
                        if (response.success) {
                            populateAvailableNumbers(response.data);
                            if (response.data.length > 0) {
                                updateSelectionStatus('success',
                                    `Found ${response.data.length} available letter numbers`);
                            } else {
                                updateSelectionStatus('warning', 'No available letter numbers found');
                            }
                        } else {
                            updateSelectionStatus('danger', 'Failed to load letter numbers');
                        }
                    })
                    .fail(function(xhr, status, error) {
                        console.error('Letter Number Selector Error:', error);
                        updateSelectionStatus('danger', 'Error loading letter numbers');
                    })
                    .always(function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Refresh List');
                    });
            }

            function populateAvailableNumbers(numbers) {
                const selectedValue = '{{ $selectedValue }}';
                $select.empty().append('<option value="">{{ $placeholder }}</option>');

                numbers.forEach(number => {
                    const letterDate = number.letter_date ? new Date(number.letter_date).toLocaleDateString(
                        'en-GB', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric'
                        }).replace(/ /g, '-') : 'No Date';
                    // Build display text with remarks
                    let displayText =
                        `${number.letter_number} - ${number.subject_name || 'No Subject'} (${letterDate})`;

                    // Add remarks if available (limit to 40 characters)
                    if (number.remarks && number.remarks.trim()) {
                        let remarks = number.remarks.trim();
                        if (remarks.length > 40) {
                            remarks = remarks.substring(0, 37) + '...';
                        }
                        displayText += ` - ${remarks}`;
                    }

                    const option = $('<option>')
                        .val(number.id)
                        .text(displayText)
                        .data('number', number)
                        .attr('title', number.remarks ? number.remarks : ''); // Full remarks in tooltip

                    // Pre-select if this is the old value
                    if (selectedValue && number.id == selectedValue) {
                        option.prop('selected', true);
                    }

                    $select.append(option);
                });

                // Trigger Select2 update and change event
                $select.trigger('change');

                // If there's a selected value, show status
                if (selectedValue) {
                    const selectedOption = $select.find('option:selected');
                    if (selectedOption.val()) {
                        const info = `Selected: ${selectedOption.text()}`;
                        updateSelectionStatus('success', info);
                    }
                }
            }

            function updateSelectionStatus(type, message) {
                const $statusAlert = $component.find('.status-alert');
                const $statusMessage = $component.find('.status-message');

                $statusAlert
                    .removeClass('alert-info alert-success alert-warning alert-danger')
                    .addClass(`alert-${type}`)
                    .show();

                $statusMessage.text(message);

                // Auto-hide success messages after 3 seconds
                if (type === 'success') {
                    setTimeout(() => {
                        $statusAlert.fadeOut();
                    }, 3000);
                }
            }
        }

        // Start checking for jQuery
        initWhenReady();
    })();
</script>

<!-- Component Styles -->
<style>
    .letter-number-selector .alert-sm {
        padding: 0.375rem 0.75rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .letter-number-selector .form-label {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .letter-number-selector .btn-group-vertical .btn {
        margin-bottom: 0.25rem;
    }
</style>
