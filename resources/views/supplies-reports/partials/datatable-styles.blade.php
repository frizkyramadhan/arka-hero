<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<style>
    .supplies-report-table { font-size: 0.875rem; }
    .supplies-report-table thead th { white-space: nowrap; font-weight: 600; }
    .card-outline.card-{{ $accent ?? 'primary' }} { border-top: 3px solid var(--primary, #007bff); }
</style>
