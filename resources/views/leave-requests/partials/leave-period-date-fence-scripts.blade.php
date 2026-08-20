            function selectedLeaveCategory() {
                return String($('#leave_type_id option:selected').data('category') || '').toLowerCase();
            }

            function leaveTypeUsesDateFence(category) {
                const cat = (category || selectedLeaveCategory()).toLowerCase();
                return cat === 'annual' || cat === 'lsl';
            }

            function toggleLeavePeriodField(category) {
                const cat = (category || selectedLeaveCategory()).toLowerCase();
                if (cat === 'paid' || cat === 'unpaid') {
                    $('#leave_period_group').hide();
                } else {
                    $('#leave_period_group').show();
                }
            }

            function applyPeriodFromSelectedLeaveType() {
                const $option = $('#leave_type_id option:selected');
                const periodStart = $option.data('period-start');
                const periodEnd = $option.data('period-end');

                if (!periodStart || !periodEnd) {
                    return false;
                }

                const startMoment = moment(periodStart);
                const endMoment = moment(periodEnd);
                if (!startMoment.isValid() || !endMoment.isValid()) {
                    return false;
                }

                currentEntitlementPeriod.start = startMoment;
                currentEntitlementPeriod.end = endMoment;

                const periodDisplay = $option.data('period-display')
                    || (startMoment.format('DD MMM YYYY') + ' - ' + endMoment.format('DD MMM YYYY'));
                $('#leave_period').val(periodDisplay);

                return true;
            }

            function readPreservedLeaveRange() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();
                const startMoment = startDate ? moment(startDate, 'YYYY-MM-DD', true) : null;
                const endMoment = endDate ? moment(endDate, 'YYYY-MM-DD', true) : null;
                const hasValidRange = !!(startMoment && endMoment && startMoment.isValid() && endMoment.isValid());

                return { startMoment, endMoment, hasValidRange };
            }

            function restoreLeaveDateDisplay(range) {
                if (range && range.hasValidRange) {
                    $('#leave_date').val(
                        `${range.startMoment.format('DD/MM/YYYY')} - ${range.endMoment.format('DD/MM/YYYY')}`
                    );
                }
            }

            function applyLeavePeriodDateFence(baseConfig) {
                const range = readPreservedLeaveRange();

                if (range.hasValidRange) {
                    baseConfig.startDate = range.startMoment;
                    baseConfig.endDate = range.endMoment;
                }

                if (!leaveTypeUsesDateFence()) {
                    return baseConfig;
                }

                if (currentEntitlementPeriod.start && currentEntitlementPeriod.end) {
                    let minDate = currentEntitlementPeriod.start.clone();
                    let maxDate = currentEntitlementPeriod.end.clone();
                    if (range.hasValidRange) {
                        if (range.startMoment.isBefore(minDate, 'day')) {
                            minDate = range.startMoment.clone();
                        }
                        if (range.endMoment.isAfter(maxDate, 'day')) {
                            maxDate = range.endMoment.clone();
                        }
                    }
                    baseConfig.minDate = minDate;
                    baseConfig.maxDate = maxDate;
                }

                return baseConfig;
            }

            function showLeaveBalanceLink() {
                $('#leave_balance_link_wrap').removeClass('d-none');
            }

            function hideLeaveBalanceLink() {
                $('#leave_balance_link_wrap').addClass('d-none');
            }

            function applyLeavePeriodDateFenceForEdit(baseConfig, startMoment, endMoment, hasValidRange) {
                return applyLeavePeriodDateFence(baseConfig);
            }
