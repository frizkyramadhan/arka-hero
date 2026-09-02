<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script>
    function suppliesReportFilterParams() {
        return {
            project_id: $('#project_id').val() || '',
            category_id: $('#category_id').val() || '',
            status: $('#status').val() || '',
            doc_type: $('#doc_type').val() || '',
            date_from: $('#date_from').val() || '',
            date_to: $('#date_to').val() || '',
            order_number: $('#order_number').val() || '',
            requester_q: $('#requester_q').val() || '',
            q: $('#q').val() || ''
        };
    }

    function suppliesReportHasFilters(requireProject) {
        const p = suppliesReportFilterParams();
        if (requireProject) {
            return !!p.project_id;
        }
        return !!(p.project_id || p.category_id || p.status || p.doc_type ||
            p.date_from || p.date_to || p.order_number || p.requester_q || p.q);
    }

    function initSuppliesReport(options) {
        const table = $('#report-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ordering: false,
            dom: 'rtip',
            ajax: {
                url: options.dataUrl,
                data: function(d) {
                    return Object.assign(d, suppliesReportFilterParams());
                }
            },
            columns: options.columns
        });

        $('#btn-show-data').on('click', function() {
            if (!options.allowEmptyFilters && !suppliesReportHasFilters(options.requireProject)) {
                if (typeof toast_error === 'function') {
                    toast_error(options.requireProject ? 'Please select a project.' : 'Please apply at least one filter.');
                } else {
                    alert(options.requireProject ? 'Please select a project.' : 'Please apply at least one filter.');
                }
                return;
            }
            table.ajax.reload();
        });

        $('#btn-export-excel').on('click', function(e) {
            e.preventDefault();
            if (!options.allowEmptyFilters && !suppliesReportHasFilters(options.requireProject)) {
                if (typeof toast_error === 'function') {
                    toast_error('Apply filters before exporting.');
                }
                return;
            }
            const params = new URLSearchParams(suppliesReportFilterParams());
            window.location.href = options.exportUrl + '?' + params.toString();
        });
    }
</script>
