<!-- CV Review Modal -->
<div class="modal fade" id="cvReviewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-alt"></i> Choose Your Decision
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('recruitment.sessions.update-cv-review', $session->id) }}"
                class="confirm-submit" data-confirm-message="Submit this decision? You cannot edit after submission.">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="assessment_data" id="cv_review_assessment_data">
                    <input type="hidden" id="cv_review_decision" name="decision" required>

                    <div class="form-group">
                        <label class="form-label mb-2">
                            <strong>CV Review Decision</strong> <span class="text-danger">*</span>
                        </label>
                        <div class="decision-buttons">
                            <button type="button" class="btn decision-btn btn-outline-success"
                                data-status="recommended">
                                <i class="fas fa-check-circle"></i> Recommended
                            </button>
                            <button type="button" class="btn decision-btn btn-outline-danger"
                                data-status="not_recommended">
                                <i class="fas fa-times-circle"></i> Not Recommended
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="cv_review_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="cv_review_reviewed_at" name="reviewed_at"
                            value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="form-group">
                        <label for="cv_review_notes" class="font-weight-bold">Notes <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="cv_review_notes" name="notes" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary submit-btn" disabled>
                        <i class="fas fa-paper-plane"></i> Submit Decision
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Psikotes Modal -->
<div class="modal fade" id="psikotesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-brain"></i> Psikotes Assessment
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="psikotesForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit this assessment? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-psikotes', $session->id) }}">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-laptop"></i> Psikotes Online
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="psikotes_online_score">Rata-rata Hasil</label>
                                        <input type="number" class="form-control" id="psikotes_online_score"
                                            name="online_score" min="0" step="0.01"
                                            placeholder="Masukkan rata-rata hasil">
                                    </div>
                                    <div class="alert alert-info">
                                        <strong>Kriteria:</strong><br>
                                        • ≥ 40: Proses dapat dilanjutkan<br>
                                        • < 40: Tidak Direkomendasikan </div>
                                            <div id="psikotes_online_result" class="mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-user-graduate"></i> Psikotes Offline
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="psikotes_offline_score">Skor Tes Intelegensi Umum (TIU)</label>
                                            <input type="number" class="form-control" id="psikotes_offline_score"
                                                name="offline_score" min="0" step="0.1"
                                                placeholder="Masukkan skor TIU">
                                        </div>
                                        <div class="alert alert-info">
                                            <strong>Kriteria:</strong><br>
                                            • ≥ 8: Proses dapat dilanjutkan<br>
                                            • < 8: Kurang </div>
                                                <div id="psikotes_offline_result" class="mt-2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="psikotes_reviewed_at" class="font-weight-bold">Review Date
                                            <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" id="psikotes_reviewed_at"
                                            name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="psikotes_notes">Catatan</label>
                                        <textarea class="form-control" id="psikotes_notes" name="notes" rows="3"
                                            placeholder="Masukkan catatan tambahan (opsional)"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit Assessment</button>
                        </div>
            </form>
        </div>
    </div>
</div>

<!-- Tes Teori Modal -->
<div class="modal fade" id="tesTeoriModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-book"></i> Tes Teori Assessment
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="tesTeoriForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit this assessment? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-tes-teori', $session->id) }}">
                <div class="modal-body">
                    @csrf
                    <div class="alert alert-warning mb-4">
                        <strong>Kategori Berdasarkan Skor:</strong><br>
                        • ≥ 76: Mechanic Senior<br>
                        • ≥ 61: Mechanic Advance<br>
                        • ≥ 46: Mechanic<br>
                        • ≥ 21: Helper Mechanic<br>
                        • < 21: Belum Kompeten </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tes_teori_score">Skor Tes Teori</label>
                                        <input type="number" class="form-control" id="tes_teori_score"
                                            name="score" min="0" max="100" step="0.01"
                                            placeholder="Masukkan skor tes teori">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tes_teori_reviewed_at" class="font-weight-bold">Review Date
                                            <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" id="tes_teori_reviewed_at"
                                            name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="tes_teori_notes">Catatan</label>
                                        <textarea class="form-control" id="tes_teori_notes" name="notes" rows="3"
                                            placeholder="Masukkan catatan tambahan (opsional)"></textarea>
                                    </div>
                                </div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit Assessment</button>
                    </div>
            </form>
        </div>
    </div>
</div>

