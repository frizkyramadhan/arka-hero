@php
    $lotFollowers =
        $flightRequest->request_type === \App\Models\FlightRequest::TYPE_TRAVEL_BASED &&
        $flightRequest->officialTravel &&
        $flightRequest->officialTravel->details->isNotEmpty();

    $standaloneFollowers =
        $flightRequest->request_type === \App\Models\FlightRequest::TYPE_STANDALONE &&
        $flightRequest->relationLoaded('followers') &&
        $flightRequest->followers->isNotEmpty();
@endphp

@if ($lotFollowers)
    <div class="flight-request-card followers-card flight-request-lot-followers mb-4">
        <div class="card-head">
            <h2><i class="fas fa-users"></i> Followers <span
                    class="followers-count">{{ $flightRequest->officialTravel->details->count() }}</span>
            </h2>
        </div>
        <div class="card-body p-0">
            <div class="followers-list">
                @foreach ($flightRequest->officialTravel->details as $detail)
                    <div class="follower-item">
                        <div class="follower-info">
                            <div class="follower-name">
                                {{ $detail->follower->employee->fullname ?? 'Unknown Employee' }}
                            </div>
                            <div class="follower-position">
                                {{ $detail->follower->position->position_name ?? 'No Position' }}
                            </div>
                            <div class="follower-meta">
                                <span class="follower-nik"><i class="fas fa-id-card"></i>
                                    {{ $detail->follower->nik }}</span>
                                <span class="follower-department"><i class="fas fa-sitemap"></i>
                                    {{ $detail->follower->position->department->department_name ?? 'No Department' }}</span>
                            </div>
                            <div class="follower-project">
                                <i class="fas fa-project-diagram"></i>
                                {{ $detail->follower->project->project_code ?? 'No Code' }} :
                                {{ $detail->follower->project->project_name ?? 'No Project' }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@elseif ($standaloneFollowers)
    <div class="flight-request-card followers-card flight-request-standalone-followers mb-4">
        <div class="card-head">
            <h2><i class="fas fa-users"></i> Followers <span
                    class="followers-count">{{ $flightRequest->followers->count() }}</span>
            </h2>
        </div>
        <div class="card-body p-0">
            <div class="followers-list">
                @foreach ($flightRequest->followers as $follower)
                    @php
                        $isEmployeeFollower = ! $follower->isManual();
                        $followerPosition =
                            $follower->position ??
                            ($follower->administration?->position?->position_name ?? null);
                        $followerDepartment =
                            $follower->department ??
                            ($follower->administration?->position?->department?->department_name ?? null);
                        $followerProject = $follower->administration?->project;
                    @endphp
                    <div class="follower-item">
                        <div class="follower-info">
                            <div class="follower-name">{{ $follower->displayName() }}</div>
                            @if ($isEmployeeFollower)
                                <div class="follower-position">
                                    {{ $followerPosition ?? 'No Position' }}
                                </div>
                            @endif
                            <div class="follower-meta">
                                <span class="follower-nik"><i class="fas fa-id-card"></i>
                                    {{ $follower->idLabel() }}: {{ $follower->nik ?? 'N/A' }}</span>
                                @if ($isEmployeeFollower)
                                    <span class="follower-department"><i class="fas fa-sitemap"></i>
                                        {{ $followerDepartment ?? 'No Department' }}</span>
                                @endif
                                @if ($follower->phone_number)
                                    <span class="follower-phone"><i class="fas fa-phone"></i>
                                        {{ $follower->phone_number }}</span>
                                @endif
                            </div>
                            @if ($isEmployeeFollower)
                                <div class="follower-project">
                                    <i class="fas fa-project-diagram"></i>
                                    @if ($followerProject)
                                        {{ $followerProject->project_code ?? 'No Code' }} :
                                        {{ $followerProject->project_name ?? 'No Project' }}
                                    @else
                                        {{ $follower->project ?? 'No Project' }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
