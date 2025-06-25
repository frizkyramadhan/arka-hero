<div class="text-center">
    <a href="{{ route('employee.registration.admin.show', $registration->id) }}" class="btn btn-sm btn-primary me-1"
        title="View Details">
        <i class="fas fa-eye"></i>
    </a>

    <button type="button" class="btn btn-sm btn-success btn-approve me-1" data-id="{{ $registration->id }}"
        title="Approve">
        <i class="fas fa-check"></i>
    </button>

    <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="{{ $registration->id }}" title="Reject">
        <i class="fas fa-times"></i>
    </button>
</div>

<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();

        // Approve button
        $('.btn-approve').on('click', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Approve Registration?',
                text: 'Add approval notes (optional):',
                input: 'textarea',
                inputPlaceholder: 'Enter approval notes...',
                showCancelButton: true,
                confirmButtonText: 'Approve',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    approveRegistration(id, result.value || '');
                }
            });
        });

        // Reject button
        $('.btn-reject').on('click', function() {
            let id = $(this).data('id');

            Swal.fire({
                title: 'Reject Registration?',
                text: 'Please provide rejection reason:',
                input: 'textarea',
                inputPlaceholder: 'Enter rejection reason...',
                showCancelButton: true,
                confirmButtonText: 'Reject',
                cancelButtonText: 'Cancel',
                inputValidator: (value) => {
                    if (!value) {
                        return 'You need to provide a rejection reason!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    rejectRegistration(id, result.value);
                }
            });
        });

        function approveRegistration(id, notes) {
            // Create and submit form
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('employee.registration.admin.approve', ':id') }}`.replace(':id', id);

            let csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            if (notes) {
                let notesField = document.createElement('input');
                notesField.type = 'hidden';
                notesField.name = 'admin_notes';
                notesField.value = notes;
                form.appendChild(notesField);
            }

            document.body.appendChild(form);
            form.submit();
        }

        function rejectRegistration(id, notes) {
            // Create and submit form
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ route('employee.registration.admin.reject', ':id') }}`.replace(':id', id);

            let csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            let notesField = document.createElement('input');
            notesField.type = 'hidden';
            notesField.name = 'admin_notes';
            notesField.value = notes;
            form.appendChild(notesField);

            document.body.appendChild(form);
            form.submit();
        }
    });
</script>
