@php
    $ot = $officialtravel ?? null;
    $stopsLocked = $ot && !$ot->plannedStopsAreEditable();

    $stopRows = old('stop_destinations');
    if (!is_array($stopRows)) {
        $stopRows = [];
    }
    $stopRows = array_values(array_filter(array_map('trim', $stopRows), fn($s) => $s !== ''));
    if ($stopRows === [] && old('destination')) {
        $stopRows = [old('destination')];
    }
    $dbStopManual = [];
    if ($stopRows === [] && $ot) {
        $ot->loadMissing('stops');
        if ($ot->stops->isNotEmpty()) {
            foreach ($ot->stops as $stopRow) {
                $d = trim((string) $stopRow->destination);
                if ($d !== '') {
                    $stopRows[] = $d;
                    $dbStopManual[] = (bool) $stopRow->is_manual;
                }
            }
        }
        if ($stopRows === [] && $ot->destination) {
            $stopRows = [$ot->destination];
        }
    }
    if ($stopRows === []) {
        $stopRows = [''];
    }

    $manualFlags = old('stop_destinations_manual');
    if (!is_array($manualFlags)) {
        $manualFlags = [];
    }
@endphp

@if ($stopsLocked)
    <div class="form-group">
        <label>Itinerary (destinations per destination)</label>
        <ol class="mb-0 pl-3">
            @foreach ($ot->stops as $stop)
                <li>{{ $stop->destination }}</li>
            @endforeach
        </ol>
        <small class="text-muted">Itinerary cannot be changed after any arrival or departure has been recorded.</small>
    </div>
