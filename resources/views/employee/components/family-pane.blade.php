@php
    $loadingOverlay = '<div class="overlay-wrapper">
        <div class="overlay">
            <i class="fas fa-3x fa-sync-alt fa-spin"></i>
            <div class="text-bold pt-2">Loading...</div>
        </div>
    </div>';
@endphp

<div id="family-pane" class="content" role="tabpanel" aria-labelledby="family-pane-trigger">
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h5 class="mb-0">Families</h5>
        <div>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modal-family" title="Add Family">
                <i class="fas fa-plus mr-1"></i> Add Family
            </button>
            @if ($families->isNotEmpty())
                <form action="{{ url('families/' . $employee->id) }}" method="post"
                    onsubmit="return confirm('Are you sure want to delete all family records?')" class="d-inline">
                    @method('delete')
                    @csrf
                    <button class="btn btn-danger" title="Delete All Family Records">
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
                    <th>Relationship</th>
                    <th>Name</th>
                    <th>Birth Place</th>
                    <th>Birth Date</th>
                    <th>Remarks</th>
                    <th>BPJS Kesehatan</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($families as $family)
                    <tr>
                        <td>{{ $family->family_relationship }}</td>
                        <td>{{ $family->family_name }}</td>
                        <td>{{ $family->family_birthplace }}</td>
                        <td>{{ $family->family_birthdate ? date('d M Y', strtotime($family->family_birthdate)) : '-' }}
                        </td>
                        <td>{{ $family->family_remarks }}</td>
                        <td>{{ $family->bpjsks_no }}</td>
                        <td class="action-buttons">
                            <button class="btn btn-primary btn-action" data-toggle="modal"
                                data-target="#modal-family-{{ $family->id }}">
                                <i class="fas fa-pen-square"></i>
                            </button>
                            <form action="{{ url('families/' . $employee->id . '/' . $family->id) }}" method="post"
                                onsubmit="return confirm('Are you sure want to delete this record?')" class="d-inline">
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
                        <td colspan="7">
                            <div class="empty-state">
                                <i class="fas fa-exclamation-circle"></i>
                                <h6>No Data Available</h6>
                                <p>No family records found for this employee</p>
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

    .empty-state {
        padding: 2rem;
        text-align: center;
        color: #6c757d;
    }

    .empty-state i {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: #adb5bd;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const familyPane = document.getElementById('family-pane');
        const familyTrigger = document.getElementById('family-pane-trigger');

        if (familyTrigger) {
            familyTrigger.addEventListener('click', function() {
                // Show loading overlay
                const overlay = document.createElement('div');
                overlay.className = 'overlay-wrapper';
                overlay.innerHTML = `{!! $loadingOverlay !!}`;
                familyPane.appendChild(overlay);

                // Simulate loading delay (remove this in production)
                setTimeout(() => {
                    // Remove loading overlay
                    familyPane.removeChild(overlay);
                    // Show content
                    familyPane.classList.add('loaded');
                }, 500);
            });
        }
    });
</script>
