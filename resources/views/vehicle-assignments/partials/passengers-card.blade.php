@php
$passengers = $assignment->passengers;
@endphp
<div class="travel-card followers-card">
    <div class="card-head">
        <h2>
            <i class="fas fa-users"></i> Passengers
            @if ($passengers->isNotEmpty())
            <span class="followers-count">{{ $passengers->count() }}</span>
            @endif
        </h2>
    </div>
    <div class="card-body p-0">
        @if ($passengers->isEmpty())
        <p class="text-muted mb-0 px-3 py-4">No passengers</p>
        @else
        <div class="followers-list">
            @foreach ($passengers as $p)
            @php
            $admin = $p->employee?->activeAdministration;
            $position = $admin?->position;
            $department = $position?->department;
            $project = $admin?->project;
            @endphp
            <div class="follower-item">
                <div class="follower-info">
                    <div class="follower-name">
                        {{ $p->passenger_name ?: ($p->employee?->fullname ?? '—') }}
                        @if (! $p->employee_id)
                        <span class="badge badge-secondary follower-manual-badge">External</span>
                        @endif
                    </div>
                    @if ($p->employee_id && $admin)
                    <div class="follower-position">
                        {{ $position?->position_name ?? 'No Position' }}
                    </div>
                    <div class="follower-meta">
                        <span class="follower-nik">
                            <i class="fas fa-id-card"></i>
                            {{ $admin->nik ?: '—' }}
                        </span>
                        <span class="follower-department">
                            <i class="fas fa-sitemap"></i>
                            {{ $department?->department_name ?? 'No Department' }}
                        </span>
                    </div>
                    @if ($project)
                    <div class="follower-project">
                        <i class="fas fa-project-diagram"></i>
                        {{ $project->project_code ?? '—' }}
                        @if ($project->project_name)
                        : {{ $project->project_name }}
                        @endif
                    </div>
                    @endif
                    @elseif ($p->employee_id)
                    <div class="follower-position text-muted">
                        Employee linked — administration data unavailable
                    </div>
                    @else

                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>