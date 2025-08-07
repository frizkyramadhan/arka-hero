<!-- Advance Stage Modal -->
<div class="modal fade" id="advanceModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Advance to Next Stage</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="advanceForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="advance_session_id" name="session_id">
                    <div class="form-group">
                        <label for="advance_notes">Stage Notes</label>
                        <textarea class="form-control" id="advance_notes" name="notes" rows="3"
                            placeholder="Enter notes for stage completion (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success">Advance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Session Modal -->
<div class="modal fade" id="rejectModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Reject Session</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="rejectForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="reject_session_id" name="session_id">
                    <div class="form-group">
                        <label for="reject_reason">Rejection Reason *</label>
                        <textarea class="form-control" id="reject_reason" name="reason" rows="3" placeholder="Enter rejection reason"
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Complete Session Modal -->
<div class="modal fade" id="completeModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Complete Session</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="completeForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="complete_session_id" name="session_id">
                    <div class="form-group">
                        <label for="hire_date">Hire Date *</label>
                        <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                    </div>
                    <div class="form-group">
                        <label for="employee_id">Employee ID</label>
                        <input type="text" class="form-control" id="employee_id" name="employee_id"
                            placeholder="Enter employee ID">
                    </div>
                    <div class="form-group">
                        <label for="complete_notes">Completion Notes</label>
                        <textarea class="form-control" id="complete_notes" name="notes" rows="3"
                            placeholder="Enter completion notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Complete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Cancel Session Modal -->
<div class="modal fade" id="cancelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cancel Session</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="cancelForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="cancel_session_id" name="session_id">
                    <div class="form-group">
                        <label for="cancel_reason">Cancellation Reason *</label>
                        <textarea class="form-control" id="cancel_reason" name="cancel_reason" rows="3"
                            placeholder="Enter cancellation reason" required></textarea>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CV Review Modal -->
<div class="modal fade" id="cvReviewModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-alt"></i> CV Review Decision
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <p class="lead">What is your decision for this candidate's CV?</p>
                </div>
                <div class="row">
                    <div class="col-6">
                        <button type="button" class="btn btn-success btn-lg btn-block" id="cvPassBtn">
                            <i class="fas fa-check"></i> Pass
                        </button>
                    </div>
                    <div class="col-6">
                        <button type="button" class="btn btn-danger btn-lg btn-block" id="cvFailBtn">
                            <i class="fas fa-times"></i> Fail
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
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
            <form id="psikotesForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="psikotes_session_id" name="session_id">
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
            <form id="tesTeoriForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="tes_teori_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tes_teori_score">Skor Tes Teori</label>
                                <input type="number" class="form-control" id="tes_teori_score" name="score"
                                    min="0" max="100" step="0.01"
                                    placeholder="Masukkan skor tes teori">
                            </div>
                            <div class="alert alert-info">
                                <strong>Kriteria:</strong><br>
                                • ≥ 75: Lulus<br>
                                • < 75: Tidak Lulus </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tes_teori_duration">Durasi Pengerjaan (menit)</label>
                                    <input type="number" class="form-control" id="tes_teori_duration"
                                        name="duration" min="0" placeholder="Masukkan durasi pengerjaan">
                                </div>
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

