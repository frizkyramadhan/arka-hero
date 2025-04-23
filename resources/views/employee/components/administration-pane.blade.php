@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="administration-pane" class="content" role="tabpanel" aria-labelledby="administration-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Employment History</h5>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-administration"
                title="Add Administration Data">
                <i class="fas fa-plus mr-1"></i> Add Employment
            </button>
            @if ($administrations->isNotEmpty())
                <form action="{{ url('administrations/' . $employee->id) }}" method="post"
                    onsubmit="return confirm('Are you sure want to delete all administration records?')"
                    class="d-inline">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger" title="Delete All Administration Records">
                        <i class="fas fa-trash mr-1"></i> Delete All
                    </button>
                </form>
            @endif
        </div>
    </div>
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th class="text-center">Status</th>
                    <th class="text-center">NIK</th>
                    <th>POH</th>
                    <th>DOH</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th class="text-center">Project</th>
                    <th>Class</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($administrations as $administration)
                    <tr>
                        <td class="text-center">
                            @if ($administration->is_active == 1)
                                <span class="badge-status active">Active</span>
                            @else
                                <form
                                    action="{{ url('administrations/changeStatus/' . $employee->id . '/' . $administration->id) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="badge-status inactive">Inactive</button>
                                </form>
                            @endif
                        </td>
                        <td class="text-center">{{ $administration->nik }}</td>
                        <td>{{ $administration->poh }}</td>
                        <td>{{ $administration->doh ? date('d M Y', strtotime($administration->doh)) : '-' }}
                        </td>
                        <td>{{ $administration->department_name }}</td>
                        <td>{{ $administration->position_name }}</td>
                        <td>{{ $administration->project_code }}</td>
                        <td>{{ $administration->class }}</td>
                        <td class="action-buttons">
                            <button class="btn btn-primary btn-action" data-toggle="modal"
                                data-target="#modal-administration-{{ $administration->id }}">
                                <i class="fas fa-pen-square"></i>
                            </button>
                            <form action="{{ url('administrations/' . $employee->id . '/' . $administration->id) }}"
                                method="post" onsubmit="return confirm('Are you sure want to delete this record?')"
                                class="d-inline">
                                @method('delete')
                                @csrf
                                <button class="btn btn-danger btn-action">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="fas fa-exclamation-circle"></i>
                                <h6>No Data Available</h6>
                                <p>No administration records found for this employee</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
    .overlay-wrapper {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1000;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        align-items: center;
        padding-top: 2rem;
    }

    .overlay i {
        color: #007bff;
    }

    .content {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease-in-out;
        position: relative;
    }

    .content.loaded {
        opacity: 1;
        visibility: visible;
    }

    /* Table Styles */
    .table-modern {
        width: 100%;
        margin-bottom: 1rem;
        background-color: transparent;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table-modern thead th {
        background-color: #f8f9fa;
        color: #495057;
        font-weight: 600;
        padding: 1rem;
        border-bottom: 2px solid #dee2e6;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .table-modern tbody tr {
        transition: all 0.2s ease;
    }

    .table-modern tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #dee2e6;
        color: #212529;
    }

    .table-modern .action-buttons {
        white-space: nowrap;
        text-align: center;
    }

    .table-modern .action-buttons .btn {
        padding: 0.375rem 0.75rem;
        margin: 0 0.25rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }

    .table-modern .action-buttons .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table-modern .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
        border-radius: 0.25rem;
    }

    .table-modern .text-center {
        text-align: center;
    }

    .table-modern .text-muted {
        color: #6c757d !important;
    }

    .table-modern .empty-state {
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }

    .table-modern .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #adb5bd;
    }

    /* Status Badges */
    .badge-status {
        padding: 0.5em 0.75em;
        font-weight: 500;
        border-radius: 0.25rem;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .badge-status.active {
        background-color: #28a745;
        color: white;
    }

    .badge-status.inactive {
        background-color: #dc3545;
        color: white;
    }

    /* Action Buttons */
    .btn-action {
        padding: 0.375rem 0.75rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
        margin: 0 0.25rem;
    }

    .btn-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-action i {
        margin-right: 0.25rem;
    }

    /* Empty State */
    .empty-state {
        padding: 2rem;
        text-align: center;
        background-color: #f8f9fa;
        border-radius: 0.25rem;
        margin: 1rem 0;
    }

    .empty-state i {
        font-size: 2.5rem;
        color: #adb5bd;
        margin-bottom: 1rem;
    }

    .empty-state h6 {
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #adb5bd;
        margin-bottom: 0;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const administrationPane = document.getElementById('administration-pane');
        const administrationTrigger = document.getElementById('administration-pane-trigger');

        if (administrationTrigger) {
            administrationTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                administrationPane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    administrationPane.removeChild(overlay);
                    // Show content
                    administrationPane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
