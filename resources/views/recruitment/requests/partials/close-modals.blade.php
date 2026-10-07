@can('recruitment-requests.hold')
    @if ($fptk->status === 'approved')
        <div class="modal fade" id="closeFptkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="{{ route('recruitment.requests.close', $fptk->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-dark text-white">
                            <h5 class="modal-title"><i class="fas fa-lock"></i> Close FPTK</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2">Kandidat yang masih <strong>In Process</strong> akan dibatalkan. FPTK dapat dibuka kembali bila diperlukan.</p>
                            <div class="form-group">
                                <label for="close_reason">Close Reason <span class="text-danger">*</span></label>
                                <select name="close_reason" id="close_reason" class="form-control" required>
                                    <option value="">- Pilih alasan -</option>
                                    @foreach (\App\Models\RecruitmentRequest::MANUAL_CLOSE_REASONS as $reason)
                                        <option value="{{ $reason }}" {{ old('close_reason') === $reason ? 'selected' : '' }}>
                                            {{ \App\Models\RecruitmentRequest::CLOSE_REASONS[$reason] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0">
                                <label for="close_notes">Notes <span class="text-danger">*</span></label>
                                <textarea name="close_notes" id="close_notes" class="form-control" rows="3" required maxlength="2000"
                                    placeholder="Contoh: posisi diisi promosi karyawan existing / FPTK dibatalkan karena perubahan rencana kerja">{{ old('close_notes') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark">Confirm Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @elseif ($fptk->canBeReopened())
        <div class="modal fade" id="reopenFptkModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="{{ route('recruitment.requests.reopen', $fptk->id) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="fas fa-lock-open"></i> Reopen FPTK</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2">FPTK kembali ke status <strong>Approved</strong>. Sesi kandidat yang sudah dibatalkan tidak dipulihkan.</p>
                            <div class="form-group mb-0">
                                <label for="reopen_reason">Reopen Reason <span class="text-danger">*</span></label>
                                <textarea name="reopen_reason" id="reopen_reason" class="form-control" rows="3" required maxlength="2000"
                                    placeholder="Alasan FPTK dibuka kembali">{{ old('reopen_reason') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Confirm Reopen</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endcan
