            function showLeaveFormClientErrors(messages) {
                $('#leave-form-client-errors').remove();
                if (!messages.length) {
                    return;
                }

                const items = messages.map(function(message) {
                    return '<li>' + $('<div>').text(message).html() + '</li>';
                }).join('');

                const html = '<div class="alert alert-danger alert-dismissible fade show" id="leave-form-client-errors">' +
                    '<ul class="mb-0 pl-3">' + items + '</ul>' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span></button></div>';

                const $body = $('form.js-leave-request-form .card-primary .card-body').first();
                $body.prepend(html);

                const offset = $('#leave-form-client-errors').offset();
                if (offset) {
                    $('html, body').animate({ scrollTop: offset.top - 80 }, 200);
                }

                if (typeof toast_error === 'function') {
                    toast_error(messages[0]);
                }
            }

            function currentLslUsageMode() {
                if (typeof getLSLUsageMode === 'function') {
                    return getLSLUsageMode();
                }

                return String($('input[name="lsl_usage_mode"]:checked').val() || '');
            }

            $('form.js-leave-request-form').on('submit', function(e) {
                $('#leave_type_id').prop('disabled', false);

                const messages = [];
                const lslVisible = $('#lsl_flexible_section').is(':visible');
                const usageMode = currentLslUsageMode();
                const isCashoutOnly = lslVisible && usageMode === 'cashout_only';

                if (isCashoutOnly) {
                    $('#leave_date').prop('required', false);
                }

                if (!$('#leave_type_id').val()) {
                    messages.push('Please select a leave type.');
                }

                let totalDays = parseInt($('#total_days_hidden').val(), 10) || 0;
                if (lslVisible) {
                    const takenDays = parseInt($('#lsl_taken_days').val(), 10) || 0;
                    const cashoutDays = parseInt($('#lsl_cashout_days').val(), 10) || 0;
                    totalDays = isCashoutOnly ? cashoutDays : (takenDays + cashoutDays);
                } else {
                    totalDays = parseInt($('#total_days_input').val(), 10)
                        || parseInt($('#total_days_hidden').val(), 10)
                        || 0;
                }

                if (!isCashoutOnly && (!$('#start_date').val() || !$('#end_date').val())) {
                    messages.push('Please select a leave date range.');
                }

                if (!totalDays || totalDays <= 0) {
                    messages.push(isCashoutOnly
                        ? 'Cash out days must be greater than 0.'
                        : 'Total days must be greater than 0. Please select a date range or enter total days.');
                }

                const approverCount = $('input[name="manual_approvers[]"]').filter(function() {
                    return !$(this).prop('disabled') && String($(this).val() || '').trim() !== '';
                }).length;

                if (approverCount < 1) {
                    messages.push('Please select at least one approver.');
                }

                if (messages.length) {
                    e.preventDefault();
                    showLeaveFormClientErrors(messages);
                    return false;
                }

                $('#total_days_hidden').val(totalDays);
            });