@else
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
        <label class="mb-0 font-weight-normal">
            <i class="fas fa-map-marker-alt mr-1"></i>
            Destinations <span class="text-danger">*</span>
        </label>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="add-stop-destination"
            aria-label="Add destination">
            <i class="fas fa-plus"></i> Add destination
        </button>
    </div>

    <div id="destination-stops-container">
        @foreach ($stopRows as $idx => $rowDest)
            @php
                $rowManual = false;
                if (isset($manualFlags[$idx]) && (string) $manualFlags[$idx] === '1') {
                    $rowManual = true;
                } elseif (isset($dbStopManual[$idx])) {
                    $rowManual = (bool) $dbStopManual[$idx];
                } elseif ($rowDest !== '' && isset($destinationProjects)) {
                    $rowManual = true;
                    foreach ($destinationProjects as $project) {
                        $label = $project->project_code . ' - ' . $project->project_name;
                        if ((string) $rowDest === (string) $label) {
                            $rowManual = false;
                            break;
                        }
                    }
                }
            @endphp
            <div class="stop-destination-row border rounded p-2 mb-2" data-stop-row>
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted font-weight-bold">Destination {{ $idx + 1 }}</small>
                    <button type="button" class="btn btn-link btn-sm text-danger p-0 remove-stop-destination"
                        @if (count($stopRows) <= 1) disabled @endif>&times; Remove</button>
                </div>
                <input type="hidden" name="stop_destinations[]" class="stop-dest-hidden" value="{{ $rowDest }}">
                <input type="hidden" name="stop_destinations_manual[]" class="stop-manual-flag"
                    value="{{ $rowManual ? '1' : '0' }}">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text p-1" title="Free-text destination (origin project stamps)">
                            <input type="checkbox" class="stop-manual-cb mx-1" {{ $rowManual ? 'checked' : '' }}
                                aria-label="Manual destination">
                        </span>
                    </div>
                    <div class="stop-project-wrap flex-fill {{ $rowManual ? 'd-none' : '' }}" style="min-width:0">
                        <select class="form-control stop-project-select select2-destination-destination"
                            style="width:100%">
                            <option value="">Select project</option>
                            @foreach ($destinationProjects as $project)
                                @php $destinationOptLabel = $project->project_code . ' - ' . $project->project_name; @endphp
                                <option value="{{ $destinationOptLabel }}"
                                    {{ !$rowManual && (string) $rowDest === (string) $destinationOptLabel ? 'selected' : '' }}>
                                    {{ $destinationOptLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <input type="text" class="form-control stop-manual-input {{ $rowManual ? '' : 'd-none' }}"
                        style="min-width:0" value="{{ $rowManual ? $rowDest : '' }}"
                        placeholder="Manual destination label" autocomplete="off" {{ $rowManual ? '' : 'disabled' }}>
                </div>
            </div>
        @endforeach
    </div>

    <small class="form-text text-muted d-block mb-1">Pick an active project per destination, or check the box for free
        text
        (checkpoints for those destinations use the travel <strong>origin</strong> project assignment).</small>
    @error('stop_destinations')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
    @error('stop_destinations.*')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
@endif

@once
    @push('scripts')
        <script>
            (function() {
                function stripSelect2DomArtifacts($select) {
                    if (!$select.length) return;
                    var $wrap = $select.closest('.stop-project-wrap');
                    $wrap.find('span.select2-container, span.select2').remove();
                    $select.next('span.select2-container, span.select2').remove();
                    $select.removeClass('select2-hidden-accessible');
                    $select.removeAttr('data-select2-id');
                    $select.removeAttr('aria-hidden');
                    $select.removeAttr('tabindex');
                }

                function destroyLegSelect2($select) {
                    if (!$select.length) return;
                    try {
                        if ($select.hasClass('select2-hidden-accessible') && $select.data('select2')) {
                            $select.select2('destroy');
                        }
                    } catch (e) {
                        /* cloned selects often have no plugin instance */
                    }
                    stripSelect2DomArtifacts($select);
                }

                function initLegSelect2($select, $dropdownParent) {
                    if (!$select.length) return;
                    destroyLegSelect2($select);
                    var $parent = $dropdownParent && $dropdownParent.length ? $dropdownParent : $select.closest('form');
                    if (!$parent.length) {
                        $parent = $(document.body);
                    }
                    $select.select2({
                        theme: 'bootstrap4',
                        placeholder: 'Select project',
                        width: '100%',
                        dropdownParent: $parent
                    }).on('select2:open', function() {
                        var el = document.querySelector('.select2-container--open .select2-search__field');
                        if (el) el.focus();
                    });
                }

                function syncRow($row) {
                    var manual = $row.find('.stop-manual-cb').is(':checked');
                    $row.find('.stop-manual-flag').val(manual ? '1' : '0');
                    var v = '';
                    if (manual) {
                        v = ($row.find('.stop-manual-input').val() || '').trim();
                    } else {
                        v = ($row.find('.stop-project-select').val() || '').trim();
                    }
                    $row.find('.stop-dest-hidden').val(v);
                }

                function toggleRow($row, $dropdownParent) {
                    var manual = $row.find('.stop-manual-cb').is(':checked');
                    var $wrap = $row.find('.stop-project-wrap');
                    var $sel = $row.find('.stop-project-select');
                    var $in = $row.find('.stop-manual-input');
                    if (manual) {
                        destroyLegSelect2($sel);
                        $wrap.addClass('d-none');
                        $in.removeClass('d-none').prop('disabled', false);
                        $sel.prop('disabled', true);
                    } else {
                        $in.addClass('d-none').prop('disabled', true);
                        $wrap.removeClass('d-none');
                        $sel.prop('disabled', false);
                        initLegSelect2($sel, $dropdownParent);
                    }
                    syncRow($row);
                }

                function bindOfficialTravelMultiDest($form) {
                    if (!$form.length || $form.data('multiDestBound')) return;
                    $form.data('multiDestBound', true);

                    var legSelectOptionsHtml = $form.find('#destination-stops-container .stop-project-select')
                        .first().html();

                    $form.find('#destination-stops-container .stop-destination-row').each(function() {
                        toggleRow($(this), $form);
                    });

                    $form.on('change', '.stop-manual-cb', function() {
                        toggleRow($(this).closest('.stop-destination-row'), $form);
                    });
                    $form.on('change', '.stop-project-select', function() {
                        syncRow($(this).closest('.stop-destination-row'));
                    });
                    $form.on('input', '.stop-manual-input', function() {
                        syncRow($(this).closest('.stop-destination-row'));
                    });
                    $form.on('submit', function() {
                        $form.find('.stop-destination-row').each(function() {
                            syncRow($(this));
                        });
                    });

                    $form.on('click', '#add-stop-destination', function() {
                        var $container = $form.find('#destination-stops-container');
                        var $tpl = $container.find('.stop-destination-row').first();
                        if (!$tpl.length) return;
                        var $clone = $tpl.clone(false, false);
                        $clone.find('.stop-dest-hidden').val('');
                        $clone.find('.stop-manual-flag').val('0');
                        $clone.find('.stop-manual-cb').prop('checked', false);
                        $clone.find('.stop-manual-input').val('').addClass('d-none').prop('disabled', true);
                        $clone.find('.stop-project-wrap').removeClass('d-none');
                        var $fresh = $('<select>', {
                            'class': 'form-control stop-project-select select2-destination-destination',
                            'style': 'width:100%'
                        });
                        $fresh.html(legSelectOptionsHtml || '');
                        $fresh.find('option').prop('selected', false);
                        $fresh.find('option[value=""]').first().prop('selected', true);
                        $fresh.val('');
                        $clone.find('.stop-project-select').replaceWith($fresh);
                        $container.append($clone);
                        $container.find('.stop-destination-row').each(function(i) {
                            $(this).find('small.font-weight-bold').first().text('Destination ' + (i + 1));
                        });
                        $container.find('.remove-stop-destination').prop('disabled', false);
                        toggleRow($clone, $form);
                    });

                    $form.on('click', '.remove-stop-destination', function() {
                        var $rows = $form.find('#destination-stops-container .stop-destination-row');
                        if ($rows.length <= 1) return;
                        $(this).closest('.stop-destination-row').remove();
                        $form.find('#destination-stops-container .stop-destination-row').each(function(i) {
                            $(this).find('small.font-weight-bold').first().text('Destination ' + (i + 1));
                        });
                        if ($form.find('#destination-stops-container .stop-destination-row').length <= 1) {
                            $form.find('.remove-stop-destination').prop('disabled', true);
                        }
                    });
                }

                $(function() {
                    bindOfficialTravelMultiDest($('#officialTravelForm'));
                });
            })
            ();
        </script>
    @endpush
@endonce
