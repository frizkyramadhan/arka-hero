@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="candidate-header">
            <div class="candidate-header-content">
                <div class="candidate-number">{{ $candidate->candidate_number }}</div>
                <h1 class="candidate-name">{{ $candidate->fullname }}</h1>
                <div class="candidate-email">
                    <i class="fas fa-envelope"></i> {{ $candidate->email }}
                </div>
                @php
                    $statusBadges = [
                        'available' => [
                            'label' => 'Available',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'in_process' => [
                            'label' => 'In Process',
                            'class' => 'badge badge-warning',
                            'icon' => 'fa-clock',
                        ],
                        'hired' => ['label' => 'Hired', 'class' => 'badge badge-info', 'icon' => 'fa-user-check'],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'blacklisted' => ['label' => 'Blacklisted', 'class' => 'badge badge-dark', 'icon' => 'fa-ban'],
                    ];
                    $status = $candidate->global_status;
                    $pill = $statusBadges[$status] ?? [
                        'label' => ucfirst($status),
                        'class' => 'badge badge-secondary',
                        'icon' => 'fa-question-circle',
                    ];
                @endphp
                <div class="candidate-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="candidate-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Main Candidate Info -->
                    <div class="candidate-card candidate-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user"></i> Candidate Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Phone Number</div>
                                        <div class="info-value">{{ $candidate->phone }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Date of Birth</div>
                                        <div class="info-value">
                                            {{ $candidate->date_of_birth ? $candidate->date_of_birth->format('d F Y') : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Education Level</div>
                                        <div class="info-value">{{ $candidate->education_level }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Experience</div>
                                        <div class="info-value">{{ $candidate->experience_years }} years</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Current Salary</div>
                                        <div class="info-value">
                                            {{ $candidate->current_salary ? 'Rp ' . number_format($candidate->current_salary, 0, ',', '.') : 'Not specified' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-coins"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Expected Salary</div>
                                        <div class="info-value">
                                            {{ $candidate->expected_salary ? 'Rp ' . number_format($candidate->expected_salary, 0, ',', '.') : 'Not specified' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5><i class="fas fa-map-marker-alt mr-2"></i>Address</h5>
                                    <p class="text-muted">{{ $candidate->address }}</p>
                                </div>
                            </div>

                            @if ($candidate->position_applied)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5><i class="fas fa-briefcase mr-2"></i>Position Applied For</h5>
                                        <p class="text-muted">{{ $candidate->position_applied }}</p>
                                    </div>
                                </div>
                            @endif

                            @if ($candidate->remarks)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5><i class="fas fa-comment mr-2"></i>Remarks</h5>
                                        <p class="text-muted">{{ $candidate->remarks }}</p>
                                    </div>
                                </div>
                            @endif

                            @if ($candidate->skills)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5><i class="fas fa-tools mr-2"></i>Skills & Competencies</h5>
                                        <p class="text-muted">{{ $candidate->skills }}</p>
                                    </div>
                                </div>
                            @endif

                            @if ($candidate->previous_companies)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5><i class="fas fa-building mr-2"></i>Previous Companies</h5>
                                        <p class="text-muted">{{ $candidate->previous_companies }}</p>
                                    </div>
                                </div>
                            @endif

                            @if ($candidate->global_status === 'blacklisted' && $candidate->blacklist_reason)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5><i class="fas fa-ban mr-2 text-danger"></i>Blacklist Reason</h5>
                                        <p class="text-danger">{{ $candidate->blacklist_reason }}</p>
                                        <small class="text-muted">
                                            Blacklisted on:
                                            {{ $candidate->blacklisted_at ? date('d/m/Y H:i', strtotime($candidate->blacklisted_at)) : '-' }}
                                        </small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recruitment Sessions Card -->
                    <div class="candidate-card sessions-card">
                        <div class="card-head">
                            <h2><i class="fas fa-list-alt"></i> Recruitment Sessions <span
                                    class="sessions-count">{{ $candidate->sessions->count() }}</span></h2>
                        </div>
                        <div class="card-body">
                            @if ($candidate->sessions->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Session Number</th>
                                                <th>FPTK Number</th>
                                                <th>Position</th>
                                                <th>Department</th>
                                                <th>Status</th>
                                                <th>Applied Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($candidate->sessions as $session)
                                                <tr>
                                                    <td>{{ $session->session_number }}</td>
                                                    <td>{{ $session->fptk->request_number }}</td>
                                                    <td>{{ $session->fptk->position->name }}</td>
                                                    <td>{{ $session->fptk->department->name }}</td>
                                                    <td>
                                                        @php
                                                            $sessionStatusBadges = [
                                                                'active' => 'badge-primary',
                                                                'in_process' => 'badge-warning',
                                                                'completed' => 'badge-success',
                                                                'rejected' => 'badge-danger',
                                                                'withdrawn' => 'badge-secondary',
                                                            ];
                                                            $sessionStatusClass =
                                                                $sessionStatusBadges[$session->status] ?? 'badge-light';
                                                        @endphp
                                                        <span
                                                            class="badge {{ $sessionStatusClass }}">{{ ucfirst($session->status) }}</span>
                                                    </td>
                                                    <td>{{ $session->applied_date ? $session->applied_date->format('d/m/Y') : '-' }}
                                                    </td>
                                                    <td>
                                                        <a href="{{ route('recruitment.sessions.show', $session->id) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No recruitment sessions found for this candidate.</p>
                                    @if ($candidate->global_status === 'available')
                                        <button type="button" class="btn btn-primary btn-apply"
                                            data-id="{{ $candidate->id }}">
                                            <i class="fas fa-plus mr-2"></i>Apply to FPTK
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Action Buttons -->
                    <div class="candidate-action-buttons">
                        <a href="{{ route('recruitment.candidates.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        @can('recruitment-candidates.edit')
                            <a href="{{ route('recruitment.candidates.edit', $candidate->id) }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endcan

                        @if ($candidate->cv_file_path)
                            <div class="btn-group">
                                <a href="{{ route('recruitment.candidates.download-cv', $candidate->id) }}"
                                    class="btn-action download-btn">
                                    <i class="fas fa-download"></i> Download CV
                                </a>
                                <button type="button"
                                    class="btn-action download-btn dropdown-toggle dropdown-toggle-split"
                                    data-toggle="dropdown" aria-expanded="false">
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <div class="dropdown-menu">
                                    <form action="{{ route('recruitment.candidates.delete-cv', $candidate->id) }}"
                                        method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dropdown-item text-danger"
                                            onclick="return confirm('Are you sure you want to delete this CV file?')">
                                            <i class="fas fa-trash mr-2"></i> Delete CV
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif

                        @if ($candidate->global_status === 'available')
                            <button type="button" class="btn-action apply-btn btn-apply"
                                data-id="{{ $candidate->id }}">
                                <i class="fas fa-plus"></i> Apply to FPTK
                            </button>
                        @endif

                        @if ($candidate->global_status !== 'blacklisted')
                            <button type="button" class="btn-action btn-dark btn-blacklist"
                                data-id="{{ $candidate->id }}">
                                <i class="fas fa-ban"></i> Blacklist
                            </button>
                        @else
                            <form action="{{ route('recruitment.candidates.remove-from-blacklist', $candidate->id) }}"
                                method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action unblacklist-btn"
                                    onclick="return confirm('Are you sure you want to remove this candidate from blacklist?')"
                                    style="width: 100%;">
                                    <i class="fas fa-user-check"></i> Remove from Blacklist
                                </button>
                            </form>
                        @endif

                        @can('recruitment-candidates.delete')
                            <form action="{{ route('recruitment.candidates.destroy', $candidate->id) }}" method="POST"
                                style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this candidate? This action cannot be undone.')"
                                    style="width: 100%;">
                                    <i class="fas fa-trash"></i> Delete Candidate
                                </button>
                            </form>
                        @endcan

                        <a href="{{ route('recruitment.candidates.print', $candidate->id) }}"
                            class="btn-action print-btn" target="_blank">
                            <i class="fas fa-print"></i> Print
                        </a>
                    </div>
                    <br>
                    <!-- Statistics Card -->
                    <div class="candidate-card stats-card">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-bar"></i> Statistics</h2>
                        </div>
                        <div class="card-body">
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #3498db;">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value">{{ $candidate->sessions->count() }}</div>
                                        <div class="stat-label">Applications</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #27ae60;">
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-value">
                                            @php
                                                $totalSessions = $candidate->sessions->count();
                                                $successfulSessions = $candidate->sessions
                                                    ->where('status', 'completed')
                                                    ->count();
                                                $successRate =
                                                    $totalSessions > 0
                                                        ? round(($successfulSessions / $totalSessions) * 100, 1)
                                                        : 0;
                                            @endphp
                                            {{ $successRate }}%
                                        </div>
                                        <div class="stat-label">Success Rate</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Apply to FPTK Modal -->
        <div class="modal fade" id="applyModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Apply to FPTK</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="applyForm">
                        <div class="modal-body">
                            @csrf
                            <input type="hidden" id="apply_candidate_id" name="candidate_id">
                            <div class="form-group">
                                <label for="fptk_id">Select FPTK *</label>
                                <select class="form-control select2bs4" id="fptk_id" name="fptk_id" required>
                                    <option value="">Select FPTK</option>
                                    @foreach ($availableFptks as $fptk)
                                        <option value="{{ $fptk->id }}">
                                            {{ $fptk->request_number }} - {{ $fptk->position->position_name }}
                                            ({{ $fptk->department->department_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="apply_source">Application Source *</label>
                                <select class="form-control" id="apply_source" name="source" required>
                                    <option value="">Select Source</option>
                                    <option value="website">Website</option>
                                    <option value="referral">Referral</option>
                                    <option value="job_portal">Job Portal</option>
                                    <option value="social_media">Social Media</option>
                                    <option value="walk_in">Walk In</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="cover_letter">Cover Letter</label>
                                <textarea class="form-control" id="cover_letter" name="cover_letter" rows="3"
                                    placeholder="Enter cover letter (optional)"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Blacklist Modal -->
        <div class="modal fade" id="blacklistModal">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Blacklist Candidate</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="blacklistForm" action="" method="POST">
                        <div class="modal-body">
                            @csrf
                            <div class="form-group">
                                <label for="blacklist_reason">Blacklist Reason *</label>
                                <textarea class="form-control" id="blacklist_reason" name="blacklist_reason" rows="3"
                                    placeholder="Enter blacklist reason" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-danger">Blacklist</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endsection

    @section('styles')
        <!-- Select2 -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

        <style>
            /* Custom Styles for Candidate Detail */
            .content-wrapper-custom {
                background-color: #f8fafc;
                min-height: 100vh;
                padding-bottom: 40px;
            }

            /* Header */
            .candidate-header {
                position: relative;
                height: 120px;
                color: white;
                padding: 20px 30px;
                margin-bottom: 30px;
                background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }

            .candidate-header-content {
                position: relative;
                z-index: 2;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .candidate-number {
                font-size: 13px;
                margin-bottom: 4px;
                opacity: 0.9;
                letter-spacing: 1px;
            }

            .candidate-name {
                font-size: 24px;
                font-weight: 600;
                margin-bottom: 8px;
            }

            .candidate-email {
                font-size: 14px;
                opacity: 0.9;
            }

            .candidate-status-pill {
                position: absolute;
                top: 20px;
                right: 20px;
            }

            .candidate-status-pill .badge {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
                border-radius: 0.375rem;
                font-weight: 500;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }

            /* Content Styles */
            .candidate-content {
                padding: 0 20px;
            }

            /* Cards */
            .candidate-card {
                background: white;
                border-radius: 6px;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
            }

            .card-head {
                padding: 15px 20px;
                border-bottom: 1px solid #e9ecef;
                background-color: #f8f9fa;
            }

            .card-head h2 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #2c3e50;
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .card-body {
                padding: 20px;
            }

            /* Info Grid */
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                padding: 20px;
            }

            .info-item {
                display: flex;
                align-items: flex-start;
                gap: 12px;
            }

            .info-icon {
                width: 32px;
                height: 32px;
                border-radius: 4px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 14px;
                background-color: #3498db;
            }

            .info-content {
                flex: 1;
            }

            .info-label {
                font-size: 12px;
                color: #777;
                margin-bottom: 4px;
            }

            .info-value {
                font-weight: 600;
                color: #333;
            }

            /* Sessions Card */
            .sessions-count {
                background: #3498db;
                color: white;
                font-size: 14px;
                border-radius: 4px;
                padding: 2px 8px;
                margin-left: 8px;
            }

            /* Stats Grid */
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }

            .stat-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 6px;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 16px;
            }

            .stat-content {
                flex: 1;
            }

            .stat-value {
                font-size: 18px;
                font-weight: 600;
                color: #2c3e50;
                line-height: 1;
            }

            .stat-label {
                font-size: 12px;
                color: #777;
                margin-top: 2px;
            }

            /* Action Buttons */
            .candidate-action-buttons {
                display: grid;
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .btn-action {
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 10px 16px;
                border-radius: 4px;
                font-weight: 500;
                font-size: 14px;
                transition: all 0.2s;
                gap: 8px;
                color: white;
                text-decoration: none;
                border: none;
                cursor: pointer;
            }

            .back-btn {
                background-color: #64748b;
            }

            .back-btn:hover {
                color: white;
            }

            .edit-btn {
                background-color: #3498db;
            }

            .edit-btn:hover {
                color: white;
            }

            .download-btn {
                background-color: #27ae60;
            }

            .download-btn:hover {
                color: white;
            }

            /* Split Button Styling */
            .btn-group {
                display: flex;
                width: 100%;
            }

            .btn-group .btn-action {
                flex: 1;
                border-radius: 4px 0 0 4px;
            }

            .btn-group .dropdown-toggle-split {
                flex: 0 0 auto;
                border-radius: 0 4px 4px 0;
                border-left: 1px solid rgba(255, 255, 255, 0.2);
            }

            .btn-group .dropdown-menu {
                min-width: 120px;
            }

            .btn-group .dropdown-item {
                padding: 8px 12px;
                font-size: 14px;
            }

            .btn-group .dropdown-item:hover {
                background-color: #f8f9fa;
            }

            .btn-group .dropdown-item.text-danger:hover {
                background-color: #fee;
            }

            .apply-btn {
                background-color: #8e44ad;
            }

            .apply-btn:hover {
                color: white;
            }

            .blacklist-btn {
                background-color: #333;
            }

            .delete-btn {
                background-color: #e74c3c;
            }

            .blacklist-btn:hover,
            .delete-btn:hover {
                color: white;
            }

            .unblacklist-btn {
                background-color: #f39c12;
            }

            .unblacklist-btn:hover {
                color: white;
            }

            .print-btn {
                background-color: #007bff;
            }

            .print-btn:hover {
                color: white;
            }

            .btn-action:hover {
                opacity: 0.9;
                transform: translateY(-1px);
            }

            /* Responsive Adjustments */
            @media (max-width: 992px) {
                .info-grid {
                    grid-template-columns: 1fr;
                }

                .candidate-content .row {
                    display: flex;
                    flex-direction: column;
                }

                .candidate-content .col-lg-8 {
                    order: 1;
                    width: 100%;
                }

                .candidate-content .col-lg-4 {
                    order: 2;
                    width: 100%;
                }

                .candidate-card {
                    margin-bottom: 20px;
                }

                .candidate-content {
                    padding: 0 15px;
                }
            }

            @media (max-width: 768px) {
                .candidate-header {
                    height: auto;
                    padding: 15px;
                    position: relative;
                }

                .candidate-header-content {
                    padding-right: 80px;
                }

                .candidate-name {
                    font-size: 20px;
                }

                .candidate-status-pill {
                    position: absolute;
                    top: 15px;
                    right: 15px;
                    margin-top: 0;
                    align-self: flex-start;
                }

                .card-body {
                    padding: 15px;
                }

                .info-item {
                    padding: 10px 0;
                }

                .stats-grid {
                    grid-template-columns: 1fr;
                }
            }

            /* Preserve desktop layout above 992px */
            @media (min-width: 993px) {
                .candidate-content .row {
                    display: flex;
                    flex-wrap: wrap;
                }

                .candidate-content .col-lg-8 {
                    flex: 0 0 66.666667%;
                    max-width: 66.666667%;
                }

                .candidate-content .col-lg-4 {
                    flex: 0 0 33.333333%;
                    max-width: 33.333333%;
                }
            }
        </style>
    @endsection

    @section('scripts')
        <!-- Select2 -->
        <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

        <script>
            $(function() {
                // Initialize Select2
                $('.select2bs4').select2({
                    theme: 'bootstrap4'
                });

                // Modal functionality
                $(document).on('click', '.btn-apply', function() {
                    var id = $(this).data('id');
                    $('#apply_candidate_id').val(id);
                    $('#applyModal').modal('show');
                });

                $(document).on('click', '.btn-blacklist', function() {
                    var id = $(this).data('id');
                    $('#blacklistForm').attr('action', "{{ route('recruitment.candidates.blacklist', ':id') }}"
                        .replace(':id', id));
                    $('#blacklistModal').modal('show');
                });

                // Form submissions
                $('#applyForm').on('submit', function(e) {
                    e.preventDefault();
                    var formData = $(this).serialize();
                    var id = $('#apply_candidate_id').val();

                    $.ajax({
                        url: "{{ route('recruitment.candidates.apply-to-fptk', ':id') }}".replace(
                            ':id', id),
                        type: 'POST',
                        data: formData,
                        success: function(response) {
                            $('#applyModal').modal('hide');
                            location.reload();
                            toastr.success('Candidate applied to FPTK successfully');
                        },
                        error: function(xhr) {
                            toastr.error('Error applying candidate to FPTK');
                        }
                    });
                });

                $('#blacklistForm').on('submit', function(e) {
                    // Let the form submit normally since controller returns redirect
                    $('#blacklistModal').modal('hide');
                });
            });
        </script>
    @endsection
