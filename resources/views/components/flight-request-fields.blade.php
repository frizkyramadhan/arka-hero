@props([
    'namePrefix' => 'fr_data',
    'allowReturnSegment' => true,
    'existingFlightRequest' => null,
])

@php
    // Prefer old() for validation repopulation; otherwise use existing flight request (edit mode)
    $needTicket = old($namePrefix . '.need_flight_ticket');
    $details = old($namePrefix . '.details');

    if ($details === null && $existingFlightRequest && $existingFlightRequest->details && $existingFlightRequest->details->isNotEmpty()) {
        $details = $existingFlightRequest->details->sortBy('segment_order')->values()->map(function ($d) {
            return [
                'segment_type' => $d->segment_type ?? 'departure',
                'flight_date' => $d->flight_date ? $d->flight_date->format('Y-m-d') : '',
                'departure_city' => $d->departure_city ?? '',
                'arrival_city' => $d->arrival_city ?? '',
                'airline' => $d->airline ?? '',
                'flight_time' => $d->flight_time ? \Carbon\Carbon::parse($d->flight_time)->format('H:i') : '',
            ];
        })->toArray();
    }

    if ($needTicket === null) {
        $needTicket = $existingFlightRequest && $existingFlightRequest->details && $existingFlightRequest->details->isNotEmpty();
    }
    $needTicket = (bool) $needTicket;

    if (empty($details)) {
        $details = [
            [
                'segment_type' => 'departure',
                'flight_date' => '',
                'departure_city' => '',
                'arrival_city' => '',
                'airline' => '',
                'flight_time' => '',
            ],
        ];
    }
@endphp

