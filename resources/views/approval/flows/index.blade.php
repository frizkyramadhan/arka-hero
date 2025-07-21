@extends('layouts.main')

@section('title', 'Approval Flows')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Approval Flow Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">Approval Flows</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Approval Flows</h3>
                            <div class="card-tools">
                                <a href="{{ route('approval.flows.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create New Flow
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Search and Filter -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <form method="GET" action="{{ route('approval.flows.index') }}" class="form-inline">
                                        <div class="input-group">
                                            <input type="text" name="search" class="form-control"
                                                placeholder="Search flows..." value="{{ request('search') }}">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-outline-secondary">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('approval.flows.index') }}"
                                            class="btn btn-outline-secondary {{ !request('is_active') ? 'active' : '' }}">
                                            All
                                        </a>
                                        <a href="{{ route('approval.flows.index', ['is_active' => 1]) }}"
                                            class="btn btn-outline-secondary {{ request('is_active') == '1' ? 'active' : '' }}">
                                            Active
                                        </a>
                                        <a href="{{ route('approval.flows.index', ['is_active' => 0]) }}"
                                            class="btn btn-outline-secondary {{ request('is_active') == '0' ? 'active' : '' }}">
                                            Inactive
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Flows Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Document Type</th>
                                            <th>Stages</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Created At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($flows as $flow)
                                            <tr>
                                                <td>
                                                    <strong>{{ $flow->name }}</strong>
                                                    @if ($flow->description)
                                                        <br><small
                                                            class="text-muted">{{ Str::limit($flow->description, 50) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">{{ $flow->document_type }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-secondary">{{ $flow->stages->count() }}
                                                        stages</span>
                                                </td>
                                                <td>
                                                    @if ($flow->is_active)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ $flow->creator->name ?? 'Unknown' }}
                                                </td>
                                                <td>
                                                    {{ $flow->created_at->format('M d, Y H:i') }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('approval.flows.show', $flow) }}"
                                                        class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('approval.flows.edit', $flow) }}"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        onclick="showCloneModal({{ $flow->id }}, '{{ $flow->name }}')"
                                                        title="Clone">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete({{ $flow->id }}, '{{ $flow->name }}')"
                                                        title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No approval flows found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $flows->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Clone Modal -->
    <div class="modal fade" id="cloneModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="cloneForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Clone Approval Flow</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="new_name">New Flow Name</label>
                            <input type="text" class="form-control" id="new_name" name="new_name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Clone Flow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Form -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('scripts')
    <script>
        function showCloneModal(flowId, flowName) {
            $('#new_name').val(flowName + ' (Copy)');
            $('#cloneForm').attr('action', '{{ route('approval.flows.index') }}/' + flowId + '/clone');
            $('#cloneModal').modal('show');
        }

        function confirmDelete(flowId, flowName) {
            if (confirm('Are you sure you want to delete the approval flow "' + flowName +
                    '"? This action cannot be undone.')) {
                const form = document.getElementById('deleteForm');
                form.action = '{{ route('approval.flows.index') }}/' + flowId;
                form.submit();
            }
        }
    </script>
@endsection