<!-- Interview Modal -->
<div class="modal fade" id="interviewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-tie"></i> Choose Your Decision
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="interviewForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit this decision? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-interview', $session->id) }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="interview_decision" name="result" required>

                    <!-- Interview Type Selection -->
                    <div class="form-group mb-4">
                        <label for="interview_type" class="font-weight-bold">Interview Type <span
                                class="text-danger">*</span></label>
                        <select class="form-control" id="interview_type" name="type" required>
                            <option value="">Select Interview Type</option>
                            <option value="hr" {{ $session->isInterviewTypeCompleted('hr') ? 'disabled' : '' }}>
                                HR Interview
                                @if ($session->isInterviewTypeCompleted('hr'))
                                    ({{ $session->interviews()->where('type', 'hr')->first()->result === 'recommended' ? 'Recommended' : 'Not Recommended' }})
                                @endif
                            </option>
                            <option value="user" {{ $session->isInterviewTypeCompleted('user') ? 'disabled' : '' }}>
                                User Interview
                                @if ($session->isInterviewTypeCompleted('user'))
                                    ({{ $session->interviews()->where('type', 'user')->first()->result === 'recommended' ? 'Recommended' : 'Not Recommended' }})
                                @endif
                            </option>
                            @if (!$session->shouldSkipTheoryTest())
                                <option value="trainer"
                                    {{ $session->isInterviewTypeCompleted('trainer') ? 'disabled' : '' }}>
                                    Trainer Interview
                                    @if ($session->isInterviewTypeCompleted('trainer'))
                                        ({{ $session->interviews()->where('type', 'trainer')->first()->result === 'recommended' ? 'Recommended' : 'Not Recommended' }})
                                    @endif
                                </option>
                            @endif
                        </select>
                        <small class="form-text text-muted">
                            Interview types that have already been completed are disabled.
                        </small>
                    </div>

                    <!-- Show existing interview results -->
                    @if ($session->interviews()->exists())
                        @php
                            $interviewSummary = $session->getInterviewSummary();
                        @endphp
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Interview Status:</h6>
                            @php
                                $interviewTypes = ['hr', 'user'];
                                if (!$session->shouldSkipTheoryTest()) {
                                    $interviewTypes[] = 'trainer';
                                }
                            @endphp
                            @foreach ($interviewTypes as $type)
                                <div class="mb-2">
                                    <strong>{{ ucfirst($type) }} Interview:</strong>
                                    @if ($interviewSummary[$type]['completed'])
                                        <span
                                            class="badge badge-{{ $interviewSummary[$type]['result'] === 'recommended' ? 'success' : 'danger' }}">
                                            {{ ucfirst($interviewSummary[$type]['result']) }}
                                        </span>
                                        <small class="ml-2">
                                            {{ date('d M Y H:i', strtotime($interviewSummary[$type]['reviewed_at'])) }}
                                            by {{ $interviewSummary[$type]['reviewer'] }}
                                        </small>
                                    @else
                                        <span class="badge badge-secondary">Pending</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="form-label mb-2">
                            <strong>Interview Decision</strong> <span class="text-danger">*</span>
                        </label>
                        <div class="decision-buttons">
                            <button type="button" class="btn decision-btn btn-outline-success"
                                data-status="recommended">
                                <i class="fas fa-check-circle"></i> Recommended
                            </button>
                            <button type="button" class="btn decision-btn btn-outline-danger"
                                data-status="not_recommended">
                                <i class="fas fa-times-circle"></i> Not Recommended
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="interview_notes" class="font-weight-bold">Notes <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="interview_notes" name="notes" rows="3" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="interview_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="interview_reviewed_at"
                            name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary submit-btn" disabled>
                        <i class="fas fa-paper-plane"></i> Submit Decision
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Offering Modal -->
<div class="modal fade" id="offeringModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-handshake"></i> Offering Stage
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="offeringForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit this offering? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-offering', $session->id) }}">
                <div class="modal-body">
                    @csrf

                    <!-- Letter Number Selection -->
                    <div class="form-group">
                        @include('components.smart-letter-number-selector', [
                            'categoryCode' => 'A',
                            'fieldName' => 'offering_letter_number_id',
                            'required' => true,
                            'placeholder' => 'Select Offering Letter Number',
                        ])
                    </div>

                    <!-- Offering Letter Number (auto-filled like LOT Number) -->
                    <div class="form-group">
                        <label for="offering_letter_number">Offering Letter Number <span
                                class="text-danger">*</span></label>
                        <input type="text" class="form-control alert-warning text-dark font-weight-bold"
                            id="offering_letter_number" name="offering_letter_number_display"
                            placeholder="Select letter number above" readonly>
                        <small class="form-text text-muted">
                            This value is auto-filled from the selected letter number. It will be saved as the offering
                            letter number.
                        </small>
                    </div>

                    <!-- Decision Buttons -->
                    <div class="form-group">
                        <label class="form-label mb-2">
                            <strong>Offering Decision</strong> <span class="text-danger">*</span>
                        </label>
                        <div class="decision-buttons">
                            <button type="button" class="btn decision-btn btn-outline-success"
                                data-decision="accepted">
                                <i class="fas fa-check-circle"></i> Accepted
                            </button>
                            <button type="button" class="btn decision-btn btn-outline-danger"
                                data-decision="rejected">
                                <i class="fas fa-times-circle"></i> Rejected
                            </button>
                        </div>
                        <input type="hidden" name="result" id="offering_result" required>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="offering_notes" class="form-label">
                            <strong>Notes</strong>
                        </label>
                        <textarea class="form-control" id="offering_notes" name="notes" rows="3"
                            placeholder="Enter offering notes (optional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="offering_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="offering_reviewed_at"
                            name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="offering_submit_btn" disabled>Submit
                        Offering</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MCU Modal -->
