@php
    $statusUrlTemplate = url('employee-disciplinaries/employees/__ID__/status');
    $terminateUrlTemplate = url('employee-disciplinaries/employees/__ID__/terminate');
    $criteriaOptionsUrl = route('disciplinary-criteria.options');
    $allTypeLabels = \App\Models\EmployeeDisciplinary::TYPE_LABELS;
    $scopeTypeKeys = array_keys($typeOptions);
@endphp
<style>
    .criteria-checkbox-list {
        max-height: 280px;
        overflow-y: auto;
        padding-left: 0.75rem !important;
        padding-right: 0.75rem !important;
    }

    .criteria-item {
        border-bottom: 1px solid #f0f0f0;
        padding: 0.65rem 0.5rem 0.65rem 1.75rem;
        position: relative;
    }

    .criteria-item:last-child {
        border-bottom: none;
    }

    .criteria-item:hover {
        background-color: #f8f9fa;
    }

    .criteria-item .custom-control-input {
        left: 0.5rem;
        z-index: 1;
    }

    .criteria-item .custom-control-label {
        width: 100%;
        cursor: pointer;
        padding-left: 0.5rem;
    }

    .criteria-item .custom-control-label::before,
    .criteria-item .custom-control-label::after {
        left: -1.25rem;
    }

    .criteria-item .criteria-code {
        font-weight: 600;
        margin-right: 0.35rem;
    }

    .criteria-item .criteria-title {
        display: block;
        white-space: normal;
        line-height: 1.35;
        color: #333;
    }

    .criteria-item .criteria-meta {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        margin-top: 0.15rem;
    }

    .criteria-selected-chip {
        display: inline-flex;
        align-items: center;
        max-width: 100%;
        margin: 0 0.35rem 0.35rem 0;
        padding: 0.25rem 0.5rem;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background: #fff;
        font-size: 0.85rem;
    }

    .criteria-selected-chip .chip-code {
        font-weight: 600;
        margin-right: 0.35rem;
        white-space: nowrap;
    }

    .criteria-selected-chip .chip-text {
        white-space: normal;
        line-height: 1.3;
    }
