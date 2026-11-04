{{-- Shared Reuse-on-FPTK modal (FPTK detail, session hub, candidate detail can include this). --}}
<div class="modal fade" id="reuseOnFptkModal" tabindex="-1" role="dialog" aria-labelledby="reuseOnFptkModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="reuseOnFptkModalLabel">Reuse Candidate on FPTK</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="reuseOnFptkForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" id="reuse_on_fptk_candidate_id" name="candidate_id" value="">
                    <input type="hidden" id="reuse_on_fptk_source_session_id" name="source_session_id" value="">

                    <div class="alert alert-secondary py-2">
                        <div><strong>Candidate:</strong> <span id="reuse_on_fptk_candidate_name">—</span></div>
                        <div><strong>Source session:</strong> <span id="reuse_on_fptk_source_label">—</span></div>
                    </div>

                    <div class="form-group">
                        <label for="reuse_on_fptk_target_id">Target FPTK *</label>
                        <select class="form-control" id="reuse_on_fptk_target_id" name="fptk_id" required>
                            <option value="">Loading available FPTKs…</option>
                        </select>
                        <small class="text-muted">Only Approved FPTKs this candidate has not applied to yet.</small>
                    </div>

                    <div class="form-group">
                        <label>Process mode *</label>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="reuse_on_fptk_mode_copy" name="mode" value="copy"
                                class="custom-control-input" checked>
                            <label class="custom-control-label" for="reuse_on_fptk_mode_copy">
                                Salin proses sebelumnya (copy stages that match the target FPTK flow)
                            </label>
                        </div>
                        <div class="custom-control custom-radio mt-1">
                            <input type="radio" id="reuse_on_fptk_mode_fresh" name="mode" value="fresh"
                                class="custom-control-input">
                            <label class="custom-control-label" for="reuse_on_fptk_mode_fresh">
                                Mulai dari awal (create new process on target FPTK)
                            </label>
                        </div>
                    </div>

                    <div id="reuse_on_fptk_hint" class="alert alert-info d-none mb-0"></div>
                    <p class="text-muted small mt-3 mb-0">
                        Session di FPTK sumber akan di-close / terhenti; histori tetap tersimpan.
                        Progress Summary di FPTK baru dihitung ulang sesuai tahapan FPTK target.
                    </p>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="reuse_on_fptk_submit" disabled>
                        Continue on FPTK
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            (function($) {
                var availableFptksUrlTpl =
                    @json(route('recruitment.candidates.available-fptks', ['id' => '__ID__']));
                var reuseUrlTpl =
                    @json(route('recruitment.candidates.reuse-on-fptk', ['id' => '__ID__']));

                function updateReuseOnFptkHint() {
                    var mode = $('#reuseOnFptkForm input[name="mode"]:checked').val();
                    var $opt = $('#reuse_on_fptk_target_id option:selected');
                    var $hint = $('#reuse_on_fptk_hint');
                    if (!$opt.val()) {
                        $hint.addClass('d-none').text('');
                        return;
                    }
                    var msgs = [];
                    if (mode === 'copy') {
                        msgs.push(
                            'Stages completed on the source session will be copied when valid on the target FPTK.');
                        if (String($opt.data('simplified')) === '1') {
                            msgs.push('Target uses simplified flow (MCU + Hire). Some stages may be skipped.');
                        }
                    } else {
                        msgs.push(
                            'New session starts from the beginning on the target FPTK. Source session will be closed.'
                        );
                    }
                    $hint.removeClass('d-none').text(msgs.join(' '));
                }

                function loadAvailableFptks(candidateId) {
                    var $select = $('#reuse_on_fptk_target_id');
                    var $submit = $('#reuse_on_fptk_submit');
                    $select.html('<option value="">Loading available FPTKs…</option>');
                    $submit.prop('disabled', true);

                    $.getJSON(availableFptksUrlTpl.replace('__ID__', candidateId))
                        .done(function(resp) {
                            var rows = (resp && resp.data) ? resp.data : [];
                            if (!rows.length) {
                                $select.html('<option value="">No available FPTK found</option>');
                                return;
                            }
                            var html = '<option value="">Select FPTK</option>';
                            rows.forEach(function(f) {
                                var label = (f.request_number || '') + ' - ' + (f.position || 'N/A') +
                                    ' (' + (f.department || 'N/A') + ') — ' + String(f.employment_type || '')
                                    .toUpperCase();
                                html += '<option value="' + f.id + '"' +
                                    ' data-employment="' + (f.employment_type || '') + '"' +
                                    ' data-theory="' + (f.requires_theory_test ? '1' : '0') + '"' +
                                    ' data-simplified="' + (f.simplified_flow ? '1' : '0') + '">' +
                                    $('<div>').text(label).html() + '</option>';
                            });
                            $select.html(html);
                            $submit.prop('disabled', false);
                        })
                        .fail(function() {
                            $select.html('<option value="">Failed to load FPTKs</option>');
                        });
                }

                $(document).on('click', '.btn-reuse-on-fptk', function() {
                    var $btn = $(this);
                    var candidateId = $btn.data('candidate-id');
                    var sessionId = $btn.data('session-id');
                    var candidateName = $btn.data('candidate-name') || 'N/A';
                    var sessionNumber = $btn.data('session-number') || sessionId;
                    var fptkNumber = $btn.data('fptk-number') || '';
                    var stage = $btn.data('stage') || '';
                    var status = $btn.data('status') || '';
                    var progress = $btn.data('progress');

                    $('#reuse_on_fptk_candidate_id').val(candidateId);
                    $('#reuse_on_fptk_source_session_id').val(sessionId);
                    $('#reuse_on_fptk_candidate_name').text(candidateName);
                    $('#reuse_on_fptk_source_label').text(
                        sessionNumber +
                        (fptkNumber ? ' — ' + fptkNumber : '') +
                        (status ? ' (' + String(status).replace(/_/g, ' ') : '') +
                        (stage ? ', ' + String(stage).replace(/_/g, ' ') : '') +
                        (progress !== undefined && progress !== '' ? ', ' + progress + '%' : '') +
                        (status ? ')' : '')
                    );
                    $('#reuse_on_fptk_mode_copy').prop('checked', true);
                    $('#reuse_on_fptk_hint').addClass('d-none').text('');
                    loadAvailableFptks(candidateId);
                    $('#reuseOnFptkModal').modal('show');
                });

                $(document).on('change', '#reuse_on_fptk_target_id, #reuseOnFptkForm input[name="mode"]',
                    updateReuseOnFptkHint);

                $('#reuseOnFptkForm').on('submit', function(e) {
                    e.preventDefault();
                    var candidateId = $('#reuse_on_fptk_candidate_id').val();
                    var $submit = $('#reuse_on_fptk_submit');
                    $submit.prop('disabled', true);

                    $.ajax({
                        url: reuseUrlTpl.replace('__ID__', candidateId),
                        type: 'POST',
                        data: $(this).serialize(),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            $('#reuseOnFptkModal').modal('hide');
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                location.reload();
                            }
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message || 'Candidate reused on FPTK');
                            }
                        },
                        error: function(xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ?
                                xhr.responseJSON.message :
                                'Error reusing candidate on FPTK';
                            if (typeof toastr !== 'undefined') {
                                toastr.error(msg);
                            } else {
                                alert(msg);
                            }
                            $submit.prop('disabled', false);
                        }
                    });
                });
            })(jQuery);
        </script>
    @endpush
@endonce
