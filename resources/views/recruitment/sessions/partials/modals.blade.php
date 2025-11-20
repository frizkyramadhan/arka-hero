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
                        <p>The data you enter here will be automatically saved to the Employee and Administration
                            records.</p>
                    </div>
                    @csrf
                    <div class="card mb-3">
                        <div class="card-header"><strong>Personal Data</strong></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Fullname <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('employee.fullname') is-invalid @enderror"
                                            name="employee[fullname]"
                                            value="{{ old('employee.fullname', $session->candidate->fullname) }}"
                                            required>
                                        @error('employee.fullname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Identity Card No <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('employee.identity_card') is-invalid @enderror"
                                            name="employee[identity_card]"
                                            value="{{ old('employee.identity_card') }}"
                                            placeholder="Enter KTP/ID number" required>
                                        @error('employee.identity_card')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Place of Birth <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('employee.emp_pob') is-invalid @enderror"
                                            name="employee[emp_pob]" value="{{ old('employee.emp_pob') }}"
                                            placeholder="Enter birthplace" required>
                                        @error('employee.emp_pob')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date"
                                            class="form-control @error('employee.emp_dob') is-invalid @enderror"
                                            name="employee[emp_dob]"
                                            value="{{ old('employee.emp_dob', optional($session->candidate->date_of_birth)->format('Y-m-d')) }}"
                                            required>
                                        @error('employee.emp_dob')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Religion <span class="text-danger">*</span></label>
                                        <select
                                            class="form-control @error('employee.religion_id') is-invalid @enderror"
                                            name="employee[religion_id]" required>
                                            <option value="">Select religion</option>
                                            @foreach (\App\Models\Religion::get() as $religion)
                                                <option value="{{ $religion->id }}"
                                                    {{ old('employee.religion_id') == $religion->id ? 'selected' : '' }}>
                                                    {{ $religion->religion_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('employee.religion_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select class="form-control @error('employee.gender') is-invalid @enderror"
                                            name="employee[gender]">
                                            <option value="">Select gender</option>
                                            <option value="male"
                                                {{ old('employee.gender') == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female"
                                                {{ old('employee.gender') == 'female' ? 'selected' : '' }}>Female
                                            </option>
                                        </select>
                                        @error('employee.gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marital Status</label>
                                        <input type="text"
                                            class="form-control @error('employee.marital') is-invalid @enderror"
                                            name="employee[marital]" value="{{ old('employee.marital') }}"
                                            placeholder="Single/Married/etc">
                                        @error('employee.marital')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text"
                                            class="form-control @error('employee.phone') is-invalid @enderror"
                                            name="employee[phone]"
                                            value="{{ old('employee.phone', $session->candidate->phone) }}">
                                        @error('employee.phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text"
                                            class="form-control @error('employee.address') is-invalid @enderror"
                                            name="employee[address]"
                                            value="{{ old('employee.address', $session->candidate->address) }}">
                                        @error('employee.address')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email"
                                            class="form-control @error('employee.email') is-invalid @enderror"
                                            name="employee[email]"
                                            value="{{ old('employee.email', $session->candidate->email) }}">
                                        @error('employee.email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><strong>Administration Data</strong></div>
                        <div class="card-body">
                            @if (in_array(optional($session->fptk)->employment_type, ['magang', 'harian']))
                                {{-- Administration Data for Magang and Harian --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>{{ optional($session->fptk)->employment_type === 'magang' ? 'NIM' : 'NID' }}
                                                <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('administration.nik') is-invalid @enderror"
                                                name="administration[nik]" value="{{ old('administration.nik') }}"
                                                placeholder="Enter {{ optional($session->fptk)->employment_type === 'magang' ? 'NIM' : 'NID' }}"
                                                required>
                                            @error('administration.nik')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Hire <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('administration.doh') is-invalid @enderror"
                                                name="administration[doh]"
                                                value="{{ old('administration.doh', now()->format('Y-m-d')) }}"
                                                required>
                                            @error('administration.doh')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Place of Hire (POH) <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('administration.poh') is-invalid @enderror"
                                                name="administration[poh]" value="{{ old('administration.poh') }}"
                                                placeholder="Enter POH" required>
                                            @error('administration.poh')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Class <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.class') is-invalid @enderror"
                                                name="administration[class]" required>
                                                <option value="">Select class</option>
                                                <option value="Staff"
                                                    {{ old('administration.class') == 'Staff' ? 'selected' : '' }}>
                                                    Staff
                                                </option>
                                                <option value="Non Staff"
                                                    {{ old('administration.class') == 'Non Staff' ? 'selected' : '' }}>
                                                    Non
                                                    Staff</option>
                                            </select>
                                            @error('administration.class')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Position <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.position_id') is-invalid @enderror"
                                                name="administration[position_id]" id="hire_position_id_magang_harian"
                                                required>
                                                <option value="">Select position</option>
                                                @foreach (\App\Models\Position::orderBy('position_name', 'asc')->get() as $position)
                                                    <option value="{{ $position->id }}"
                                                        {{ old('administration.position_id', optional($session->fptk)->position_id) == $position->id ? 'selected' : '' }}>
                                                        {{ $position->position_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('administration.position_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Department</label>
                                            <input type="text" class="form-control"
                                                id="hire_department_magang_harian" readonly>
                                            <small class="form-text text-muted">Department will be automatically filled
                                                based on position selection</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Project <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.project_id') is-invalid @enderror"
                                                name="administration[project_id]" required>
                                                <option value="">Select project</option>
                                                @foreach (\App\Models\Project::orderBy('project_code', 'asc')->get() as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('administration.project_id', optional($session->fptk)->project_id) == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }}</option>
                                                @endforeach
                                            </select>
                                            @error('administration.project_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>FPTK No</label>
                                            <input type="text" class="form-control" name="administration[no_fptk]"
                                                value="{{ optional($session->fptk)->request_number }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Administration Data for PKWT and PKWTT --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>NIK <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('administration.nik') is-invalid @enderror"
                                                name="administration[nik]" value="{{ old('administration.nik') }}"
                                                placeholder="Enter employee NIK" required>
                                            @error('administration.nik')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Date of Hire <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('administration.doh') is-invalid @enderror"
                                                name="administration[doh]"
                                                value="{{ old('administration.doh', now()->format('Y-m-d')) }}"
                                                required>
                                            @error('administration.doh')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Place of Hire (POH) <span class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('administration.poh') is-invalid @enderror"
                                                name="administration[poh]" value="{{ old('administration.poh') }}"
                                                placeholder="Enter POH" required>
                                            @error('administration.poh')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Class <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.class') is-invalid @enderror"
                                                name="administration[class]" required>
                                                <option value="">Select class</option>
                                                <option value="Staff"
                                                    {{ old('administration.class') == 'Staff' ? 'selected' : '' }}>
                                                    Staff
                                                </option>
                                                <option value="Non Staff"
                                                    {{ old('administration.class') == 'Non Staff' ? 'selected' : '' }}>
                                                    Non
                                                    Staff</option>
                                            </select>
                                            @error('administration.class')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Position <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.position_id') is-invalid @enderror"
                                                name="administration[position_id]" id="hire_position_id" required>
                                                <option value="">Select position</option>
                                                @foreach (\App\Models\Position::orderBy('position_name', 'asc')->get() as $position)
                                                    <option value="{{ $position->id }}"
                                                        {{ old('administration.position_id', optional($session->fptk)->position_id) == $position->id ? 'selected' : '' }}>
                                                        {{ $position->position_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('administration.position_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Department</label>
                                            <input type="text" class="form-control" id="hire_department" readonly>
                                            <small class="form-text text-muted">Department will be automatically filled
                                                based on position selection</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Project <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.project_id') is-invalid @enderror"
                                                name="administration[project_id]" required>
                                                <option value="">Select project</option>
                                                @foreach (\App\Models\Project::orderBy('project_code', 'asc')->get() as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('administration.project_id', optional($session->fptk)->project_id) == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }}</option>
                                                @endforeach
                                            </select>
                                            @error('administration.project_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Grade</label>
                                            <select
                                                class="form-control @error('administration.grade_id') is-invalid @enderror"
                                                name="administration[grade_id]">
                                                <option value="">Select grade</option>
                                                @foreach (\App\Models\Grade::where('is_active', 1)->orderBy('name', 'asc')->get() as $grade)
                                                    <option value="{{ $grade->id }}"
                                                        {{ old('administration.grade_id') == $grade->id ? 'selected' : '' }}>
                                                        {{ $grade->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('administration.grade_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Level <span class="text-danger">*</span></label>
                                            <select
                                                class="form-control @error('administration.level_id') is-invalid @enderror"
                                                name="administration[level_id]" required>
                                                <option value="">Select level</option>
                                                @foreach (\App\Models\Level::where('is_active', 1)->orderBy('name', 'asc')->get() as $level)
                                                    <option value="{{ $level->id }}"
                                                        {{ old('administration.level_id') == $level->id ? 'selected' : '' }}>
                                                        {{ $level->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('administration.level_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>FPTK No</label>
                                            <input type="text" class="form-control" name="administration[no_fptk]"
                                                value="{{ optional($session->fptk)->request_number }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Finish of Contract (FOC) <span class="text-danger">*</span></label>
                                            <input type="date"
                                                class="form-control @error('administration.foc') is-invalid @enderror"
                                                name="administration[foc]" value="{{ old('administration.foc') }}"
                                                id="administration_foc">
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