<div class="modal fade" id="mcuModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-md"></i> Medical Check Up Assessment
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="mcuForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit this MCU result? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-mcu', $session->id) }}">
                <div class="modal-body">
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>MCU Result</label>
                                <div class="decision-buttons">
                                    <button type="button" class="btn decision-btn btn-outline-success"
                                        data-mcu="fit">
                                        <i class="fas fa-check-circle"></i> Fit to Work
                                    </button>
                                    <button type="button" class="btn decision-btn btn-outline-danger"
                                        data-mcu="unfit">
                                        <i class="fas fa-times-circle"></i> Unfit
                                    </button>
                                    <button type="button" class="btn decision-btn btn-outline-warning"
                                        data-mcu="follow_up">
                                        <i class="fas fa-stethoscope"></i> Follow Up
                                    </button>
                                </div>
                                <input type="hidden" id="mcu_overall_health" name="overall_health" required>
                            </div>
                            <div class="form-group">
                                <label for="mcu_notes">Notes</label>
                                <textarea class="form-control" id="mcu_notes" name="notes" rows="3" placeholder="Enter notes (optional)"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="mcu_reviewed_at" class="font-weight-bold">Review Date <span
                                        class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="mcu_reviewed_at"
                                    name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary submit-btn" disabled>Submit Assessment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hire Modal -->
