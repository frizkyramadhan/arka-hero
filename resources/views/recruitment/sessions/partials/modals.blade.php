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
                                            name="online_score" min="0" max="100" step="0.01"
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
                                                name="offline_score" min="0" max="10" step="0.1"
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
                            <option value="hr">HR Interview</option>
                            <option value="user">User Interview</option>
                        </select>
                    </div>

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
<div class="modal fade" id="hireModal">
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
                                        <input type="text" class="form-control" name="employee[fullname]"
                                            value="{{ $session->candidate->fullname }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Identity Card No <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="employee[identity_card]"
                                            placeholder="Enter KTP/ID number" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Place of Birth <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="employee[emp_pob]"
                                            placeholder="Enter birthplace" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Birth <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="employee[emp_dob]"
                                            value="{{ optional($session->candidate->date_of_birth)->format('Y-m-d') }}"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Religion <span class="text-danger">*</span></label>
                                        <select class="form-control" name="employee[religion_id]" required>
                                            <option value="">Select religion</option>
                                            @foreach (\App\Models\Religion::get() as $religion)
                                                <option value="{{ $religion->id }}">{{ $religion->religion_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select class="form-control" name="employee[gender]">
                                            <option value="">Select gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marital Status</label>
                                        <input type="text" class="form-control" name="employee[marital]"
                                            placeholder="Single/Married/etc">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input type="text" class="form-control" name="employee[phone]"
                                            value="{{ $session->candidate->phone }}">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" name="employee[address]"
                                            value="{{ $session->candidate->address }}">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" name="employee[email]"
                                            value="{{ $session->candidate->email }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header"><strong>Administration Data</strong></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>NIK <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="administration[nik]"
                                            placeholder="Enter employee NIK" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Date of Hire <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="administration[doh]"
                                            value="{{ now()->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Place of Hire (POH) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="administration[poh]"
                                            placeholder="Enter POH" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Class <span class="text-danger">*</span></label>
                                        <select class="form-control" name="administration[class]" required>
                                            <option value="">Select class</option>
                                            <option value="Staff">Staff</option>
                                            <option value="Non Staff">Non Staff</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Project</label>
                                        <select class="form-control" name="administration[project_id]">
                                            <option value="">Select project</option>
                                            @foreach (\App\Models\Project::orderBy('project_code', 'asc')->get() as $project)
                                                <option value="{{ $project->id }}"
                                                    {{ optional($session->fptk)->project_id == $project->id ? 'selected' : '' }}>
                                                    {{ $project->project_code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Position</label>
                                        <select class="form-control" name="administration[position_id]">
                                            <option value="">Select position</option>
                                            @foreach (\App\Models\Position::orderBy('position_name', 'asc')->get() as $position)
                                                <option value="{{ $position->id }}"
                                                    {{ optional($session->fptk)->position_id == $position->id ? 'selected' : '' }}>
                                                    {{ $position->position_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Grade</label>
                                        <select class="form-control" name="administration[grade_id]">
                                            <option value="">Select grade</option>
                                            @foreach (\App\Models\Grade::where('is_active', 1)->orderBy('name', 'asc')->get() as $grade)
                                                <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Level</label>
                                        <select class="form-control" name="administration[level_id]">
                                            <option value="">Select level</option>
                                            @foreach (\App\Models\Level::where('is_active', 1)->orderBy('name', 'asc')->get() as $level)
                                                <option value="{{ $level->id }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
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
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header"><strong>Agreement</strong></div>
                        <div class="card-body">
                            <!-- PKWT Letter Number Selection -->
                            <div class="form-group">
                                @include('components.smart-letter-number-selector', [
                                    'categoryCode' => 'PKWT',
                                    'fieldName' => 'hiring_letter_number_id',
                                    'required' => true,
                                    'placeholder' => 'Select PKWT Letter Number',
                                ])
                            </div>

                            <!-- Final Letter Number (readonly display) -->
                            <div class="form-group">
                                <label for="hiring_letter_number">PKWT Letter Number <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control alert-warning text-dark font-weight-bold"
                                    id="hiring_letter_number" name="hiring_letter_number_display"
                                    placeholder="Select letter number above" readonly>
                                <small class="form-text text-muted">This value is auto-filled from the selected letter
                                    number.</small>
                            </div>

                            <!-- Agreement Type -->
                            <div class="form-group">
                                <label class="form-label mb-2"><strong>Agreement Type</strong> <span
                                        class="text-danger">*</span></label>
                                <div class="decision-buttons">
                                    <button type="button" class="btn decision-btn btn-outline-success"
                                        data-agreement="pkwt"><i class="fas fa-file-signature"></i> PKWT</button>
                                    <button type="button" class="btn decision-btn btn-outline-warning"
                                        data-agreement="pkwtt"><i class="fas fa-briefcase"></i> PKWTT</button>
                                </div>
                                <input type="hidden" name="agreement_type" id="agreement_type" required>
                            </div>

                            <!-- FOC (only for PKWT) -->
                            <div class="row">
                                <div class="col-md-6" id="foc_container" style="display:none;">
                                    <div class="form-group">
                                        <label>Finish of Contract (FOC) <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="administration[foc]"
                                            id="administration_foc">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="hire_notes" class="form-label"><strong>Notes</strong></label>
                        <textarea class="form-control" id="hire_notes" name="notes" rows="3"
                            placeholder="Enter hiring notes (optional)"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="hire_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="hire_reviewed_at" name="reviewed_at"
                            value="{{ now()->format('Y-m-d\TH:i') }}" required>
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

<!-- Onboarding Modal -->
<div class="modal fade" id="onboardingModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-graduation-cap"></i> Onboarding Stage
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="onboardingForm" method="POST" class="confirm-submit"
                data-confirm-message="Submit onboarding? You cannot edit after submission."
                action="{{ route('recruitment.sessions.update-onboarding', $session->id) }}">
                <div class="modal-body">
                    @csrf
                    <div class="form-group">
                        <label for="onboarding_date">Onboarding Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="onboarding_date" name="onboarding_date"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="onboarding_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="onboarding_reviewed_at"
                            name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="onboarding_notes">Notes</label>
                        <textarea class="form-control" id="onboarding_notes" name="notes" rows="3"
                            placeholder="Enter notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Onboarding</button>
                </div>
            </form>
        </div>
    </div>
</div>
