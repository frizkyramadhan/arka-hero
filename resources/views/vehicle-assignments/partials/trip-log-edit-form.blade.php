@php
    /** @var \App\Models\VehicleAssignment $assignment */
    $legs = $assignment->tripLegs();
    $formAction = $formAction ?? route('vehicle-assignments.my-trips.update-stops', $assignment);
@endphp

<div class="travel-card foa-trip-log-edit-card">
    <div class="card-head">
        <h2><i class="fas fa-route"></i> Trip Log — Jam Berangkat / Tiba</h2>
    </div>
    <form method="POST" action="{{ $formAction }}">
        @csrf
        @method('PUT')
        <div class="card-body table-responsive p-0">
            <table class="table table-bordered mb-0 foa-trip-log-table foa-trip-log-edit">
                <thead class="thead-light">
                    <tr>
                        <th class="col-leg text-center">Leg</th>
                        <th class="col-dest text-center">Tujuan</th>
                        <th class="col-time text-center">Pukul Berangkat</th>
                        <th class="col-km text-center">Km</th>
                        <th class="col-time text-center">Pukul Tiba</th>
                        <th class="col-km text-center">Km</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($legs as $i => $stop)
                        <tr>
                            <td class="col-leg">
                                <strong>{{ $stop->legLabel() }}</strong>
                                <input type="hidden" name="stops[{{ $i }}][id]" value="{{ $stop->id }}">
                            </td>
                            <td class="col-dest">
                                {{ $stop->destination }}
                                @if ($stop->is_manual)
                                    <span class="badge badge-secondary">Manual</span>
                                @endif
                            </td>
                            <td class="col-time">
                                <input type="time" name="stops[{{ $i }}][depart_time]"
                                    class="form-control foa-trip-input"
                                    value="{{ $stop->depart_time ? substr($stop->depart_time, 0, 5) : '' }}">
                            </td>
                            <td class="col-km">
                                <input type="number" name="stops[{{ $i }}][depart_km]"
                                    class="form-control foa-trip-input text-right" min="0"
                                    value="{{ $stop->depart_km }}">
                            </td>
                            <td class="col-time">
                                <input type="time" name="stops[{{ $i }}][arrive_time]"
                                    class="form-control foa-trip-input"
                                    value="{{ $stop->arrive_time ? substr($stop->arrive_time, 0, 5) : '' }}">
                            </td>
                            <td class="col-km">
                                <input type="number" name="stops[{{ $i }}][arrive_km]"
                                    class="form-control foa-trip-input text-right" min="0"
                                    value="{{ $stop->arrive_km }}">
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No destinations yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($legs->isNotEmpty())
            <div class="card-body">
                <button type="submit" class="btn btn-primary px-3">
                    <i class="fas fa-save"></i> Save Jam / KM
                </button>
            </div>
        @endif
    </form>
</div>
