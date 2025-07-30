@extends('layouts.main')

@section('title', 'Approval Request Details')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Approval Request Details</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('approval.requests.index') }}">Approval
                                    Requests</a></li>
                            <li class="breadcrumb-item active">Details</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    {{ ucfirst(str_replace('_', ' ', $document_type)) }} -
                                    {{ $document_data->nomor ?? 'N/A' }}
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-sm btn-success"
                                        onclick="showApprovalModal({{ $document->id }})">
                                        <i class="fas fa-check"></i> Approve/Reject
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Document Details based on type -->
                                @if ($document_type == 'officialtravel')
                                    @include('approvals-request.partials.officialtravel-details')
                                @elseif($document_type == 'recruitment_request')
                                    @include('approvals-request.partials.recruitment-request-details')
                                @endif

                                <!-- Approval History -->
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <h5>Approval History</h5>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Approver</th>
                                                        <th>Status</th>
                                                        <th>Remarks</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($document->approval_plans as $plan)
                                                        <tr>
                                                            <td>{{ $plan->approver->name }}</td>
                                                            <td>
                                                                @switch($plan->status)
                                                                    @case(0)
                                                                        <span class="badge badge-warning">Pending</span>
                                                                    @break

                                                                    @case(1)
                                                                        <span class="badge badge-success">Approved</span>
                                                                    @break

                                                                    @case(2)
                                                                        <span class="badge badge-info">Revised</span>
                                                                    @break

                                                                    @case(3)
                                                                        <span class="badge badge-danger">Rejected</span>
                                                                    @break

                                                                    @case(4)
                                                                        <span class="badge badge-secondary">Cancelled</span>
                                                                    @break

                                                                    @default
                                                                        <span class="badge badge-secondary">Unknown</span>
                                                                @endswitch
                                                            </td>
                                                            <td>{{ $plan->remarks ?? '-' }}</td>
                                                            <td>{{ $plan->updated_at ? $plan->updated_at->format('d-M-Y H:i:s') : '-' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Approval Decision Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="approvalForm">
                    @csrf
                    <input type="hidden" id="approval_plan_id" name="approval_plan_id" value="{{ $document->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalModalLabel">Approval Decision</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="status">Decision <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="">Select Decision</option>
                                <option value="1">Approve</option>
                                <option value="2">Revise</option>
                                <option value="3">Reject</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="3" placeholder="Enter approval remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Decision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Show approval modal
            window.showApprovalModal = function(id) {
                $('#approval_plan_id').val(id);
                $('#approvalModal').modal('show');
            };

            // Approval form submission
            $('#approvalForm').on('submit', function(e) {
                e.preventDefault();

                var id = $('#approval_plan_id').val();
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('approval.plans.update', '') }}/" + id,
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#approvalModal').modal('hide');
                            // Reload page to show updated approval history
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('An error occurred while processing the request.');
                        }
                    }
                });
            });
        });
    </script>
@endpush