<!-- Interview HR Modal -->
<div class="modal fade" id="interviewHrModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-tie"></i> Interview HR Assessment
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="interviewHrForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="interview_hr_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="interview_hr_communication">Komunikasi (1-10)</label>
                                <input type="number" class="form-control" id="interview_hr_communication"
                                    name="communication" min="1" max="10" step="0.1"
                                    placeholder="Skor komunikasi">
                            </div>
                            <div class="form-group">
                                <label for="interview_hr_attitude">Sikap (1-10)</label>
                                <input type="number" class="form-control" id="interview_hr_attitude"
                                    name="attitude" min="1" max="10" step="0.1"
                                    placeholder="Skor sikap">
                            </div>
                            <div class="form-group">
                                <label for="interview_hr_cultural_fit">Cultural Fit (1-10)</label>
                                <input type="number" class="form-control" id="interview_hr_cultural_fit"
                                    name="cultural_fit" min="1" max="10" step="0.1"
                                    placeholder="Skor cultural fit">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="interview_hr_overall">Skor Keseluruhan</label>
                                <input type="number" class="form-control" id="interview_hr_overall"
                                    name="overall_score" min="0" max="100" step="0.01"
                                    placeholder="Skor keseluruhan" readonly>
                            </div>
                            <div class="alert alert-info">
                                <strong>Kriteria:</strong><br>
                                • ≥ 70: Lulus<br>
                                • < 70: Tidak Lulus </div>
                                    <div class="form-group">
                                        <label for="interview_hr_notes">Catatan Interview</label>
                                        <textarea class="form-control" id="interview_hr_notes" name="notes" rows="4"
                                            placeholder="Masukkan catatan interview (opsional)"></textarea>
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

<!-- Interview User Modal -->
<div class="modal fade" id="interviewUserModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-users"></i> Interview User Assessment
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="interviewUserForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="interview_user_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="interview_user_technical">Technical Skill (1-10)</label>
                                <input type="number" class="form-control" id="interview_user_technical"
                                    name="technical" min="1" max="10" step="0.1"
                                    placeholder="Skor technical skill">
                            </div>
                            <div class="form-group">
                                <label for="interview_user_experience">Experience (1-10)</label>
                                <input type="number" class="form-control" id="interview_user_experience"
                                    name="experience" min="1" max="10" step="0.1"
                                    placeholder="Skor experience">
                            </div>
                            <div class="form-group">
                                <label for="interview_user_problem_solving">Problem Solving (1-10)</label>
                                <input type="number" class="form-control" id="interview_user_problem_solving"
                                    name="problem_solving" min="1" max="10" step="0.1"
                                    placeholder="Skor problem solving">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="interview_user_overall">Skor Keseluruhan</label>
                                <input type="number" class="form-control" id="interview_user_overall"
                                    name="overall_score" min="0" max="100" step="0.01"
                                    placeholder="Skor keseluruhan" readonly>
                            </div>
                            <div class="alert alert-info">
                                <strong>Kriteria:</strong><br>
                                • ≥ 75: Lulus<br>
                                • < 75: Tidak Lulus </div>
                                    <div class="form-group">
                                        <label for="interview_user_notes">Catatan Interview</label>
                                        <textarea class="form-control" id="interview_user_notes" name="notes" rows="4"
                                            placeholder="Masukkan catatan interview (opsional)"></textarea>
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

<!-- MCU Modal -->
<div class="modal fade" id="mcuModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-user-md"></i> Medical Check Up Assessment
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="mcuForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="mcu_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mcu_blood_pressure">Tekanan Darah</label>
                                <input type="text" class="form-control" id="mcu_blood_pressure"
                                    name="blood_pressure" placeholder="Contoh: 120/80 mmHg">
                            </div>
                            <div class="form-group">
                                <label for="mcu_heart_rate">Detak Jantung</label>
                                <input type="number" class="form-control" id="mcu_heart_rate" name="heart_rate"
                                    min="40" max="200" placeholder="BPM">
                            </div>
                            <div class="form-group">
                                <label for="mcu_blood_sugar">Gula Darah</label>
                                <input type="number" class="form-control" id="mcu_blood_sugar" name="blood_sugar"
                                    min="0" step="0.1" placeholder="mg/dL">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="mcu_overall_health">Kondisi Kesehatan Keseluruhan</label>
                                <select class="form-control" id="mcu_overall_health" name="overall_health">
                                    <option value="">Pilih kondisi</option>
                                    <option value="fit">Fit</option>
                                    <option value="unfit">Unfit</option>
                                    <option value="conditional">Conditional</option>
                                </select>
                            </div>
                            <div class="alert alert-info">
                                <strong>Kriteria:</strong><br>
                                • Fit: Lulus<br>
                                • Conditional: Perlu evaluasi lebih lanjut<br>
                                • Unfit: Tidak Lulus
                            </div>
                            <div class="form-group">
                                <label for="mcu_notes">Catatan MCU</label>
                                <textarea class="form-control" id="mcu_notes" name="notes" rows="4"
                                    placeholder="Masukkan catatan MCU (opsional)"></textarea>
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

