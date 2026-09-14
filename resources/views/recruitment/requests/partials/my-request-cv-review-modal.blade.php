<div class="modal fade" id="myRequestCvReviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="fas fa-file-alt"></i> CV Review — <span id="my_request_cv_review_candidate"></span>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="myRequestCvReviewForm" method="POST" action=""
                data-confirm-message="Submit this CV Review decision? You cannot edit after submission.">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="my_request_cv_review_decision" name="decision" required>

                    <div class="form-group">
                        <label class="form-label mb-2">
                            <strong>CV Review Decision</strong> <span class="text-danger">*</span>
                        </label>
                        <div class="decision-buttons">
                            <button type="button" class="btn decision-btn btn-outline-success my-request-cv-decision"
                                data-status="recommended">
                                <i class="fas fa-check-circle"></i> Recommended
                            </button>
                            <button type="button" class="btn decision-btn btn-outline-danger my-request-cv-decision"
                                data-status="not_recommended">
                                <i class="fas fa-times-circle"></i> Not Recommended
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="my_request_cv_review_reviewed_at" class="font-weight-bold">Review Date <span
                                class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control" id="my_request_cv_review_reviewed_at"
                            name="reviewed_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                    </div>

                    <div class="form-group mb-0">
                        <label for="my_request_cv_review_notes" class="font-weight-bold">Notes <span
                                class="text-danger">*</span></label>
                        <textarea class="form-control" id="my_request_cv_review_notes" name="notes" rows="3"
                            required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-secondary" id="my_request_cv_review_submit" disabled>
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