<div class="card card-outline card-info mb-3 flight-request-fields-wrapper shadow-sm" id="{{ $namePrefix }}_fr_wrapper"
    data-name-prefix="{{ $namePrefix }}" style="max-width: 100%;">
    <div class="card-header py-2">
        <h5 class="card-title mb-0">
            <i class="fas fa-plane mr-2"></i>
            <strong>Flight Request</strong>
        </h5>
    </div>
    <div class="card-body py-3">
        <div class="form-group mb-0">
            <div class="custom-control custom-checkbox">
                <input type="hidden" name="{{ $namePrefix }}[need_flight_ticket]" value="0">
                <input type="checkbox" class="custom-control-input" id="{{ $namePrefix }}_need_flight_ticket"
                    name="{{ $namePrefix }}[need_flight_ticket]" value="1" {{ $needTicket ? 'checked' : '' }}>
                <label class="custom-control-label" for="{{ $namePrefix }}_need_flight_ticket">
                    Check if you need flight ticket reservation
                </label>
            </div>
        </div>

        <div id="{{ $namePrefix }}_segments_wrap" class="mt-3" style="{{ $needTicket ? '' : 'display:none;' }}">
            <p class="text-muted small mb-2">
                <i class="fas fa-info-circle"></i> Fill in flight data: date, route, airline, and time.
            </p>
            <div id="{{ $namePrefix }}_segments_container">
                @foreach ($details as $index => $detail)
                    @php
                        $segType = $detail['segment_type'] ?? ($index === 0 ? 'departure' : 'return');
                        $segLabel = $segType === 'departure' ? 'Departure' : 'Return';
                    @endphp
                    <div class="border rounded p-3 mb-3 fr-segment bg-light" data-index="{{ $index }}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <strong class="small">Flight {{ $index + 1 }}</strong>
                            @if ($index > 0)
                                <button type="button" class="btn btn-sm btn-outline-danger fr-remove-segment">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            @endif
                        </div>
                        <input type="hidden" name="{{ $namePrefix }}[details][{{ $index }}][segment_type]"
                            value="{{ $segType }}">
                        <div class="row no-gutters fr-segment-fields">
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">Date <span class="text-danger">*</span></label>
                                    <input type="date"
                                        name="{{ $namePrefix }}[details][{{ $index }}][flight_date]"
                                        class="form-control form-control-sm" value="{{ $detail['flight_date'] ?? '' }}"
                                        {{ $needTicket ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">From (City/Airport) <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        name="{{ $namePrefix }}[details][{{ $index }}][departure_city]"
                                        class="form-control form-control-sm" placeholder="e.g. CGK"
                                        value="{{ $detail['departure_city'] ?? '' }}"
                                        {{ $needTicket ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">To (City/Airport) <span
                                            class="text-danger">*</span></label>
                                    <input type="text"
                                        name="{{ $namePrefix }}[details][{{ $index }}][arrival_city]"
                                        class="form-control form-control-sm" placeholder="e.g. DPS"
                                        value="{{ $detail['arrival_city'] ?? '' }}"
                                        {{ $needTicket ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-2">
                                    <label class="small mb-1">Airline</label>
                                    <input type="text"
                                        name="{{ $namePrefix }}[details][{{ $index }}][airline]"
                                        class="form-control form-control-sm" placeholder="e.g. Garuda"
                                        value="{{ $detail['airline'] ?? '' }}" {{ $needTicket ? '' : 'disabled' }}>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group mb-0">
                                    <label class="small mb-1">Time</label>
                                    <input type="time"
                                        name="{{ $namePrefix }}[details][{{ $index }}][flight_time]"
                                        class="form-control form-control-sm" value="{{ $detail['flight_time'] ?? '' }}"
                                        {{ $needTicket ? '' : 'disabled' }}>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if ($allowReturnSegment)
                <button type="button" class="btn btn-sm btn-success fr-add-segment"
                    id="{{ $namePrefix }}_add_segment_btn">
                    <i class="fas fa-plus"></i> Add Flight Segment
                </button>
            @endif
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function() {
                function initFlightRequestFields() {
                    var wrappers = document.querySelectorAll('.flight-request-fields-wrapper');
                    wrappers.forEach(function(wrapper) {
                        initOne(wrapper);
                    });
                }

                function initOne(wrapper) {
                    var prefix = wrapper.getAttribute('data-name-prefix');
                    if (!prefix) return;
                    var checkbox = document.getElementById(prefix + '_need_flight_ticket');
                    var segmentsWrap = document.getElementById(prefix + '_segments_wrap');
                    var container = document.getElementById(prefix + '_segments_container');
                    var addBtn = document.getElementById(prefix + '_add_segment_btn');

                    function setDisabled(disabled) {
                        var inputs = segmentsWrap ? segmentsWrap.querySelectorAll('input, select, textarea') : [];
                        inputs.forEach(function(inp) {
                            if (disabled) inp.setAttribute('disabled', 'disabled');
                            else inp.removeAttribute('disabled');
                        });
                    }

                    function clearSegmentFieldsAndRemoveExtra() {
                        if (!container) return;
                        var segments = container.querySelectorAll('.fr-segment');
                        segments.forEach(function(seg, i) {
                            if (i === 0) {
                                var inputs = seg.querySelectorAll('input[type="date"], input[type="text"], input[type="time"]');
                                inputs.forEach(function(inp) { inp.value = ''; });
                            } else {
                                seg.remove();
                            }
                        });
                        reindexSegments(prefix, container);
                    }

                    function toggleVisibility() {
                        var checked = checkbox && checkbox.checked;
                        if (segmentsWrap) segmentsWrap.style.display = checked ? '' : 'none';
                        setDisabled(!checked);
                        if (!checked) clearSegmentFieldsAndRemoveExtra();
                    }

                    if (checkbox) {
                        checkbox.addEventListener('change', toggleVisibility);
                    }
                    toggleVisibility();

                    if (!container) return;
                    if (addBtn) {
                        addBtn.addEventListener('click', function() {
                            var segments = container.querySelectorAll('.fr-segment');
                            var nextIndex = segments.length;
                            var segType = nextIndex === 0 ? 'departure' : 'return';
                            var segLabel = segType === 'departure' ? 'Departure' : 'Return';
                            var html = '<div class="border rounded p-3 mb-3 fr-segment bg-light" data-index="' +
                                nextIndex + '">' +
                                '<div class="d-flex justify-content-between align-items-center mb-3">' +
                                '<strong class="small">Flight ' + (nextIndex + 1) + '</strong>' +
                                '<button type="button" class="btn btn-sm btn-outline-danger fr-remove-segment"><i class="fas fa-times"></i> Remove</button>' +
                                '</div>' +
                                '<input type="hidden" name="' + prefix + '[details][' + nextIndex +
                                '][segment_type]" value="' + segType + '">' +
                                '<div class="row no-gutters fr-segment-fields">' +
                                '<div class="col-12"><div class="form-group mb-2"><label class="small mb-1">Date <span class="text-danger">*</span></label>' +
                                '<input type="date" name="' + prefix + '[details][' + nextIndex +
                                '][flight_date]" class="form-control form-control-sm"></div></div>' +
                                '<div class="col-12"><div class="form-group mb-2"><label class="small mb-1">From (City/Airport) <span class="text-danger">*</span></label>' +
                                '<input type="text" name="' + prefix + '[details][' + nextIndex +
                                '][departure_city]" class="form-control form-control-sm" placeholder="e.g. CGK"></div></div>' +
                                '<div class="col-12"><div class="form-group mb-2"><label class="small mb-1">To (City/Airport) <span class="text-danger">*</span></label>' +
                                '<input type="text" name="' + prefix + '[details][' + nextIndex +
                                '][arrival_city]" class="form-control form-control-sm" placeholder="e.g. DPS"></div></div>' +
                                '<div class="col-12"><div class="form-group mb-2"><label class="small mb-1">Airline</label>' +
                                '<input type="text" name="' + prefix + '[details][' + nextIndex +
                                '][airline]" class="form-control form-control-sm" placeholder="e.g. Garuda"></div></div>' +
                                '<div class="col-12"><div class="form-group mb-0"><label class="small mb-1">Time</label>' +
                                '<input type="time" name="' + prefix + '[details][' + nextIndex +
                                '][flight_time]" class="form-control form-control-sm"></div></div>' +
                                '</div></div>';
                            container.insertAdjacentHTML('beforeend', html);
                            reindexSegments(prefix, container);
                        });
                    }

                    container.addEventListener('click', function(e) {
                        if (e.target.closest('.fr-remove-segment')) {
                            var seg = e.target.closest('.fr-segment');
                            if (seg && container.querySelectorAll('.fr-segment').length > 1) {
                                seg.remove();
                                reindexSegments(prefix, container);
                            }
                        }
                    });
                }

                function reindexSegments(prefix, container) {
                    var segments = container.querySelectorAll('.fr-segment');
                    segments.forEach(function(seg, i) {
                        seg.setAttribute('data-index', i);
                        var segType = i === 0 ? 'departure' : 'return';
                        var segLabel = segType === 'departure' ? 'Departure' : 'Return';
                        seg.querySelector('strong').textContent = 'Flight ' + (i + 1);
                        seg.querySelector('input[type="hidden"]').setAttribute('name', prefix + '[details][' + i +
                            '][segment_type]');
                        seg.querySelector('input[type="hidden"]').value = segType;
                        var inputs = seg.querySelectorAll(
                            'input[type="date"], input[type="text"], input[type="time"]');
                        var names = ['flight_date', 'departure_city', 'arrival_city', 'airline', 'flight_time'];
                        inputs.forEach(function(inp, j) {
                            if (names[j]) inp.setAttribute('name', prefix + '[details][' + i + '][' + names[
                                j] + ']');
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initFlightRequestFields);
                } else {
                    initFlightRequestFields();
                }
            })
            ();
        </script>
    @endpush
@endonce