<!-- Offering Modal -->
<div class="modal fade" id="offeringModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-handshake"></i> Offering Stage
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="offeringForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="offering_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="offering_salary">Salary Offered</label>
                                <input type="number" class="form-control" id="offering_salary" name="salary"
                                    min="0" step="1000" placeholder="Masukkan gaji yang ditawarkan">
                            </div>
                            <div class="form-group">
                                <label for="offering_position">Position Offered</label>
                                <input type="text" class="form-control" id="offering_position" name="position"
                                    placeholder="Masukkan posisi yang ditawarkan">
                            </div>
                            <div class="form-group">
                                <label for="offering_start_date">Start Date</label>
                                <input type="date" class="form-control" id="offering_start_date"
                                    name="start_date">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="offering_status">Status Offering</label>
                                <select class="form-control" id="offering_status" name="status">
                                    <option value="">Pilih status</option>
                                    <option value="accepted">Accepted</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="negotiating">Negotiating</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="offering_notes">Catatan Offering</label>
                                <textarea class="form-control" id="offering_notes" name="notes" rows="4"
                                    placeholder="Masukkan catatan offering (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Offering</button>
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
            <form id="hireForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="hire_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hire_date">Hire Date *</label>
                                <input type="date" class="form-control" id="hire_date" name="hire_date" required>
                            </div>
                            <div class="form-group">
                                <label for="hire_employee_id">Employee ID</label>
                                <input type="text" class="form-control" id="hire_employee_id" name="employee_id"
                                    placeholder="Masukkan Employee ID">
                            </div>
                            <div class="form-group">
                                <label for="hire_contract_type">Contract Type</label>
                                <select class="form-control" id="hire_contract_type" name="contract_type">
                                    <option value="">Pilih tipe kontrak</option>
                                    <option value="permanent">Permanent</option>
                                    <option value="contract">Contract</option>
                                    <option value="probation">Probation</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="hire_department">Department</label>
                                <input type="text" class="form-control" id="hire_department" name="department"
                                    placeholder="Masukkan department">
                            </div>
                            <div class="form-group">
                                <label for="hire_position">Position</label>
                                <input type="text" class="form-control" id="hire_position" name="position"
                                    placeholder="Masukkan posisi">
                            </div>
                            <div class="form-group">
                                <label for="hire_notes">Catatan Hire</label>
                                <textarea class="form-control" id="hire_notes" name="notes" rows="4"
                                    placeholder="Masukkan catatan hire (opsional)"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit Hire</button>
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
            <form id="onboardingForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="onboarding_session_id" name="session_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="onboarding_start_date">Onboarding Start Date</label>
                                <input type="date" class="form-control" id="onboarding_start_date"
                                    name="start_date">
                            </div>
                            <div class="form-group">
                                <label for="onboarding_duration">Duration (days)</label>
                                <input type="number" class="form-control" id="onboarding_duration" name="duration"
                                    min="1" placeholder="Masukkan durasi onboarding">
                            </div>
                            <div class="form-group">
                                <label for="onboarding_mentor">Mentor</label>
                                <input type="text" class="form-control" id="onboarding_mentor" name="mentor"
                                    placeholder="Masukkan nama mentor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="onboarding_status">Onboarding Status</label>
                                <select class="form-control" id="onboarding_status" name="status">
                                    <option value="">Pilih status</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="extended">Extended</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="onboarding_notes">Catatan Onboarding</label>
                                <textarea class="form-control" id="onboarding_notes" name="notes" rows="4"
                                    placeholder="Masukkan catatan onboarding (opsional)"></textarea>
                            </div>
                        </div>
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
