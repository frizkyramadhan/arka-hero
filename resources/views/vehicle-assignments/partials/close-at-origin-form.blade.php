@php
    /** @var \App\Models\VehicleAssignment $assignment */
@endphp
<div class="travel-card mb-3">
    <div class="card-head">
        <h2><i class="fas fa-flag-checkered"></i> Close at Origin</h2>
    </div>
    <form method="POST" action="{{ $formAction }}">
        @csrf
        <div class="card-body">
            <p class="text-muted small">
                Mengisi <em>Tiba</em> pada baris pulang ke
                <strong>{{ $assignment->origin_destination }}</strong>.
            </p>
            <div class="form-group">
                <label>Pukul Tiba Origin <span class="text-danger">*</span></label>
                <input type="time" name="arrive_time" class="form-control" required>
            </div>
            <div class="form-group mb-0">
                <label>Km Tiba Origin <span class="text-danger">*</span></label>
                <input type="number" name="arrive_km" class="form-control" min="0" required>
            </div>
        </div>
        <div class="card-body pt-0">
            <button class="btn btn-warning btn-block"
                onclick="return confirm('Close this FOA at origin?')">
                <i class="fas fa-flag-checkered"></i> Close FOA
            </button>
        </div>
    </form>
</div>
