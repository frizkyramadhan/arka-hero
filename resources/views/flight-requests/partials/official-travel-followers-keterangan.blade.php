@if (
    $flightRequest->request_type === \App\Models\FlightRequest::TYPE_TRAVEL_BASED &&
        $flightRequest->officialTravel &&
        $flightRequest->officialTravel->details->isNotEmpty())
    @if (! empty($forPrint))
        <div style="margin-bottom: 12px;">
            <div class="info-row" style="margin-bottom: 6px;">
                <span class="label">Follower/s</span>
                <span class="colon">:</span>
            </div>
            <table class="flight-table" style="margin-top: 0;">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 8%;">No.</th>
                        <th style="text-align: center;">Name / NIK</th>
                        <th style="text-align: center;">Title</th>
                        <th style="text-align: center;">Business Unit</th>
                        <th style="text-align: center;">Department</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($flightRequest->officialTravel->details as $key => $detail)
                        <tr>
                            <td style="text-align: center;">{{ $key + 1 }}</td>
                            <td>{{ $detail->follower->employee->fullname ?? 'N/A' }} /
                                {{ $detail->follower->nik ?? 'N/A' }}</td>
                            <td>{{ $detail->follower->position->position_name ?? 'N/A' }}</td>
                            <td>{{ $detail->follower->project->project_name ?? 'N/A' }}</td>
                            <td>{{ $detail->follower->position->department->department_name ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="flight-request-card followers-card flight-request-lot-followers mb-4">
            <div class="card-head">
                <h2><i class="fas fa-users"></i> Followers <span
                        class="followers-count">{{ $flightRequest->officialTravel->details->count() }}</span></h2>
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
                                    {{ $detail->follower->position->position_name ?? 'No Position' }}</div>
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

        @push('styles')
            <style>
                /* Match official travel detail — followers card */
                .followers-card {
                    overflow: hidden;
                }

                .followers-count {
                    background: #3498db;
                    color: white;
                    font-size: 14px;
                    border-radius: 4px;
                    padding: 2px 8px;
                    margin-left: 8px;
                }

                .followers-list {
                    max-height: 400px;
                    overflow-y: auto;
                }

                .follower-item {
                    padding: 15px;
                    border-bottom: 1px solid #edf2f7;
                }

                .follower-name {
                    font-size: 16px;
                    font-weight: 500;
                    color: #2c3e50;
                    margin-bottom: 4px;
                }

                .follower-position {
                    font-size: 15px;
                    color: #64748b;
                    margin-bottom: 6px;
                }

                .follower-meta {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 15px;
                    font-size: 14px;
                    color: #64748b;
                    margin-bottom: 6px;
                }

                .follower-nik,
                .follower-department {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }

                .follower-project {
                    font-size: 14px;
                    color: #64748b;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                }

                .follower-meta i,
                .follower-project i {
                    font-size: 14px;
                    width: 16px;
                    text-align: center;
                }
            </style>
        @endpush
    @endif
@endif
