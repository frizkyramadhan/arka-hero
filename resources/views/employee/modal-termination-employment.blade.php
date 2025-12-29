<!-- Termination Modals for Employment History -->
@foreach ($administrations as $administration)
    <div class="modal fade text-left" id="modal-termination-employment-{{ $administration->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h4 class="modal-title">
                        <i class="fas fa-user-times mr-2"></i>Terminate Employment
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('administrations/' . $administration->id . '/terminate') }}" method="POST"
                    id="form-termination-{{ $administration->id }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="employee_id" value="{{ $administration->employee_id }}">
                    <input type="hidden" name="administration_id" value="{{ $administration->id }}">

                    <div class="modal-body">
                        <div class="card-body">
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                <strong>Warning:</strong> This will mark this employment history as inactive.
                            </div>

                            <!-- Termination Checklist -->
                            <div class="form-group">
                                <label class="form-label required-field mb-3">
                                    <i class="fas fa-clipboard-check mr-2"></i>Termination Checklist
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input class="custom-control-input termination-checklist" type="checkbox"
                                                id="exit_interview_{{ $administration->id }}" name="exit_interview"
                                                value="1">
                                            <label class="custom-control-label"
                                                for="exit_interview_{{ $administration->id }}">
                                                Exit Interview
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input class="custom-control-input termination-checklist" type="checkbox"
                                                id="clearance_accounting_{{ $administration->id }}"
                                                name="clearance_accounting" value="1">
                                            <label class="custom-control-label"
                                                for="clearance_accounting_{{ $administration->id }}">
                                                Payment Request Clearance
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input class="custom-control-input termination-checklist" type="checkbox"
                                                id="clearance_it_{{ $administration->id }}" name="clearance_it"
                                                value="1">
                                            <label class="custom-control-label"
                                                for="clearance_it_{{ $administration->id }}">
                                                IT Asset Clearance
                                            </label>
                                        </div>
                                        <div class="custom-control custom-checkbox mb-2">
                                            <input class="custom-control-input termination-checklist" type="checkbox"
                                                id="clearance_koperasi_{{ $administration->id }}"
                                                name="clearance_koperasi" value="1">
                                            <label class="custom-control-label"
                                                for="clearance_koperasi_{{ $administration->id }}">
                                                Koperasi Clearance
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-info mt-3 mb-0" id="checklist-info-{{ $administration->id }}">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span>Please complete all checklist items to proceed with termination.</span>
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Termination Form Fields -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termination_date_employment_{{ $administration->id }}"
                                            class="form-label required-field">Termination Date</label>
                                        <div class="input-group date">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span>
                                            </div>
                                            <input type="date"
                                                class="form-control @error('termination_date') is-invalid @enderror termination-form-field"
                                                id="termination_date_employment_{{ $administration->id }}"
                                                name="termination_date"
                                                value="{{ old('termination_date', $administration->termination_date) }}"
                                                disabled required>
                                        </div>
                                        @error('termination_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="termination_reason_employment_{{ $administration->id }}"
                                            class="form-label required-field">Termination Reason</label>
                                        <select name="termination_reason"
                                            id="termination_reason_employment_{{ $administration->id }}"
                                            class="form-control @error('termination_reason') is-invalid @enderror termination-form-field"
                                            style="width: 100%;" disabled required>
                                            <option value="">-Select Reason-</option>
                                            <option value="End of Contract"
                                                {{ old('termination_reason', $administration->termination_reason) == 'End of Contract' ? 'selected' : '' }}>
                                                End of Contract</option>
                                            <option value="End of Project"
                                                {{ old('termination_reason', $administration->termination_reason) == 'End of Project' ? 'selected' : '' }}>
                                                End of Project</option>
                                            <option value="Resign"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Resign' ? 'selected' : '' }}>
                                                Resign</option>
                                            <option value="Termination"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Termination' ? 'selected' : '' }}>
                                                Termination</option>
                                            <option value="Retired"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Retired' ? 'selected' : '' }}>
                                                Retired</option>
                                            <option value="Efficiency"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Efficiency' ? 'selected' : '' }}>
                                                Efficiency</option>
                                            <option value="Passed Away"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Passed Away' ? 'selected' : '' }}>
                                                Passed Away</option>
                                            <option value="Canceled"
                                                {{ old('termination_reason', $administration->termination_reason) == 'Canceled' ? 'selected' : '' }}>
                                                Canceled</option>
                                        </select>
                                        @error('termination_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="coe_no_employment_{{ $administration->id }}"
                                            class="form-label">Certificate of Employment</label>
                                        <input type="text"
                                            class="form-control @error('coe_no') is-invalid @enderror termination-form-field"
                                            id="coe_no_employment_{{ $administration->id }}" name="coe_no"
                                            value="{{ old('coe_no', $administration->coe_no) }}"
                                            placeholder="Enter certificate number" disabled>
                                        @error('coe_no')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" id="btn-terminate-{{ $administration->id }}"
                            disabled
                            onclick="return confirm('Are you sure you want to terminate this employment history?')">
                            <i class="fas fa-user-times mr-1"></i> Terminate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
    <script>
        $(document).ready(function() {
            // Function to check if all checklist items are checked
            function checkTerminationChecklist(administrationId) {
                const modal = $('#modal-termination-employment-' + administrationId);
                const checkboxes = modal.find('.termination-checklist');
                const allChecked = checkboxes.length === checkboxes.filter(':checked').length;

                const formFields = modal.find('.termination-form-field');
                const submitBtn = $('#btn-terminate-' + administrationId);
                const infoAlert = $('#checklist-info-' + administrationId);
                const selectField = $('#termination_reason_employment_' + administrationId);

                if (allChecked) {
                    // Enable form fields
                    formFields.prop('disabled', false);

                    // Enable Select2
                    if (selectField.length) {
                        if (selectField.hasClass('select2-hidden-accessible')) {
                            selectField.prop('disabled', false);
                            selectField.trigger('change.select2');
                        } else {
                            // Initialize Select2 if not already initialized
                            selectField.select2({
                                theme: 'bootstrap4',
                                width: '100%',
                                dropdownParent: modal
                            });
                        }
                    }

                    // Update info message
                    infoAlert.removeClass('alert-info').addClass('alert-success');
                    infoAlert.html(
                        '<i class="fas fa-check-circle mr-2"></i><span>All checklist items completed. You can now proceed with termination.</span>'
                    );

                    // Enable submit button
                    submitBtn.prop('disabled', false);
                } else {
                    // Disable form fields
                    formFields.prop('disabled', true);

                    // Disable Select2
                    if (selectField.length && selectField.hasClass('select2-hidden-accessible')) {
                        selectField.prop('disabled', true);
                        selectField.trigger('change.select2');
                    }

                    // Update info message
                    const checkedCount = checkboxes.filter(':checked').length;
                    const totalCount = checkboxes.length;
                    infoAlert.removeClass('alert-success').addClass('alert-info');
                    infoAlert.html(
                        '<i class="fas fa-info-circle mr-2"></i><span>Please complete all checklist items (' +
                        checkedCount + '/' + totalCount + ') to proceed with termination.</span>');

                    // Disable submit button
                    submitBtn.prop('disabled', true);
                }
            }

            // Handle checklist checkbox changes
            $(document).on('change', '.termination-checklist', function() {
                const checkbox = $(this);
                const administrationId = checkbox.closest('[id^="modal-termination-employment-"]')
                    .attr('id').replace('modal-termination-employment-', '');
                checkTerminationChecklist(administrationId);
            });

            // Validate form before submit
            $(document).on('submit', '[id^="form-termination-"]', function(e) {
                const form = $(this);
                const administrationId = form.attr('id').replace('form-termination-', '');
                const modal = $('#modal-termination-employment-' + administrationId);
                const checkboxes = modal.find('.termination-checklist');
                const allChecked = checkboxes.length === checkboxes.filter(':checked').length;

                if (!allChecked) {
                    e.preventDefault();
                    alert('Please complete all checklist items before submitting.');
                    return false;
                }
            });

            // Select2 will be initialized when modal is shown

            // Re-initialize Select2 when modal is shown
            $('[id^="modal-termination-employment-"]').on('shown.bs.modal', function() {
                const modalId = $(this).attr('id');
                const administrationId = modalId.replace('modal-termination-employment-', '');
                const selectId = '#termination_reason_employment_' + administrationId;
                const $modal = $(this);

                if ($(selectId).length) {
                    // Destroy existing Select2 if any
                    if ($(selectId).hasClass('select2-hidden-accessible')) {
                        $(selectId).select2('destroy');
                    }

                    // Initialize Select2
                    $(selectId).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $modal
                    });
                }

                // Initialize checklist state
                checkTerminationChecklist(administrationId);
            });
        });
    </script>
@endpush
