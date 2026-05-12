@php
    /** @var \App\Models\Officialtravel $officialtravel */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Project> $destinationProjects */
    $lockedStopCount = $officialtravel->approvedItineraryLockedStopCount();
    $officialtravel->loadMissing('stops');
    $ordered = $officialtravel->stops->sortBy(['sort_order', 'id'])->values();
    $stopRows = [];
    $dbStopManual = [];
    foreach ($ordered as $stopRow) {
        $d = trim((string) $stopRow->destination);
        if ($d !== '') {
            $stopRows[] = $d;
            $dbStopManual[] = (bool) $stopRow->is_manual;
        }
    }
    $allHaveCheckpoint = $ordered->isNotEmpty() && $ordered->every(function ($s) {
        return $s->hasArrival() || $s->hasDeparture();
    });
    if ($allHaveCheckpoint) {
        $stopRows[] = '';
        $dbStopManual[] = false;
    }
    if ($stopRows === []) {
        $stopRows = [''];
    }
    $manualFlags = old('stop_destinations_manual', []);
    if (!is_array($manualFlags)) {
        $manualFlags = [];
    }
    if (is_array(old('stop_destinations'))) {
        $stopRows = old('stop_destinations');
    }
@endphp

<div class="travel-card mb-3">
    <div class="card-head">
        <h2 class="h5 mb-0"><i class="fas fa-route"></i> Edit itinerary</h2>
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">
            <strong>Approved</strong> trips only. Stamped stops stay <strong>locked</strong>—edit open stops below.
            Save: <strong>LOT origin</strong> only ({{ $officialtravel->project->project_code ?? '—' }} —
            {{ $officialtravel->project->project_name ?? '—' }}).
            <span class="badge badge-secondary ml-1">{{ $lockedStopCount }}</span> locked.
        </p>

        <form id="approvedItineraryAdjustForm" method="POST"
            action="{{ route('officialtravels.adjustApprovedItinerary', $officialtravel) }}">
            @csrf
            @method('PATCH')

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                <label class="mb-0 font-weight-normal">Destinations</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-stop-destination-approved">
                    <i class="fas fa-plus"></i> Add Destinations
                </button>
            </div>

            <div id="destination-stops-container-approved">
                @foreach ($stopRows as $idx => $rowDest)
                    @php
                        $rowFrozen = isset($ordered[$idx]) && ($ordered[$idx]->hasArrival() || $ordered[$idx]->hasDeparture());
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
                    <div class="stop-destination-row border rounded p-2 mb-2"
                        @if ($rowFrozen) data-frozen="1" @endif data-stop-row>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted font-weight-bold">
                                @if ($rowFrozen)
                                    <i class="fas fa-lock text-secondary mr-1"></i>
                                @endif
                                Destination {{ $idx + 1 }}
                            </small>
                            @if ($rowFrozen)
                                <span class="badge badge-light">Locked</span>
                            @else
                                <button type="button"
                                    class="btn btn-link btn-sm text-danger p-0 remove-stop-destination-approved">&times;
                                    Remove</button>
                            @endif
                        </div>
                        @if ($rowFrozen)
                            <p class="mb-1 small font-weight-bold">{{ $rowDest }}</p>
                            <input type="hidden" name="stop_destinations[]" value="{{ $rowDest }}">
                            <input type="hidden" name="stop_destinations_manual[]"
                                value="{{ $rowManual ? '1' : '0' }}">
                        @else
                            <input type="hidden" name="stop_destinations[]" class="stop-dest-hidden"
                                value="{{ $rowDest }}">
                            <input type="hidden" name="stop_destinations_manual[]" class="stop-manual-flag"
                                value="{{ $rowManual ? '1' : '0' }}">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text p-1"
                                        title="Type the place yourself (origin site handles stamps)">
                                        <input type="checkbox" class="stop-manual-cb mx-1"
                                            {{ $rowManual ? 'checked' : '' }} aria-label="Manual destination">
                                    </span>
                                </div>
                                <div class="stop-project-wrap flex-fill {{ $rowManual ? 'd-none' : '' }}"
                                    style="min-width:0">
                                    <select
                                        class="form-control stop-project-select select2-destination-destination-approved"
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
                                <input type="text"
                                    class="form-control stop-manual-input {{ $rowManual ? '' : 'd-none' }}"
                                    style="min-width:0" value="{{ $rowManual ? $rowDest : '' }}"
                                    placeholder="Destination text" autocomplete="off"
                                    {{ $rowManual ? '' : 'disabled' }}>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div id="approved-itinerary-adjust-prototype" class="d-none" aria-hidden="true">
                <div class="stop-destination-row border rounded p-2 mb-2" data-stop-row>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">Destination</small>
                        <button type="button"
                            class="btn btn-link btn-sm text-danger p-0 remove-stop-destination-approved">&times;
                            Remove</button>
                    </div>
                    <input type="hidden" name="stop_destinations[]" class="stop-dest-hidden" value="">
                    <input type="hidden" name="stop_destinations_manual[]" class="stop-manual-flag" value="0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text p-1">
                                <input type="checkbox" class="stop-manual-cb mx-1" aria-label="Manual destination">
                            </span>
                        </div>
                        <div class="stop-project-wrap flex-fill" style="min-width:0">
                            <select class="form-control stop-project-select select2-destination-destination-approved"
                                style="width:100%">
                                <option value="">Select project</option>
                                @foreach ($destinationProjects as $project)
                                    @php $destinationOptLabel = $project->project_code . ' - ' . $project->project_name; @endphp
                                    <option value="{{ $destinationOptLabel }}">{{ $destinationOptLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="text" class="form-control stop-manual-input d-none" style="min-width:0"
                            value="" placeholder="Destination text" disabled>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-2">
                <i class="fas fa-save"></i> Save
            </button>
        </form>
    </div>
</div>

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
                } catch (e) {}
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
                if ($row.attr('data-frozen')) {
                    return;
                }
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

            function renumberApprovedStops($container) {
                $container.find('.stop-destination-row').each(function(i) {
                    var $label = $(this).find('small.font-weight-bold').first();
                    if ($(this).attr('data-frozen')) {
                        $label.html(
                            '<i class="fas fa-lock text-secondary mr-1"></i> Destination ' + (i + 1));
                    } else {
                        $label.text('Destination ' + (i + 1));
                    }
                });
            }

            function refreshRemoveButtons($form) {
                var $c = $form.find('#destination-stops-container-approved');
                var $tail = $c.find('.stop-destination-row').not('[data-frozen]');
                var nFrozen = $c.find('.stop-destination-row[data-frozen]').length;
                var disableRemove = ($tail.length <= 1 && nFrozen === 0);
                $tail.find('.remove-stop-destination-approved').prop('disabled', disableRemove);
            }

            var $form = $('#approvedItineraryAdjustForm');
            var $container = $('#destination-stops-container-approved');
            if (!$form.length || !$container.length) {
                return;
            }

            $container.find('.stop-destination-row').not('[data-frozen]').each(function() {
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
                $container.find('.stop-destination-row').not('[data-frozen]').each(function() {
                    syncRow($(this));
                });
            });

            $('#add-stop-destination-approved').on('click', function() {
                var $tpl = $container.find('.stop-destination-row').not('[data-frozen]').first();
                if (!$tpl.length) {
                    $tpl = $('#approved-itinerary-adjust-prototype .stop-destination-row').clone();
                } else {
                    $tpl = $tpl.clone(false, false);
                }
                $tpl.removeAttr('data-frozen');
                $tpl.find('.stop-dest-hidden').val('');
                $tpl.find('.stop-manual-flag').val('0');
                $tpl.find('.stop-manual-cb').prop('checked', false);
                $tpl.find('.stop-manual-input').val('').addClass('d-none').prop('disabled', true);
                $tpl.find('.stop-project-wrap').removeClass('d-none');
                var $fresh = $('<select>', {
                    'class': 'form-control stop-project-select select2-destination-destination-approved',
                    'style': 'width:100%'
                });
                var optHtml = $container.find('.stop-project-select').first().html();
                if (!optHtml) {
                    optHtml = $('#approved-itinerary-adjust-prototype .stop-project-select').first().html();
                }
                $fresh.html(optHtml || '');
                $fresh.find('option').prop('selected', false);
                $fresh.find('option[value=""]').first().prop('selected', true);
                $fresh.val('');
                $tpl.find('.stop-project-select').replaceWith($fresh);
                $container.append($tpl);
                renumberApprovedStops($container);
                toggleRow($tpl, $form);
                refreshRemoveButtons($form);
            });

            $container.on('click', '.remove-stop-destination-approved', function() {
                if ($(this).prop('disabled')) {
                    return;
                }
                var $row = $(this).closest('.stop-destination-row');
                if ($row.attr('data-frozen')) {
                    return;
                }
                var $tail = $container.find('.stop-destination-row').not('[data-frozen]');
                var nFrozen = $container.find('.stop-destination-row[data-frozen]').length;
                if ($tail.length <= 1 && nFrozen === 0) {
                    return;
                }
                destroyLegSelect2($row.find('.stop-project-select'));
                $row.remove();
                renumberApprovedStops($container);
                refreshRemoveButtons($form);
            });

            renumberApprovedStops($container);
            refreshRemoveButtons($form);
        })();
    </script>
@endpush
