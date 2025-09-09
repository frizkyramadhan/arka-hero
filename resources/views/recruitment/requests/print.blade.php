<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>FPTK - {{ $letterInfo['display_number'] ?? $fptk->request_number }}</title>
        <style>
            /* Print-specific styles */
            @media print {
                body {
                    margin: 0;
                }

                .no-print {
                    display: none !important;
                }

                .page-break {
                    page-break-after: always;
                }

                .page-break-inside {
                    page-break-inside: avoid;
                }
            }

            /* General styles */
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.6;
                color: #000;
                margin: 0;
                padding: 20px;
                background: white;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
            }

            /* Header */
            .header {
                margin-bottom: 30px;
            }

            .arka-logo {
                display: inline-block;
                background-color: #D2691E;
                color: white;
                padding: 8px 16px;
                font-weight: bold;
                font-size: 20px;
                margin-bottom: 20px;
            }

            .document-title {
                font-size: 16px;
                font-weight: bold;
                text-align: center;
                text-decoration: underline;
                margin: 20px 0 10px 0;
            }

            .document-number {
                font-size: 12px;
                text-align: center;
                margin-bottom: 30px;
            }

            /* Form fields */
            .form-field {
                margin-bottom: 8px;
                display: flex;
                align-items: center;
            }

            .form-field label {
                display: inline-block;
                width: 150px;
                font-weight: normal;
                margin-right: 10px;
            }

            .form-field .colon {
                margin-right: 10px;
            }

            .form-field .value {
                flex: 1;
                border-bottom: 1px solid #000;
                min-height: 20px;
                padding-bottom: 2px;
            }

            /* Checkbox alignment - make sure checkboxes start at the same position as form values */
            .checkbox-alignment {
                margin-left: 170px;
                /* 150px (label width) + 10px (colon margin) + 10px (colon to value spacing) */
            }

            /* Checkbox styles */
            .checkbox-group {
                margin: 5px 0;
            }

            .checkbox-item {
                display: inline-block;
                margin-right: 20px;
                margin-bottom: 3px;
            }

            .checkbox-item-vertical {
                display: block;
                margin-bottom: 3px;
            }

            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #000;
                margin-right: 5px;
                vertical-align: middle;
                position: relative;
            }

            /* Status Pekerjaan specific styling */
            .status-pekerjaan {
                display: flex;
                align-items: center;
                margin-bottom: 8px;
            }

            .status-pekerjaan label {
                width: 150px;
                margin-right: 10px;
            }

            .status-pekerjaan .colon {
                margin-right: 10px;
            }

            .status-pekerjaan .checkbox-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                margin-left: 0;
            }

            /* Alasan Permintaan specific styling */
            .alasan-permintaan {
                display: flex;
                align-items: flex-start;
                margin-bottom: 8px;
            }

            .alasan-permintaan label {
                width: 150px;
                margin-right: 10px;
            }

            .alasan-permintaan .colon {
                margin-right: 10px;
            }

            .alasan-permintaan .checkbox-container {
                display: flex;
                flex-direction: column;
                gap: 3px;
                margin-left: 0;
            }

            .checkbox.checked::after {
                content: '✓';
                position: absolute;
                top: -2px;
                left: 1px;
                font-size: 10px;
                font-weight: bold;
            }

            /* Job description section */
            .job-description {
                margin: 20px 0;
            }

            .job-description-title {
                font-weight: normal;
                margin-bottom: 10px;
            }

            .job-description-item {
                margin-bottom: 8px;
                display: flex;
            }

            .job-description-item .number {
                width: 20px;
                margin-right: 10px;
            }

            .job-description-item .line {
                flex: 1;
                border-bottom: 1px solid #000;
                min-height: 20px;
                padding-bottom: 2px;
            }

            /* Requirements section */
            .requirements {
                margin: 20px 0;
            }

            .requirements-title {
                font-weight: normal;
                margin-bottom: 10px;
            }

            .requirement-item {
                margin-bottom: 8px;
                display: flex;
            }

            .requirement-item .number {
                width: 20px;
                margin-right: 10px;
            }

            .requirement-item .label {
                width: 150px;
                margin-right: 10px;
            }

            .requirement-item .colon {
                margin-right: 10px;
            }

            .requirement-item .value {
                flex: 1;
                border-bottom: 1px solid #000;
                min-height: 20px;
                padding-bottom: 2px;
            }

            /* Requester section */
            .requester-section {
                margin: 30px 0;
            }

            .requester-field {
                margin-bottom: 8px;
                display: flex;
                align-items: center;
            }

            .requester-field label {
                width: 150px;
                margin-right: 10px;
            }

            .requester-field .colon {
                margin-right: 10px;
            }

            .requester-field .value {
                width: 200px;
                border-bottom: 1px solid #000;
                min-height: 20px;
                padding-bottom: 2px;
            }

            /* Approval section */
            .approval-section {
                margin-top: 40px;
                page-break-inside: avoid;
            }

            .approval-row {
                display: flex;
                justify-content: space-between;
                margin-bottom: 60px;
            }

            .approval-column {
                text-align: center;
                width: 30%;
            }

            .approval-title {
                margin-bottom: 40px;
                font-weight: normal;
            }

            .approval-signature {
                border-bottom: 1px solid #000;
                height: 40px;
                margin-bottom: 10px;
            }

            .approval-name {
                font-weight: normal;
            }

            /* Footer */
            .footer {
                margin-top: 50px;
                display: flex;
                justify-content: space-between;
                font-size: 10px;
                color: #000;
            }

            /* Print button */
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 10px 20px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
                z-index: 1000;
            }

            .print-button:hover {
                background: #0056b3;
            }

            @media print {
                .print-button {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        <button class="print-button no-print" onclick="window.print()">Print</button>

        <div class="container">
            <!-- Header -->
            <div class="header">
                <img src="{{ asset('images/logo_2.jpg') }}" alt="ARKA" style="width: 200px; height: auto;">
                <div class="document-title">FORMULIR PERMINTAAN TENAGA KERJA</div>
                <div class="document-number">{{ $letterInfo['display_number'] ?? $fptk->request_number }}</div>
            </div>

            <!-- Basic Information -->
            <div class="form-field">
                <label>Divisi / Departement</label>
                <span class="colon">:</span>
                <div class="value">{{ $fptk->department->department_name }}</div>
            </div>

            <div class="form-field">
                <label>Site Project</label>
                <span class="colon">:</span>
                <div class="value">{{ $fptk->project->project_code }} - {{ $fptk->project->project_name }}</div>
            </div>

            <div class="form-field">
                <label>Jabatan Diperlukan</label>
                <span class="colon">:</span>
                <div class="value">{{ $fptk->position->position_name }}</div>
            </div>

            <div class="form-field">
                <label>Jumlah Diperlukan</label>
                <span class="colon">:</span>
                <div class="value">{{ $fptk->required_qty }} {{ $fptk->required_qty > 1 ? 'persons' : 'person' }}</div>
            </div>

            <div class="form-field">
                <label>Waktu Diperlukan</label>
                <span class="colon">:</span>
                <div class="value">{{ date('d F Y', strtotime($fptk->required_date)) }}</div>
            </div>

            <!-- Employment Status -->
            <div class="form-field">
                <label>Status Pekerjaan</label>
                <span class="colon">:</span>
                <div class="checkbox-container" style="display: flex; gap: 20px;">
                    <div class="checkbox-item">
                        <span class="checkbox {{ $fptk->employment_type == 'pkwtt' ? 'checked' : '' }}"></span>
                        <span>PKWTT</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $fptk->employment_type == 'pkwt' ? 'checked' : '' }}"></span>
                        <span>PKWT</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $fptk->employment_type == 'harian' ? 'checked' : '' }}"></span>
                        <span>Harian</span>
                    </div>
                    <div class="checkbox-item">
                        <span class="checkbox {{ $fptk->employment_type == 'magang' ? 'checked' : '' }}"></span>
                        <span>Magang</span>
                    </div>
                </div>
            </div>

            <!-- Request Reason -->
            <div class="form-field" style="align-items: flex-start;">
                <label>Alasan Permintaan</label>
                <span class="colon">:</span>
                <div class="checkbox-container" style="display: flex; flex-direction: column; gap: 3px;">
                    <div class="checkbox-item-vertical">
                        <span class="checkbox {{ $fptk->request_reason == 'replacement' ? 'checked' : '' }}"></span>
                        <span>Pengganti Karyawan</span>
                    </div>
                    <div class="checkbox-item-vertical">
                        <span class="checkbox {{ $fptk->request_reason == 'additional' ? 'checked' : '' }}"></span>
                        <span>Penambahan Karyawan</span>
                    </div>
                    {{-- <div class="checkbox-item-vertical">
                        <span class="checkbox {{ $fptk->request_reason == 'other' ? 'checked' : '' }}"></span>
                        <span>Lain-lain / Sebutkan Alasannya ...</span>
                    </div>
                    @if ($fptk->request_reason == 'other' && $fptk->other_reason)
                        <div style="margin-top: 5px; margin-left: 17px;">
                            <span>{{ $fptk->other_reason }}</span>
                        </div>
                    @endif --}}
                </div>
            </div>

            <!-- Job Description -->
            <div class="job-description">
                <div class="job-description-title">Uraian Singkat Pekerjaan yang Dilakukan :</div>
                <div class="job-description-content"
                    style="border: 1px solid #000; padding: 10px; margin-top: 5px; min-height: 60px;">
                    {!! nl2br(e($fptk->job_description)) !!}
                </div>
            </div>

            <!-- Requirements -->
            <div class="requirements">
                <div class="requirements-title">Persyaratan</div>

                <div class="requirement-item">
                    <div class="number">1</div>
                    <div class="label">Jenis Kelamin</div>
                    <div class="colon">:</div>
                    <div class="value">{{ ucfirst($fptk->required_gender) }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">2</div>
                    <div class="label">Usia</div>
                    <div class="colon">:</div>
                    <div class="value">
                        @if ($fptk->required_age_min && $fptk->required_age_max)
                            {{ $fptk->required_age_min }} - {{ $fptk->required_age_max }} tahun
                        @elseif($fptk->required_age_min)
                            Min {{ $fptk->required_age_min }} tahun
                        @elseif($fptk->required_age_max)
                            Max {{ $fptk->required_age_max }} tahun
                        @endif
                    </div>
                </div>

                <div class="requirement-item">
                    <div class="number">3</div>
                    <div class="label">Status Perkawinan</div>
                    <div class="colon">:</div>
                    <div class="value">{{ ucfirst($fptk->required_marital_status) }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">4</div>
                    <div class="label">Pendidikan ( Minimal )</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $fptk->required_education ?? '' }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">5</div>
                    <div class="label">Kemampuan Wajib</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $fptk->required_skills ?? '' }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">6</div>
                    <div class="label">Pengalaman Kerja</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $fptk->required_experience ?? '' }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">7</div>
                    <div class="label">Persyaratan Fisik</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $fptk->required_physical ?? '' }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">8</div>
                    <div class="label">Persyaratan Mental</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $fptk->required_mental ?? '' }}</div>
                </div>

                <div class="requirement-item">
                    <div class="number">9</div>
                    <div class="label">Keterangan Lain</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $fptk->other_requirements ?? '' }}</div>
                </div>
            </div>

            <!-- Requester Information -->
            <div class="requester-section">
                <div class="requester-field">
                    <label>Diajukan Oleh</label>
                    <span class="colon">:</span>
                    <div class="value">{{ $fptk->createdBy->name }}</div>
                    <span style="margin-left: 20px;">( Dept Head / Section Head )</span>
                </div>

                <div class="requester-field">
                    <label>Tanggal Pengajuan</label>
                    <span class="colon">:</span>
                    <div class="value">{{ date('d F Y', strtotime($fptk->created_at)) }}</div>
                </div>

                <div class="requester-field">
                    <label>Tanda Tangan</label>
                    <span class="colon">:</span>
                    <div class="value"></div>
                </div>
            </div>

            <!-- Approval Section -->
            <div class="approval-section">
                <div class="approval-row">
                    <div class="approval-column">
                        <div class="approval-title">Diketahui Oleh,</div>
                        <div class="approval-signature"></div>
                        <div class="approval-name">HR&GA Section Head</div>
                    </div>

                    <div class="approval-column">
                        <div class="approval-title">Disetujui Oleh,</div>
                        <div class="approval-signature"></div>
                        <div class="approval-name">Project Manager</div>
                    </div>
                </div>

                <div class="approval-row">
                    <div class="approval-column" style="margin: 0 auto; text-align: center;">
                        <div class="approval-title">Disetujui Oleh,</div>
                        <div class="approval-signature"></div>
                        <div class="approval-name">Operation Director / HCS Division Manager</div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <div>ARKA/HCS/IV/01.01</div>
                <div>Rev.2</div>
                <div>Page 1/1</div>
            </div>
        </div>

        <script>
            // Auto print when page loads (optional)
            // window.addEventListener('load', function() {
            //     window.print();
            // });
        </script>
    </body>

</html>
