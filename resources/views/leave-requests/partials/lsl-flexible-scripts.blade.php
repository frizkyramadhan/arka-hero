            // ============================================================================
            // LSL FLEXIBLE — usage modes: leave_only | cashout_only | combined
            // Requires parent: calculateActiveDaysFromDateRange(), clearLSLValidation(), showLSLValidation()
            // ============================================================================

            initializeLSLFlexible();

            function getLSLUsageMode() {
                return $('input[name="lsl_usage_mode"]:checked').val() || 'leave_only';
            }

            function initializeLSLFlexible() {
                $('input[name="lsl_usage_mode"]').on('change', onLSLUsageModeChange);
                $('#lsl_cashout_days').on('input', onLSLCashoutDaysChange);
                $('#lsl_taken_days').on('input', onLSLTakenDaysChange);
            }

            function onLSLUsageModeChange() {
                applyLSLUsageMode();
                calculateLSLFlexible(false);
            }

            function onLSLCashoutDaysChange() {
                calculateLSLFlexible(false);
            }

            function onLSLTakenDaysChange() {
                calculateLSLFlexible(false);
            }

            function applyLSLUsageMode() {
                const mode = getLSLUsageMode();
                const $leaveDateRow = $('#leave_date_fields_row');
                const $leaveDate = $('#leave_date');
                const $backToWorkCol = $('#back_to_work_date_col');
                const $cashoutInput = $('#lsl_cashout_days');
                const $modeHelp = $('#lsl_mode_help');
                const $takenHelp = $('#lsl_taken_days_help');
                const $cashoutHelp = $('#lsl_cashout_help');

                if (mode === 'cashout_only') {
                    $leaveDateRow.hide();
                    $leaveDate.prop('required', false);
                    $backToWorkCol.hide();

                    $('#lsl_taken_days').prop('readonly', true);

                    $cashoutInput.prop('readonly', false).prop('disabled', false);

                    $modeHelp.text('Cash out only: leave dates are optional. Cash out days will be deducted from your LSL balance.');
                    $takenHelp.text('Not counted in this mode while cash-out-only is selected.');
                    $cashoutHelp.text('Enter the number of days to cash out.');
                } else if (mode === 'leave_only') {
                    $leaveDateRow.show();
                    $leaveDate.prop('required', true);
                    $backToWorkCol.show();

                    $('#lsl_taken_days').prop('readonly', false);

                    $cashoutInput.prop('readonly', true).prop('disabled', false);

                    $modeHelp.text('Take leave only: select leave dates. Cash out is not counted in this mode.');
                    $takenHelp.text('Calculated from date range by default. Can be edited manually.');
                    $cashoutHelp.text('Not counted in this mode (value kept until you change it).');
                } else {
                    $leaveDateRow.show();
                    $leaveDate.prop('required', true);
                    $backToWorkCol.show();

                    $('#lsl_taken_days').prop('readonly', false);

                    $cashoutInput.prop('readonly', false).prop('disabled', false);

                    $modeHelp.text('Combined: take leave and optionally cash out additional days.');
                    $takenHelp.text('Calculated from date range by default. Can be edited manually.');
                    $cashoutHelp.text('Additional days to cash out (enter 0 if none).');
                }
            }

            function calculateLSLFlexible(syncFromDates = false) {
                const mode = getLSLUsageMode();
                let takenDays = parseInt($('#lsl_taken_days').val(), 10) || 0;

                if (mode !== 'cashout_only' && syncFromDates) {
                    const calculatedDays = calculateActiveDaysFromDateRange();
                    if (calculatedDays > 0) {
                        $('#lsl_taken_days').val(calculatedDays);
                        takenDays = calculatedDays;
                    }
                }

                const cashoutDays = parseInt($('#lsl_cashout_days').val(), 10) || 0;
                const countedTakenDays = mode === 'cashout_only' ? 0 : takenDays;
                const countedCashoutDays = mode === 'leave_only' ? 0 : cashoutDays;
                const totalDays = countedTakenDays + countedCashoutDays;

                $('#lsl_total_days').val(totalDays);

                const $option = $('#leave_type_id option:selected');
                const remainingDays = parseInt($option.data('remaining'), 10) || 0;

                clearLSLValidation();

                if (typeof window.entitlementData !== 'undefined' && window.entitlementData.remaining_days !== undefined) {
                    const entitlementRemaining = window.entitlementData.remaining_days;
                    if (totalDays > entitlementRemaining) {
                        showLSLValidation(
                            `Total days (${totalDays}) exceeds remaining leave balance (${entitlementRemaining} days)`);
                        $('#lsl_cashout_days, #lsl_taken_days, #lsl_total_days').addClass('is-invalid');
                        $('#total_days_hidden').val(totalDays);
                        return;
                    }
                } else if (totalDays > remainingDays && remainingDays > 0) {
                    showLSLValidation(
                        `Total days (${totalDays}) exceeds remaining leave balance (${remainingDays} days)`);
                    $('#lsl_cashout_days, #lsl_taken_days, #lsl_total_days').addClass('is-invalid');
                    $('#total_days_hidden').val(totalDays);
                    return;
                }

                $('#lsl_cashout_days, #lsl_taken_days, #lsl_total_days').removeClass('is-invalid');
                $('#total_days_hidden').val(totalDays);
            }

            function toggleLSLFlexibleSection(show) {
                if (show) {
                    $('#lsl_flexible_section').slideDown();
                    $('#total_days_input').closest('.form-group').hide();
                    applyLSLUsageMode();
                    const mode = getLSLUsageMode();
                    calculateLSLFlexible(mode !== 'cashout_only');
                } else {
                    $('#lsl_flexible_section').slideUp();
                    $('#total_days_input').closest('.form-group').show();
                    $('#leave_date_fields_row').show();
                    $('#back_to_work_date_col').show();
                    $('#leave_date').prop('required', true);
                    $('#lsl_cashout_days').val(0).prop('readonly', false).prop('disabled', false);
                    $('#lsl_cashout_days').data('initial-value', 0);
                    $('#lsl_cashout_field_col').show();
                    $('#lsl_taken_days').val(0).prop('readonly', false);
                    $('#lsl_total_days').val(0);
                    $('input[name="lsl_usage_mode"][value="leave_only"]').prop('checked', true);
                    $('input[name="lsl_usage_mode"]').parent('label').removeClass('active');
                    $('input[name="lsl_usage_mode"][value="leave_only"]').parent('label').addClass('active');
                    clearLSLValidation();
                    calculateLSLFlexible(false);
                }
            }

            if ($('#lsl_flexible_section').is(':visible')) {
                applyLSLUsageMode();
                calculateLSLFlexible(false);
            }
