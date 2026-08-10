@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee-disciplinaries.index') }}">Disciplinary</a>
                        </li>
                        <li class="breadcrumb-item active">Termination</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <div class="alert alert-danger">
                        <strong>{{ $employee->fullname }}</strong> still has an active
                        <strong>First & Final Warning (SP3)</strong>. A further violation triggers termination of the active
                        administration record.
                    </div>

                    @if (!empty($statusSummary['active_records']))
                        <div class="card mb-3">
                            <div class="card-header"><strong>Active Status</strong></div>
                            <div class="card-body">
                                <ul class="mb-0">
                                    @foreach ($statusSummary['active_records'] as $active)
                                        <li>{{ $active['type_label'] }} — until {{ $active['end_date'] }}
                                            ({{ $active['remaining_days'] }} days left)</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    @if (!$administration)
                        <div class="alert alert-warning">No active administration found for this employee.</div>
                    @else
                        <form action="{{ route('employee-disciplinaries.terminate', $employee->id) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to terminate this employee administration?');">
                            @csrf
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">{{ $subtitle }}</h3>
                                </div>
                                <div class="card-body">
                                    <p>
                                        NIK: <strong>{{ $administration->nik }}</strong><br>
                                        ID Card: <strong>{{ $employee->identity_card ?: '-' }}</strong>
                                    </p>
                                    <div class="form-group">
                                        <label for="termination_date">Termination Date <span
                                                class="text-danger">*</span></label>
                                        <input type="date" name="termination_date" id="termination_date"
                                            class="form-control @error('termination_date') is-invalid @enderror"
                                            value="{{ old('termination_date', date('Y-m-d')) }}" required>
                                        @error('termination_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="termination_reason">Termination Reason <span
                                                class="text-danger">*</span></label>
                                        <textarea name="termination_reason" id="termination_reason" rows="3"
                                            class="form-control @error('termination_reason') is-invalid @enderror"
                                            required>{{ old('termination_reason', 'Repeated violation after First & Final Warning') }}</textarea>
                                        @error('termination_reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-user-slash"></i> Process Termination
                                    </button>
                                    <a href="{{ route('employee-disciplinaries.index') }}"
                                        class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
