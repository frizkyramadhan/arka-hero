@php
    $printDate = date('d F Y');
    $printTime = date('H:i:s');
@endphp

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Candidate Detail - {{ $candidate->fullname }}</title>
        <style>
            /* Print-specific styles */
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                font-family: 'Roboto', 'Helvetica Neue', sans-serif;
                line-height: 1.3;
                color: #333;
                background: #fff;
                font-size: 10px;
                margin: 0;
                padding: 0;
            }

            .container {
                max-width: 100%;
                padding: 0;
            }

            .print-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 0.75rem 0;
                border-bottom: 1px solid #eaeaea;
                margin-bottom: 1rem;
            }

            .header-title {
                text-align: left;
            }

            .header-title h1 {
                color: #1a237e;
                margin: 0;
                font-size: 18px;
                font-weight: 400;
            }

            .header-title p {
                color: #757575;
                margin: 0.15rem 0 0 0;
                font-size: 10px;
            }

            .header-meta {
                text-align: right;
                font-size: 9px;
                color: #757575;
            }

            .document-id {
                display: inline-block;
                padding: 0.15rem 0.3rem;
                background-color: #f5f5f5;
                border-radius: 2px;
                margin-top: 0.25rem;
                font-size: 8px;
                font-family: monospace;
                letter-spacing: 0.5px;
            }

            .profile-section {
                display: flex;
                margin-bottom: 1rem;
                gap: 1rem;
                padding-bottom: 0.75rem;
                border-bottom: 1px solid #eaeaea;
            }

            .profile-image {
                flex: 0 0 100px;
            }

            .profile-image img {
                width: 100%;
                height: auto;
                border-radius: 3px;
                border: 1px solid #eaeaea;
            }

            .profile-info {
                flex: 1;
                display: flex;
                flex-direction: column;
            }

            .profile-info h2 {
                color: #1a237e;
                margin: 0 0 0.25rem 0;
                font-size: 16px;
                font-weight: 500;
            }

            .profile-info .subtitle {
                color: #757575;
                margin: 0 0 0.5rem 0;
                font-size: 11px;
                font-weight: 400;
            }

            .profile-meta {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
                font-size: 9px;
            }

            .profile-meta-item {
                display: flex;
                align-items: baseline;
            }

            .profile-meta-label {
                color: #757575;
                margin-right: 0.3rem;
                width: 70px;
                flex-shrink: 0;
            }

            .profile-meta-value {
                color: #212121;
                font-weight: 500;
            }

            .section {
                margin-bottom: 1rem;
                page-break-inside: avoid;
            }

            .section-header {
                display: flex;
                align-items: center;
                margin-bottom: 0.5rem;
                border-bottom: 1px solid #eaeaea;
                padding-bottom: 0.25rem;
            }

            .section-header h2 {
                color: #1a237e;
                margin: 0;
                font-size: 12px;
                font-weight: 500;
                position: relative;
                padding-left: 8px;
            }

            .section-header h2::before {
                content: "";
                position: absolute;
                left: 0;
                top: 2px;
                height: 80%;
                width: 3px;
                background-color: #1a237e;
                border-radius: 1px;
            }

            .content-box {
                background-color: #fafafa;
                border-radius: 3px;
                padding: 0.6rem;
            }

            .info-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.6rem;
            }

            .info-item {
                margin-bottom: 0.4rem;
            }

            .info-label {
                font-weight: 500;
                color: #616161;
                font-size: 9px;
                margin-bottom: 0.1rem;
            }

            .info-value {
                color: #212121;
                font-size: 10px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 0;
                font-size: 9px;
            }

            th {
                background-color: #f5f5f5;
                color: #616161;
                font-weight: 500;
                text-align: left;
                padding: 0.4rem 0.5rem;
                border-bottom: 1px solid #eaeaea;
            }

            td {
                padding: 0.35rem 0.5rem;
                border-bottom: 1px solid #eaeaea;
                color: #212121;
            }

            tr:last-child td {
                border-bottom: none;
            }

            tr:nth-child(even) {
                background-color: #fafafa;
            }

            .badge {
                display: inline-block;
                padding: 0.15em 0.3em;
                font-size: 8px;
                font-weight: 500;
                line-height: 1;
                text-align: center;
                white-space: nowrap;
                vertical-align: baseline;
                border-radius: 2px;
            }

            .badge-success {
                background-color: #e8f5e9;
                color: #2e7d32;
            }

            .badge-danger {
                background-color: #ffebee;
                color: #c62828;
            }

            .badge-warning {
                background-color: #fff8e1;
                color: #ff8f00;
            }

            .badge-info {
                background-color: #e3f2fd;
                color: #1565c0;
            }

            .badge-dark {
                background-color: #424242;
                color: #ffffff;
            }

            .empty-state {
                text-align: center;
                padding: 0.5rem;
                color: #9e9e9e;
                font-style: italic;
                background: #f5f5f5;
                border-radius: 3px;
                font-size: 9px;
            }

            .footer {
                margin-top: 1rem;
                padding-top: 0.5rem;
                border-top: 1px solid #eaeaea;
                display: flex;
                justify-content: space-between;
                font-size: 8px;
                color: #9e9e9e;
            }

            @media print {
                body {
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .no-print {
                    display: none;
                }

                .page-break {
                    page-break-before: always;
                }

                .section {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="print-header">
                <div class="header-title">
                    <h1>Recruitment Candidate Profile</h1>
                    <p>Complete candidate information and application details</p>
                </div>
                <div class="header-meta">
                    <div>Generated on {{ $printDate }} at {{ $printTime }}</div>
                    <div class="document-id">{{ $candidate->candidate_number }}</div>
                </div>
            </div>

            <div class="profile-section">
                <div class="profile-image">
                    <img src="{{ asset('assets/dist/img/avatar6.png') }}" alt="Default Profile Picture">
                </div>
                <div class="profile-info">
                    <h2>{{ $candidate->fullname }}</h2>
                    <p class="subtitle">{{ $candidate->position_applied ?? 'No Position Applied' }} ·
                        {{ $candidate->education_level }}</p>

                    <div class="profile-meta">
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Status</span>
                            <span class="profile-meta-value">
                                @php
                                    $statusBadges = [
                                        'available' => 'Available',
                                        'in_process' => 'In Process',
                                        'hired' => 'Hired',
                                        'rejected' => 'Rejected',
                                        'blacklisted' => 'Blacklisted',
                                    ];
                                    $status =
                                        $statusBadges[$candidate->global_status] ?? ucfirst($candidate->global_status);
                                @endphp
                                {{ $status }}
                            </span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Experience</span>
                            <span class="profile-meta-value">{{ $candidate->experience_years }} years</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Age</span>
                            <span
                                class="profile-meta-value">{{ $candidate->date_of_birth ? $candidate->date_of_birth->age . ' years' : '-' }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Applications</span>
                            <span class="profile-meta-value">{{ $candidate->sessions->count() }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Registration</span>
                            <span class="profile-meta-value">{{ $candidate->created_at->format('d-M-Y') }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">CV Available</span>
                            <span class="profile-meta-value">{{ $candidate->cv_file_path ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Personal & Contact Information</h2>
                </div>
                <div class="content-box">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $candidate->email }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value">{{ $candidate->phone }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date of Birth</div>
                            <div class="info-value">
                                {{ $candidate->date_of_birth ? $candidate->date_of_birth->format('d-M-Y') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $candidate->address }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Education Level</div>
                            <div class="info-value">{{ $candidate->education_level }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Position Applied</div>
                            <div class="info-value">{{ $candidate->position_applied ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Professional Information</h2>
                </div>
                <div class="content-box">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Current Salary</div>
                            <div class="info-value">
                                {{ $candidate->current_salary ? 'Rp ' . number_format($candidate->current_salary, 0, ',', '.') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Expected Salary</div>
                            <div class="info-value">
                                {{ $candidate->expected_salary ? 'Rp ' . number_format($candidate->expected_salary, 0, ',', '.') : '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Skills</div>
                            <div class="info-value">{{ $candidate->skills ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Previous Companies</div>
                            <div class="info-value">{{ $candidate->previous_companies ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Remarks</div>
                            <div class="info-value">{{ $candidate->remarks ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($candidate->global_status === 'blacklisted' && $candidate->blacklist_reason)
                <div class="section">
                    <div class="section-header">
                        <h2>Blacklist Information</h2>
                    </div>
                    <div class="content-box">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">Blacklist Reason</div>
                                <div class="info-value" style="color: #c62828;">{{ $candidate->blacklist_reason }}
                                </div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Blacklisted On</div>
                                <div class="info-value">
                                    {{ $candidate->blacklisted_at ? $candidate->blacklisted_at->format('d-M-Y H:i') : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="section">
                <div class="section-header">
                    <h2>Recruitment Applications</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Session No.</th>
                            <th>FPTK Number</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Applied Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($candidate->sessions->isEmpty())
                            <tr>
                                <td colspan="6" class="empty-state">No recruitment applications found</td>
                            </tr>
                        @else
                            @foreach ($candidate->sessions as $session)
                                <tr>
                                    <td>{{ $session->session_number }}</td>
                                    <td>{{ $session->fptk->request_number }}</td>
                                    <td>{{ $session->fptk->position->name }}</td>
                                    <td>{{ $session->fptk->department->name }}</td>
                                    <td>
                                        @php
                                            $sessionStatusBadges = [
                                                'active' => 'badge-info',
                                                'in_process' => 'badge-warning',
                                                'completed' => 'badge-success',
                                                'rejected' => 'badge-danger',
                                                'withdrawn' => 'badge-dark',
                                            ];
                                            $sessionStatusClass =
                                                $sessionStatusBadges[$session->status] ?? 'badge-dark';
                                        @endphp
                                        <span
                                            class="badge {{ $sessionStatusClass }}">{{ ucfirst($session->status) }}</span>
                                    </td>
                                    <td>{{ $session->applied_date ? $session->applied_date->format('d-M-Y') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <div>© {{ date('Y') }} Your Company Name. All rights reserved.</div>
                <div>Document generated automatically · No signature required</div>
            </div>
        </div>
    </body>

</html>
