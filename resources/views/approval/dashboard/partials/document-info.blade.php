@php
    $documentType = $approval->document_type;
    $documentId = $approval->document_id;
@endphp

@if ($documentType === 'officialtravel')
    @php
        $document = \App\Models\Officialtravel::find($documentId);
    @endphp
    @if ($document)
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Employee:</strong></td>
                        <td>{{ $document->employee->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Destination:</strong></td>
                        <td>{{ $document->destination }}</td>
                    </tr>
                    <tr>
                        <td><strong>Purpose:</strong></td>
                        <td>{{ $document->purpose }}</td>
                    </tr>
                    <tr>
                        <td><strong>Start Date:</strong></td>
                        <td>{{ $document->start_date ? \Carbon\Carbon::parse($document->start_date)->format('M d, Y') : 'N/A' }}
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>End Date:</strong></td>
                        <td>{{ $document->end_date ? \Carbon\Carbon::parse($document->end_date)->format('M d, Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Duration:</strong></td>
                        <td>{{ $document->duration ?? 'N/A' }} days</td>
                    </tr>
                    <tr>
                        <td><strong>Budget:</strong></td>
                        <td>{{ $document->budget ? 'Rp ' . number_format($document->budget, 0, ',', '.') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span
                                class="badge badge-{{ $document->recommendation_status === 'approved' ? 'success' : ($document->recommendation_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($document->recommendation_status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Document not found
        </div>
    @endif
@elseif($documentType === 'recruitment_request')
    @php
        $document = \App\Models\RecruitmentRequest::find($documentId);
    @endphp
    @if ($document)
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Position:</strong></td>
                        <td>{{ $document->position }}</td>
                    </tr>
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td>{{ $document->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Quantity:</strong></td>
                        <td>{{ $document->quantity }} person(s)</td>
                    </tr>
                    <tr>
                        <td><strong>Urgency:</strong></td>
                        <td>
                            <span
                                class="badge badge-{{ $document->urgency === 'high' ? 'danger' : ($document->urgency === 'medium' ? 'warning' : 'success') }}">
                                {{ ucfirst($document->urgency ?? 'normal') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Requested By:</strong></td>
                        <td>{{ $document->requested_by ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Request Date:</strong></td>
                        <td>{{ $document->request_date ? \Carbon\Carbon::parse($document->request_date)->format('M d, Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Reason:</strong></td>
                        <td>{{ $document->reason ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span
                                class="badge badge-{{ $document->known_status === 'approved' ? 'success' : ($document->known_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($document->known_status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Document not found
        </div>
    @endif
@elseif($documentType === 'employee_registration')
    @php
        $document = \App\Models\EmployeeRegistration::find($documentId);
    @endphp
    @if ($document)
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Employee Name:</strong></td>
                        <td>{{ $document->employee_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $document->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>{{ $document->phone }}</td>
                    </tr>
                    <tr>
                        <td><strong>Position:</strong></td>
                        <td>{{ $document->position }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Department:</strong></td>
                        <td>{{ $document->department->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Join Date:</strong></td>
                        <td>{{ $document->join_date ? \Carbon\Carbon::parse($document->join_date)->format('M d, Y') : 'N/A' }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>
                            <span
                                class="badge badge-{{ $document->status === 'approved' ? 'success' : ($document->status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($document->status ?? 'pending') }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Admin Notes:</strong></td>
                        <td>{{ $document->admin_notes ?? 'No notes' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    @else
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Document not found
        </div>
    @endif
@else
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        Document type: {{ ucfirst(str_replace('_', ' ', $documentType)) }}
        <br>
        Document ID: {{ $documentId }}
        <br>
        <small class="text-muted">Detailed information not available for this document type.</small>
    </div>
@endif
