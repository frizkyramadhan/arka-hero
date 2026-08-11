@php
    /** @var \App\Models\VehicleAssignment $assignment */
    /** @var \Illuminate\Support\Collection $destinationProjects */
    $lockedStopCount = $assignment->lockedDestinationStopCount();
    $assignment->loadMissing('stops');
    $ordered = $assignment->stops
        ->where('stop_type', \App\Models\VehicleAssignmentStop::TYPE_DESTINATION)
        ->sortBy(['sequence', 'id'])
        ->values();
    $stopRows = [];
    $dbStopManual = [];
    foreach ($ordered as $stopRow) {
        $d = trim((string) $stopRow->destination);
        if ($d !== '') {
            $stopRows[] = $d;
            $dbStopManual[] = (bool) $stopRow->is_manual;
        }
    }
    $allLocked = $ordered->isNotEmpty() && $ordered->every(fn ($s) => $s->hasTripActivity());
    if ($allLocked) {
        $stopRows[] = '';
        $dbStopManual[] = false;
    }
    if ($stopRows === []) {
        $stopRows = [''];
    }
    $manualFlags = old('stop_destinations_manual', []);
    if (! is_array($manualFlags)) {
        $manualFlags = [];
    }
    if (is_array(old('stop_destinations'))) {
        $stopRows = old('stop_destinations');
    }
@endphp