</style>
<script>
    (function() {
        const statusUrlTemplate = @json($statusUrlTemplate);
        const terminateUrlTemplate = @json($terminateUrlTemplate);
        const criteriaOptionsUrl = @json($criteriaOptionsUrl);
        const allTypeLabels = @json($allTypeLabels);
        const scopeTypeKeys = @json($scopeTypeKeys);
        const isEdit = @json(isset($record));

        let criteriaCache = [];
        let selectedIds = [];

        function monthsForType(type) {
            return (type === 'coaching' || type === 'counseling') ? 3 : 6;
        }

        function escapeHtml(text) {
            return $('<div>').text(text == null ? '' : String(text)).html();
        }

        function updateSelectedSummary() {
            const selected = criteriaCache.filter(function(item) {
                return selectedIds.indexOf(String(item.id)) !== -1;
            });
            const $summary = $('#criteria-selected-summary');
            const $badges = $('#criteria-selected-badges');
            $badges.empty();

            if (!selected.length) {
                $summary.addClass('d-none');
                return;
            }

            selected.forEach(function(item) {
                $badges.append(
                    '<span class="criteria-selected-chip">' +
                    '<span class="chip-code">' + escapeHtml(item.code) + '</span>' +
                    '<span class="chip-text">' + escapeHtml(item.title) + '</span>' +
                    '</span>'
                );
            });
            $summary.removeClass('d-none');
        }

        function renderCriteriaList(filterText) {
            const $list = $('#criteria-list');
            const q = (filterText || '').toLowerCase().trim();

            $list.empty();

            if (!criteriaCache.length) {
                $list.append('<div class="text-muted small py-2 px-1">No criteria found.</div>');
                updateSelectedSummary();
                return;
            }

            let visibleCount = 0;
            criteriaCache.forEach(function(item) {
                const hay = [item.code, item.title, item.article_reference, item.description]
                    .filter(Boolean)
                    .join(' ')
                    .toLowerCase();
                const match = !q || hay.indexOf(q) !== -1;
                const id = 'criterion_' + item.id;
                const checked = selectedIds.indexOf(String(item.id)) !== -1;
                const meta = item.article_reference ?
                    '<span class="criteria-meta">' + escapeHtml(item.article_reference) + '</span>' :
                    '';

                // Keep selected items in the form even when filtered out of view
                if (!match && checked) {
                    $list.append(
                        '<input type="hidden" name="criterion_ids[]" value="' + item.id + '" class="criterion-hidden">'
                    );
                    return;
                }

                if (!match) {
                    return;
                }

                visibleCount++;
                $list.append(
                    '<div class="criteria-item custom-control custom-checkbox">' +
                    '<input type="checkbox" class="custom-control-input criterion-check" name="criterion_ids[]" ' +
                    'id="' + id + '" value="' + item.id + '"' + (checked ? ' checked' : '') + '>' +
                    '<label class="custom-control-label" for="' + id + '">' +
                    '<span class="badge badge-primary criteria-code">' + escapeHtml(item.code) + '</span>' +
                    '<span class="criteria-title">' + escapeHtml(item.title) + '</span>' +
                    meta +
                    '</label>' +
                    '</div>'
                );
            });

            if (!visibleCount) {
                $list.prepend('<div class="text-muted small py-2 px-1">No criteria match your search.</div>');
            }

            updateSelectedSummary();
        }

        function updateEndDateHint() {
            const type = $('#type').val();
            const effective = $('#effective_date').val();
            if (!type || !effective) {
                $('#end-date-hint').text('Validity: Coaching/Counseling 3 months; Warning Letters 6 months.');
                return;
            }
            const d = new Date(effective + 'T00:00:00');
            d.setMonth(d.getMonth() + monthsForType(type));
            const end = d.toISOString().slice(0, 10);
            $('#end-date-hint').text('Valid until: ' + end + ' (' + monthsForType(type) + ' months).');
        }

        function rebuildTypeOptions(allowed, preferred) {
            const $type = $('#type');
            const current = preferred || $type.val();
            $type.empty().append('<option value="">Select Type</option>');
            allowed.forEach(function(key) {
                if (scopeTypeKeys.indexOf(key) === -1) return;
                $type.append(new Option(allTypeLabels[key] || key, key, false, key === current));
            });
            $type.trigger('change.select2');
        }

        function loadCriteria(type, preselectedIds) {
            const $group = $('#criteria-group');
            $('#criteria-search').val('');
            selectedIds = (preselectedIds || []).map(String);

            if (!type || ['counseling', 'sp1', 'sp2', 'sp3'].indexOf(type) === -1) {
                criteriaCache = [];
                selectedIds = [];
                $group.hide();
                $('#criteria-list').html(
                    '<div class="text-muted small py-2 px-1">Select a disciplinary type to load PP criteria.</div>'
                );
                $('#criteria-selected-summary').addClass('d-none');
                return;
            }

            $group.show();
            $('#criteria-list').html('<div class="text-muted small py-2 px-1">Loading criteria...</div>');

            $.getJSON(criteriaOptionsUrl, {
                sanction_type: type
            }, function(res) {
                criteriaCache = res.data || [];
                if (!criteriaCache.length) {
                    $('#criteria-list').html(
                        '<div class="text-muted small py-2 px-1">No active PP criteria for this type. Use PP Notes if needed.</div>'
                    );
                    $('#criteria-selected-summary').addClass('d-none');
                    return;
                }
                renderCriteriaList('');
            });
        }

        function applyStatus(data) {
            const currentAllowed = (data.allowed_types || []).filter(function(t) {
                return scopeTypeKeys.indexOf(t) !== -1;
            });

            const $banner = $('#status-banner');
            const $term = $('#terminate-banner');
            const $submit = $('#btn-submit');

            if (data.requires_termination) {
                $banner.addClass('d-none');
                $term.removeClass('d-none');
                $('#btn-go-terminate').attr('href', terminateUrlTemplate.replace('__ID__', $('#employee_id').val()));
                if (!isEdit) {
                    $submit.prop('disabled', true);
                }
                return;
            }

            $term.addClass('d-none');
            $submit.prop('disabled', false);

            if ((data.active_records || []).length) {
                let html = '<strong>Active status:</strong> ' + data.active_records.map(function(r) {
                    return r.type_label + ' (until ' + r.end_date + ', ' + r.remaining_days + ' days left)';
                }).join('; ');
                if (!isEdit && data.suggest_next_label) {
                    html += '<br><strong>Auto-escalation:</strong> another violation during validity must use <strong>'
                        + escapeHtml(data.suggest_next_label) + '</strong> or higher (same/lower types are blocked).';
                } else if (!isEdit && data.sp_floor) {
                    html += '<br><strong>Escalation:</strong> same or lower types than the active SP are blocked.';
                } else if (isEdit) {
                    html += '<br><em class="text-muted">On edit you may keep the current type or escalate to a higher level.</em>';
                }
                $banner.removeClass('d-none').html(html);
            } else {
                $banner.addClass('d-none').empty();
            }

            // Create: force next escalated type when a floor exists. Edit: keep current unless invalid.
            let preferred = $('#type').val();
            if (!isEdit && data.suggest_next && currentAllowed.indexOf(data.suggest_next) !== -1) {
                preferred = data.suggest_next;
            } else if (preferred && currentAllowed.indexOf(preferred) === -1) {
                preferred = (data.suggest_next && currentAllowed.indexOf(data.suggest_next) !== -1)
                    ? data.suggest_next
                    : (currentAllowed[0] || '');
            }
            rebuildTypeOptions(currentAllowed.length ? currentAllowed : scopeTypeKeys, preferred);
            loadCriteria($('#type').val(), $('#criteria-group').data('selected') || []);
        }

        function fetchStatus() {
            const id = $('#employee_id').val();
            if (!id) {
                $('#status-banner').addClass('d-none');
                $('#terminate-banner').addClass('d-none');
                rebuildTypeOptions(scopeTypeKeys, $('#type').val());
                return;
            }
            $.getJSON(statusUrlTemplate.replace('__ID__', id), function(data) {
                applyStatus(data);
            });
        }

        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%',
                allowClear: true
            });

            $('#employee_id').on('change', fetchStatus);
            $('#type').on('change', function() {
                loadCriteria($(this).val(), []);
                updateEndDateHint();
            });
            $('#effective_date').on('change', updateEndDateHint);

            $('#remove_document').on('change', function() {
                $('#document').prop('required', $(this).is(':checked'));
            });

            $('#criteria-search').on('input', function() {
                renderCriteriaList($(this).val());
            });

            $(document).on('change', '.criterion-check', function() {
                const id = String($(this).val());
                if ($(this).is(':checked')) {
                    if (selectedIds.indexOf(id) === -1) {
                        selectedIds.push(id);
                    }
                } else {
                    selectedIds = selectedIds.filter(function(x) {
                        return x !== id;
                    });
                }
                updateSelectedSummary();
            });

            const initialSelected = $('#criteria-group').data('selected') || [];
            if (isEdit) {
                @if (isset($statusSummary))
                    applyStatus(@json($statusSummary));
                @else
                    fetchStatus();
                @endif
                loadCriteria($('#type').val(), initialSelected);
            } else if ($('#employee_id').val()) {
                fetchStatus();
            } else {
                $('#criteria-group').hide();
            }
            updateEndDateHint();
        });
    })();
</script>
