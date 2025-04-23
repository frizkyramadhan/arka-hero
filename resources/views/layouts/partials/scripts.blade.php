<!-- jQuery -->
<script src="{{ asset('assets/plugins/jquery/jquery.min.js') }}"></script>
<!-- jQuery UI 1.11.4 -->
<script src="{{ asset('assets/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
    $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('assets/dist/js/adminlte.js') }}"></script>
<!-- pace-progress -->
{{-- <script src="{{ asset('assets/plugins/pace-progress/pace.min.js') }}"></script> --}}
@yield('scripts')
@stack('scripts')
@include('sweetalert::alert')
<!-- SweetAlert2 -->
<script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
    // Check for SweetAlert2 messages
    document.addEventListener('DOMContentLoaded', function() {
        // Handle toast_error from exception handler
        @if (session('toast_error'))
            Swal.fire({
                icon: '{{ session('alert_type') ?? 'error' }}',
                title: '{{ session('alert_title') ?? 'Error' }}',
                text: '{{ session('toast_error') }}',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        @endif

        // Handle toast_success
        @if (session('toast_success'))
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '{{ session('toast_success') }}',
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
        @endif
    });
</script>

</body>

</html>