<div class="travel-card mb-3">
    <div class="card-head">
        <h2><i class="fas fa-route"></i> Edit Destinations</h2>
    </div>
    <div class="card-body">
        <p class="small text-muted mb-3">
            FOA sudah <strong>{{ $assignment->statusLabel() }}</strong>.
            Tujuan yang sudah terisi jam/KM terkunci.
            Bisa menambah tujuan baru (project atau manual).
            <span class="badge badge-secondary ml-1">{{ $lockedStopCount }} locked</span>
        </p>

        <form id="foaDestinationsAdjustForm" method="POST"
            action="{{ $formAction ?? route('vehicle-assignments.adjustDestinations', $assignment) }}">
            @csrf
            @method('PATCH')

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
                <label class="mb-0 font-weight-normal">Destinations</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="add-foa-destination">
                    <i class="fas fa-plus"></i> Add destination
                </button>
            </div>

            <div id="foa-destination-stops-container">
                @foreach ($stopRows as $idx => $rowDest)
                    @php
                        $rowFrozen = isset($ordered[$idx]) && $ordered[$idx]->hasTripActivity();
                        $rowManual = false;
                        if (isset($manualFlags[$idx]) && (string) $manualFlags[$idx] === '1') {
                            $rowManual = true;
                        } elseif (isset($dbStopManual[$idx])) {
                            $rowManual = (bool) $dbStopManual[$idx];
                        } elseif ($rowDest !== '' && isset($destinationProjects)) {
                            $rowManual = true;
                            foreach ($destinationProjects as $project) {
                                $label = $project->project_code.' - '.$project->project_name;
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
                                    class="btn btn-link btn-sm text-danger p-0 remove-foa-destination">&times;
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
                                    <span class="input-group-text p-1" title="External / manual destination">
                                        <input type="checkbox" class="stop-manual-cb mx-1"
                                            {{ $rowManual ? 'checked' : '' }} aria-label="Manual destination">
                                    </span>
                                </div>
                                <div class="stop-project-wrap flex-fill {{ $rowManual ? 'd-none' : '' }}"
                                    style="min-width:0">
                                    <select class="form-control stop-project-select foa-adjust-project-select"
                                        style="width:100%">
                                        <option value="">Select project</option>
                                        @foreach ($destinationProjects as $project)
                                            @php $destinationOptLabel = $project->project_code.' - '.$project->project_name; @endphp
                                            <option value="{{ $destinationOptLabel }}"
                                                {{ ! $rowManual && (string) $rowDest === (string) $destinationOptLabel ? 'selected' : '' }}>
                                                {{ $destinationOptLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="text"
                                    class="form-control stop-manual-input {{ $rowManual ? '' : 'd-none' }}"
                                    style="min-width:0" value="{{ $rowManual ? $rowDest : '' }}"
                                    placeholder="Manual destination" autocomplete="off"
                                    {{ $rowManual ? '' : 'disabled' }}>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div id="foa-destination-adjust-prototype" class="d-none" aria-hidden="true">
                <div class="stop-destination-row border rounded p-2 mb-2" data-stop-row>
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted font-weight-bold">Destination</small>
                        <button type="button"
                            class="btn btn-link btn-sm text-danger p-0 remove-foa-destination">&times;
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
                            <select class="form-control stop-project-select foa-adjust-project-select"
                                style="width:100%">
                                <option value="">Select project</option>
                                @foreach ($destinationProjects as $project)
                                    @php $destinationOptLabel = $project->project_code.' - '.$project->project_name; @endphp
                                    <option value="{{ $destinationOptLabel }}">{{ $destinationOptLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="text" class="form-control stop-manual-input d-none" style="min-width:0"
                            value="" placeholder="Manual destination" disabled>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-2">
                <i class="fas fa-save"></i> Save destinations
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
        $select.removeAttr('data-select2-id aria-hidden tabindex');
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
        if (!$parent.length) $parent = $(document.body);
        $select.select2({
            theme: 'bootstrap4',
            placeholder: 'Select project',
            width: '100%',
            dropdownParent: $parent
        });
    }

    function syncRow($row) {
        var manual = $row.find('.stop-manual-cb').is(':checked');
        $row.find('.stop-manual-flag').val(manual ? '1' : '0');
        var v = manual
            ? ($row.find('.stop-manual-input').val() || '').trim()
            : ($row.find('.stop-project-select').val() || '').trim();
        $row.find('.stop-dest-hidden').val(v);
    }

    function toggleRow($row, $dropdownParent) {
        if ($row.attr('data-frozen')) return;
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

    function renumber($container) {
        $container.find('.stop-destination-row').each(function(i) {
            var $label = $(this).find('small.font-weight-bold').first();
            if ($(this).attr('data-frozen')) {
                $label.html('<i class="fas fa-lock text-secondary mr-1"></i> Destination ' + (i + 1));
            } else {
                $label.text('Destination ' + (i + 1));
            }
        });
    }

    function refreshRemoveButtons($form) {
        var $c = $form.find('#foa-destination-stops-container');
        var $tail = $c.find('.stop-destination-row').not('[data-frozen]');
        var nFrozen = $c.find('.stop-destination-row[data-frozen]').length;
        $tail.find('.remove-foa-destination').prop('disabled', $tail.length <= 1 && nFrozen === 0);
    }

    var $form = $('#foaDestinationsAdjustForm');
    var $container = $('#foa-destination-stops-container');
    if (!$form.length || !$container.length) return;

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

    $('#add-foa-destination').on('click', function() {
        var $tpl = $container.find('.stop-destination-row').not('[data-frozen]').first();
        if (!$tpl.length) {
            $tpl = $('#foa-destination-adjust-prototype .stop-destination-row').clone();
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
            'class': 'form-control stop-project-select foa-adjust-project-select',
            css: { width: '100%' }
        });
        var optHtml = $container.find('.stop-project-select').first().html()
            || $('#foa-destination-adjust-prototype .stop-project-select').first().html()
            || '';
        $fresh.html(optHtml);
        $fresh.find('option').prop('selected', false);
        $fresh.find('option[value=""]').first().prop('selected', true);
        $fresh.val('');
        $tpl.find('.stop-project-select').replaceWith($fresh);
        $container.append($tpl);
        renumber($container);
        toggleRow($tpl, $form);
        refreshRemoveButtons($form);
    });

    $container.on('click', '.remove-foa-destination', function() {
        if ($(this).prop('disabled')) return;
        var $row = $(this).closest('.stop-destination-row');
        if ($row.attr('data-frozen')) return;
        var $tail = $container.find('.stop-destination-row').not('[data-frozen]');
        var nFrozen = $container.find('.stop-destination-row[data-frozen]').length;
        if ($tail.length <= 1 && nFrozen === 0) return;
        destroyLegSelect2($row.find('.stop-project-select'));
        $row.remove();
        renumber($container);
        refreshRemoveButtons($form);
    });

    renumber($container);
    refreshRemoveButtons($form);
})();
</script>
@endpush
