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

            function applyLeavePeriodDateFence(baseConfig) {
                if (!leaveTypeUsesDateFence()) {
                    return baseConfig;
                }

                if (currentEntitlementPeriod.start && currentEntitlementPeriod.end) {
                    baseConfig.minDate = currentEntitlementPeriod.start;
                    baseConfig.maxDate = currentEntitlementPeriod.end;
                } else {
                    baseConfig.minDate = moment();
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
                if (!leaveTypeUsesDateFence()) {
                    return baseConfig;
                }

                if (currentEntitlementPeriod.start && currentEntitlementPeriod.end) {
                    let minDate = currentEntitlementPeriod.start.clone();
                    let maxDate = currentEntitlementPeriod.end.clone();
                    if (hasValidRange) {
                        if (startMoment.isBefore(minDate, 'day')) {
                            minDate = startMoment.clone();
                        }
                        if (endMoment.isAfter(maxDate, 'day')) {
                            maxDate = endMoment.clone();
                        }
                    }
                    baseConfig.minDate = minDate;
                    baseConfig.maxDate = maxDate;
                }

                return baseConfig;
            }