<div class="modal fade" id="hireModal"
    data-employment-type="{{ optional($session->fptk)->employment_type ?: (optional($session->mppDetail)->agreement_type ?: 'pkwt') }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-check"></i> Hire Stage
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="hireForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit this hiring data? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-hiring', $session->id) }}">
                {{-- info untuk proses input data berikut akan masuk ke data employee dan administration otomatis --}}
                <div class="modal-body">
                    <div class="alert alert-info alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <strong>Info:</strong>
                        <p>Submit Hire completes the recruitment session and updates FPTK/MPP quota. Registering the
                            employee to Employee Management is optional — enable the checkbox below to register a
                            <strong>new</strong> employee or <strong>link an existing</strong> employee.
                        </p>
                    </div>
                    @csrf
                    @include('recruitment.sessions.partials.hire-employee-fields', [
                        'session' => $session,
                        'suffix' => '',
                        'showRegisterCheckbox' => true,
                    ])

                    <div class="card mt-3">
                        <div class="card-header"><strong>Agreement</strong></div>
                        <div class="card-body">
                            @php
                                // Get employment type from FPTK or agreement type from MPP Detail
                                if ($session->fptk_id && $session->fptk) {
                                    $employmentType = $session->fptk->employment_type;
                                } elseif ($session->mpp_detail_id && $session->mppDetail) {
                                    $employmentType = $session->mppDetail->agreement_type ?? 'pkwt';
                                } else {
                                    $employmentType = 'pkwt';
                                }

                                $agreementType = $employmentType ?: 'pkwt'; // Fallback ke 'pkwt' jika null atau empty

                                // For display purposes
                                $displayAgreementType = $agreementType;
                                if ($displayAgreementType === 'magang') {
                                    $displayAgreementType = 'Intern';
                                } elseif ($displayAgreementType === 'harian') {
                                    $displayAgreementType = 'Daily';
                                }
                            @endphp

                            <!-- Letter Number Selection -->
                            <div class="form-group">
                                @if ($employmentType === 'harian')
                                    @include('components.smart-letter-number-selector', [
                                        'categoryCode' => 'B',
                                        'fieldName' => 'hiring_letter_number_id',
                                        'required' => true,
                                        'placeholder' => 'Select Daily Letter Number',
                                    ])
                                @elseif ($employmentType === 'magang')
                                    @include('components.smart-letter-number-selector', [
                                        'categoryCode' => 'SPM',
                                        'fieldName' => 'hiring_letter_number_id',
                                        'required' => true,
                                        'placeholder' => 'Select Intern Letter Number',
                                    ])
                                @else
                                    @include('components.smart-letter-number-selector', [
                                        'categoryCode' => 'PKWT',
                                        'fieldName' => 'hiring_letter_number_id',
                                        'required' => true,
                                        'placeholder' => 'Select PKWT Letter Number',
                                    ])
                                @endif
                            </div>

                            <!-- Final Letter Number (readonly display) -->
                            <div class="form-group">
                                <label for="hiring_letter_number">Letter Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control alert-warning text-dark font-weight-bold"
                                    id="hiring_letter_number" name="hiring_letter_number_display"
                                    placeholder="Select letter number above" readonly>
                                <small class="form-text text-muted">
                                    This value is auto-filled from the selected letter number.
                                </small>
                            </div>

                            <!-- Agreement Type -->
                            <div class="form-group">
                                <label class="form-label mb-2"><strong>Agreement Type</strong> <span
                                        class="text-danger">*</span></label>

                                <div class="d-flex align-items-center">
                                    <span
                                        class="badge badge-{{ $employmentType === 'pkwtt' ? 'success' : ($employmentType === 'pkwt' ? 'primary' : ($employmentType === 'magang' ? 'warning' : 'info')) }} badge-lg mr-2">
                                        <i
                                            class="fas fa-{{ $employmentType === 'magang' ? 'graduation-cap' : ($employmentType === 'harian' ? 'clock' : ($employmentType === 'pkwt' ? 'file-signature' : 'briefcase')) }} mr-1"></i>
                                        {{ strtoupper($displayAgreementType) }}
                                    </span>
                                    <small class="text-muted">
                                        @if ($session->fptk_id)
                                            (Auto-selected based on FPTK employment type)
                                        @elseif($session->mpp_detail_id)
                                            (Auto-selected based on MPP Detail agreement type)
                                        @else
                                            (Default: PKWT)
                                        @endif
                                    </small>
                                </div>

                                <input type="hidden" name="agreement_type" value="{{ $agreementType }}" required>
                            </div>

                            <!-- FOC (only for PKWT) -->
                            @if ($employmentType === 'pkwt')
                                <div class="row" id="foc_container" style="display: none;">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Finish of Contract (FOC) <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('administration.foc') is-invalid @enderror"
                                                name="administration[foc]" value="{{ old('administration.foc') }}"
                                                id="administration_foc" data-required-when-register>
                                            @error('administration.foc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="hire_notes" class="form-label"><strong>Notes</strong></label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="hire_notes" name="notes" rows="3"
                            placeholder="Enter hiring notes (optional)">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Review Date -->
                    <div class="form-group">
                        <label for="hire_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('reviewed_at') is-invalid @enderror"
                            id="hire_reviewed_at" name="reviewed_at"
                            value="{{ old('reviewed_at', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('reviewed_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary submit-btn" id="hire_submit_btn" disabled>Submit
                        Hire</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Register Employee Modal (post-hire) -->
<div class="modal fade" id="registerEmployeeModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="registerEmployeeModalTitle">
                    <i class="fas fa-user-plus"></i> Register Employee
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" class="confirm-submit" id="registerEmployeeForm"
                data-confirm-message="Register this candidate as an employee?"
                action="{{ route('recruitment.sessions.register-employee', $session->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Info:</strong> This session is already hired. Register a <strong>new</strong> employee
                        (with personal and administration data) or
                        <strong>link an existing</strong> employee from Employee Management. For existing employees,
                        current administration data is used as-is.
                    </div>
                    @php
                        $registerAgreementType = optional($session->hiring)->agreement_type;
                        if (!$registerAgreementType) {
                            if ($session->fptk_id && $session->fptk) {
                                $registerAgreementType = $session->fptk->employment_type;
                            } elseif ($session->mpp_detail_id && $session->mppDetail) {
                                $registerAgreementType = $session->mppDetail->agreement_type ?? 'pkwt';
                            } else {
                                $registerAgreementType = 'pkwt';
                            }
                        }
                    @endphp
                    <input type="hidden" name="agreement_type" value="{{ $registerAgreementType }}">
                    @include('recruitment.sessions.partials.hire-employee-fields', [
                        'session' => $session,
                        'suffix' => '_register',
                        'showRegisterCheckbox' => false,
                    ])
                    @if ($registerAgreementType === 'pkwt')
                        <div class="form-group" id="register_foc_container">
                            <label>Finish of Contract (FOC) <span class="text-danger">*</span></label>
                            <input type="date"
                                class="form-control @error('administration.foc') is-invalid @enderror"
                                name="administration[foc]" value="{{ old('administration.foc') }}" required>
                            @error('administration.foc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="register_employee_submit_btn">Register
                        Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Stage Transition Modal -->
<div class="modal fade" id="transitionStageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-exchange-alt"></i> Transition Stage
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('recruitment.sessions.transition-stage', $session->id) }}"
                class="confirm-submit"
                data-confirm-message="Transition to selected stage? This will update the recruitment progress.">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Current Stage:</strong> {{ ucfirst(str_replace('_', ' ', $session->current_stage)) }}
                    </div>

                    <div class="form-group">
                        <label for="target_stage" class="font-weight-bold">Target Stage <span
                                class="text-danger">*</span></label>
                        <select class="form-control @error('target_stage') is-invalid @enderror" id="target_stage"
                            name="target_stage" required>
                            <option value="">Select target stage...</option>
                            @php
                                // Get valid stages for this session
                                $isSimplifiedProcess = false;
                                if (
                                    $session->fptk_id &&
                                    $session->fptk &&
                                    in_array($session->fptk->employment_type, ['magang', 'harian'])
                                ) {
                                    $isSimplifiedProcess = true;
                                    $validStages = ['mcu', 'hire'];
                                } else {
                                    $validStages = [
                                        'cv_review',
                                        'psikotes',
                                        'tes_teori',
                                        'interview',
                                        'offering',
                                        'mcu',
                                        'hire',
                                    ];
                                    // Remove tes_teori if should be skipped
                                    if ($session->shouldSkipTheoryTest()) {
                                        $validStages = array_diff($validStages, ['tes_teori']);
                                    }
                                }

                                $stageLabels = [
                                    'cv_review' => 'CV Review',
                                    'psikotes' => 'Psikotes',
                                    'tes_teori' => 'Tes Teori',
                                    'interview' => 'Interview',
                                    'offering' => 'Offering',
                                    'mcu' => 'MCU',
                                    'hire' => 'Hiring & Onboarding',
                                ];
                            @endphp
                            @foreach ($validStages as $stage)
                                @if ($stage !== $session->current_stage)
                                    <option value="{{ $stage }}"
                                        {{ old('target_stage') === $stage ? 'selected' : '' }}>
                                        {{ $stageLabels[$stage] }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('target_stage')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="transition_reason" class="font-weight-bold">Reason for Transition <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control @error('reason') is-invalid @enderror" id="transition_reason" name="reason"
                            rows="4" placeholder="Please provide a detailed reason for this stage transition..." required>{{ old('reason') }}</textarea>
                        @error('reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">This reason will be logged for audit purposes.</small>
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="force_transition"
                                name="force_transition" value="1"
                                {{ old('force_transition') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="force_transition">
                                <strong>Force Transition</strong> (bypass validation rules)
                            </label>
                        </div>
                        <small class="form-text text-muted">
                            Check this only if you need to bypass normal validation rules (e.g., skipping failed
                            stages).
                        </small>
                    </div>

                    <div id="transition_warnings" class="alert alert-warning" style="display: none;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> <span id="warning_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary submit-btn" id="transition_submit_btn">
                        <i class="fas fa-exchange-alt"></i> Transition Stage
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
