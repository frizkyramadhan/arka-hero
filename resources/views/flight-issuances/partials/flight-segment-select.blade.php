@php
    $flightSegments = $flightSegments ?? collect();
    $selectedSegmentId = $selected ?? null;
@endphp
<div class="col-md-6">
    <div class="form-group">
        <label>
            <i class="fas fa-route mr-1"></i> Flight Segment <span class="text-danger">*</span>
        </label>
        <select name="details[{{ $index }}][flight_request_detail_id]"
            class="form-control flight-segment-select" required>
            <option value="">— Select Flight Segment —</option>
            @foreach ($flightSegments as $seg)
                <option value="{{ $seg['id'] }}"
                    data-reservation="{{ e($seg['reservation_text']) }}"
                    {{ (string) $selectedSegmentId === (string) $seg['id'] ? 'selected' : '' }}>
                    {{ $seg['label'] }}
                </option>
            @endforeach
        </select>
    </div>
</div>
