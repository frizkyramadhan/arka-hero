@php
    $printDate = date('d F Y');
    $printTime = date('H:i:s');
@endphp

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Employee Detail - {{ $employee->fullname }}</title>
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
                    <h1>Employee Profile</h1>
                    <p>Complete information and employment details</p>
                </div>
                <div class="header-meta">
                    <div>Generated on {{ $printDate }} at {{ $printTime }}</div>
                    <div class="document-id">ID: {{ uniqid() }}</div>
                </div>
            </div>

            <div class="profile-section">
                <div class="profile-image">
                    @if ($profile)
                        <img src="{{ asset('images/' . $profile->employee_id . '/' . $profile->filename) }}"
                            alt="Profile Picture">
                    @else
                        <img src="{{ asset('assets/dist/img/avatar6.png') }}" alt="Default Profile Picture">
                    @endif
                </div>
                <div class="profile-info">
                    <h2>{{ $employee->fullname }}</h2>
                    <p class="subtitle">{{ $administrations->first()->position_name ?? 'N/A' }} ·
                        {{ $administrations->first()->department_name ?? 'N/A' }}</p>

                    <div class="profile-meta">
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Employee ID</span>
                            <span class="profile-meta-value">{{ $employee->id }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">ID Card</span>
                            <span class="profile-meta-value">{{ $employee->identity_card }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Date of Birth</span>
                            <span
                                class="profile-meta-value">{{ date('d-M-Y', strtotime($employee->emp_dob)) ?? '-' }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Gender</span>
                            <span
                                class="profile-meta-value">{{ $employee->gender == 'male' ? 'Male' : 'Female' }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Blood Type</span>
                            <span class="profile-meta-value">{{ $employee->blood_type ?? '-' }}</span>
                        </div>
                        <div class="profile-meta-item">
                            <span class="profile-meta-label">Nationality</span>
                            <span class="profile-meta-value">{{ $employee->nationality ?? '-' }}</span>
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
                            <div class="info-label">Religion</div>
                            <div class="info-value">{{ $employee->religion->religion_name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Marital Status</div>
                            <div class="info-value">{{ $employee->marital ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Place of Birth</div>
                            <div class="info-value">{{ $employee->emp_pob ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $employee->address ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Village/Ward</div>
                            <div class="info-value">{{ $employee->village ?? '-' }} / {{ $employee->ward ?? '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">District/City</div>
                            <div class="info-value">{{ $employee->district ?? '-' }} / {{ $employee->city ?? '-' }}
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value">{{ $employee->phone ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $employee->email ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Employment History</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>NIK</th>
                            <th>POH/DOH</th>
                            <th>Department</th>
                            <th>Position</th>
                            <th>Project</th>
                            <th>Class</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($administrations->isEmpty())
                            <tr>
                                <td colspan="7" class="empty-state">No employment history available</td>
                            </tr>
                        @else
                            @foreach ($administrations as $administration)
                                <tr>
                                    <td>
                                        @if ($administration->is_active == 1)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $administration->nik }}</td>
                                    <td>{{ $administration->poh }}/{{ date('d-M-Y', strtotime($administration->doh)) }}
                                    </td>
                                    <td>{{ $administration->department_name }}</td>
                                    <td>{{ $administration->position_name }}</td>
                                    <td>{{ $administration->project_code }}</td>
                                    <td>{{ $administration->class }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Account & Tax Information</h2>
                </div>
                <div class="content-box">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Bank</div>
                            <div class="info-value">{{ $bank->banks->bank_name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Account No.</div>
                            <div class="info-value">{{ $bank->bank_account_no ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Account Name</div>
                            <div class="info-value">{{ $bank->bank_account_name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Branch</div>
                            <div class="info-value">{{ $bank->bank_account_branch ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tax ID No.</div>
                            <div class="info-value">{{ $tax->tax_no ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Tax Valid Date</div>
                            <div class="info-value">{{ $tax ? date('d-M-Y', strtotime($tax->tax_valid_date)) : '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Health Insurance</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Insurance</th>
                            <th>Insurance No</th>
                            <th>Health Facility</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($insurances->isEmpty())
                            <tr>
                                <td colspan="4" class="empty-state">No insurance information available</td>
                            </tr>
                        @else
                            @foreach ($insurances as $insurance)
                                <tr>
                                    <td>{{ $insurance->health_insurance_type == 'bpjskt' ? 'BPJS Ketenagakerjaan' : 'BPJS Kesehatan' }}
                                    </td>
                                    <td>{{ $insurance->health_insurance_no }}</td>
                                    <td>{{ $insurance->health_facility }}</td>
                                    <td>{{ $insurance->health_insurance_remarks }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Licenses</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>License Type</th>
                            <th>License No</th>
                            <th>Validity Period</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($licenses->isEmpty())
                            <tr>
                                <td colspan="3" class="empty-state">No license information available</td>
                            </tr>
                        @else
                            @foreach ($licenses as $license)
                                <tr>
                                    <td>{{ $license->driver_license_type }}</td>
                                    <td>{{ $license->driver_license_no }}</td>
                                    <td>{{ $license->driver_license_exp ? date('d-M-Y', strtotime($license->driver_license_exp)) : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Family Information</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Relationship</th>
                            <th>Name</th>
                            <th>Birth Place/Date</th>
                            <th>BPJS Kesehatan</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($families->isEmpty())
                            <tr>
                                <td colspan="5" class="empty-state">No family information available</td>
                            </tr>
                        @else
                            @foreach ($families as $family)
                                <tr>
                                    <td>{{ $family->family_relationship }}</td>
                                    <td>{{ $family->family_name }}</td>
                                    <td>{{ $family->family_birthplace }},
                                        {{ date('d-M-Y', strtotime($family->family_birthdate)) }}</td>
                                    <td>{{ $family->bpjsks_no }}</td>
                                    <td>{{ $family->family_remarks }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Education & Course History</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Year</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($educations->isEmpty() && $courses->isEmpty())
                            <tr>
                                <td colspan="5" class="empty-state">No education or course information available
                                </td>
                            </tr>
                        @else
                            @foreach ($educations as $education)
                                <tr>
                                    <td>Education</td>
                                    <td>{{ $education->education_name }}</td>
                                    <td>{{ $education->education_address }}</td>
                                    <td>{{ $education->education_year }}</td>
                                    <td>{{ $education->education_remarks }}</td>
                                </tr>
                            @endforeach
                            @foreach ($courses as $course)
                                <tr>
                                    <td>Course</td>
                                    <td>{{ $course->course_name }}</td>
                                    <td>{{ $course->course_address }}</td>
                                    <td>{{ $course->course_year }}</td>
                                    <td>{{ $course->course_remarks }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Job Experience</h2>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Position</th>
                            <th>Duration</th>
                            <th>Address</th>
                            <th>Quit Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($jobs->isEmpty())
                            <tr>
                                <td colspan="5" class="empty-state">No job experience information available</td>
                            </tr>
                        @else
                            @foreach ($jobs as $job)
                                <tr>
                                    <td>{{ $job->company_name }}</td>
                                    <td>{{ $job->job_position }}</td>
                                    <td>{{ $job->job_duration }}</td>
                                    <td>{{ $job->company_address }}</td>
                                    <td>{{ $job->quit_reason }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Operable Units & Emergency Contacts</h2>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Unit Name</th>
                                    <th>Type/Class</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($units->isEmpty())
                                    <tr>
                                        <td colspan="3" class="empty-state">No operable units</td>
                                    </tr>
                                @else
                                    @foreach ($units as $unit)
                                        <tr>
                                            <td>{{ $unit->unit_name }}</td>
                                            <td>{{ $unit->unit_type }}</td>
                                            <td>{{ $unit->unit_remarks }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Relation</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($emergencies->isEmpty())
                                    <tr>
                                        <td colspan="3" class="empty-state">No emergency contacts</td>
                                    </tr>
                                @else
                                    @foreach ($emergencies as $emergency)
                                        <tr>
                                            <td>{{ $emergency->emrg_call_relation }}</td>
                                            <td>{{ $emergency->emrg_call_name }}</td>
                                            <td>{{ $emergency->emrg_call_phone }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="section">
                <div class="section-header">
                    <h2>Additional Information</h2>
                </div>
                <div class="content-box">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label">Cloth Size</div>
                            <div class="info-value">{{ $additional->cloth_size ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Pants Size</div>
                            <div class="info-value">{{ $additional->pants_size ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Shoes Size</div>
                            <div class="info-value">{{ $additional->shoes_size ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Height</div>
                            <div class="info-value">{{ $additional->height ?? '-' }} cm</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Weight</div>
                            <div class="info-value">{{ $additional->weight ?? '-' }} kg</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Glasses</div>
                            <div class="info-value">{{ $additional->glasses ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer">
                <div>© {{ date('Y') }} Your Company Name. All rights reserved.</div>
                <div>Document generated automatically · No signature required</div>
            </div>
        </div>
    </body>

</html>
