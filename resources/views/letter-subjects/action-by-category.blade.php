<a class="btn btn-icon btn-primary mr-1" href="#" data-toggle="modal" data-target="#modal-edit-{{ $subject->id }}"
    title="Edit Subject">
    <i class="fas fa-pen-square"></i>
</a>
<button class="btn btn-icon btn-danger" onclick="deleteSubject({{ $subject->id }}, '{{ $subject->subject_name }}')"
    title="Delete Subject">
    <i class="fas fa-times"></i>
</button>

<!-- Edit Modal -->
<div class="modal fade text-left" id="modal-edit-{{ $subject->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Letter Subject</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-edit-subject-{{ $subject->id }}"
                onsubmit="updateSubjectForm(event, {{ $subject->id }}, '{{ $subject->category_code }}')"
                data-update-url="{{ route('letter-subjects.update-by-category', [$subject->category_code, $subject->id]) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Category</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control"
                                        value="{{ $subject->category->category_name ?? '' }} ({{ $subject->category_code }})"
                                        readonly>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Subject Name <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control" name="subject_name"
                                        value="{{ old('subject_name', $subject->subject_name) }}"
                                        placeholder="e.g., Surat Perjalanan Dinas" maxlength="200" required>
                                    <div class="invalid-feedback" id="error-edit-subject_name-{{ $subject->id }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Status <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select name="is_active" class="form-control" required>
                                        <option value="1"
                                            {{ old('is_active', $subject->is_active) == '1' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="0"
                                            {{ old('is_active', $subject->is_active) == '0' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="error-edit-is_active-{{ $subject->id }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Update subject form submission
    function updateSubjectForm(event, subjectId, categoryCode) {
        event.preventDefault();

        var form = $('#form-edit-subject-' + subjectId);

        // Clear previous errors
        form.find('.form-control').removeClass('is-invalid');
        form.find('.invalid-feedback').text('');

        // Get form data as regular object
        var formData = {
            subject_name: form.find('input[name="subject_name"]').val(),
            is_active: form.find('select[name="is_active"]').val(),
            _token: $('meta[name="csrf-token"]').attr('content'),
            _method: 'PATCH'
        };

        var updateUrl = form.data('update-url');

        $.ajax({
            url: updateUrl,
            type: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            success: function(response) {
                if (response && response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Subject updated successfully',
                        confirmButtonColor: '#3085d6'
                    });
                    $('#modal-edit-' + subjectId).modal('hide');
                    $('#subjects-table').DataTable().draw();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unexpected response format',
                        confirmButtonColor: '#3085d6'
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON ? xhr.responseJSON.errors : {};
                    $.each(errors, function(key, value) {
                        form.find('input[name="' + key + '"], select[name="' + key + '"]').addClass(
                            'is-invalid');
                        $('#error-edit-' + key + '-' + subjectId).text(value[0]);
                    });
                } else {
                    var response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: (response && response.message) ||
                            'An error occurred while updating subject',
                        confirmButtonColor: '#3085d6'
                    });
                }
            }
        });
    }
</script>
