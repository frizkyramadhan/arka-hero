<div class="row">
    <div class="col-md-6">
        <h5>Document Information</h5>
        <table class="table table-borderless">
            <tr>
                <td width="150"><strong>Document Number:</strong></td>
                <td>{{ $document_data->nomor ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Request Number:</strong></td>
                <td>{{ $document_data->request_number ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    @switch($document_data->status)
                        @case('draft')
                            <span class="badge badge-secondary">Draft</span>
                        @break

                        @case('submitted')
                            <span class="badge badge-warning">Submitted</span>
                        @break

                        @case('approved')
                            <span class="badge badge-success">Approved</span>
                        @break

                        @case('rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @break

                        @case('revise')
                            <span class="badge badge-info">Revise</span>
                        @break

                        @default
                            <span class="badge badge-secondary">{{ ucfirst($document_data->status ?? 'Unknown') }}</span>
                    @endswitch
                </td>
            </tr>
            <tr>
                <td><strong>Created Date:</strong></td>
                <td>{{ $document_data->created_at ? $document_data->created_at->format('d-M-Y H:i:s') : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Submit Date:</strong></td>
                <td>{{ $document_data->submit_at ? \Carbon\Carbon::parse($document_data->submit_at)->format('d-M-Y H:i:s') : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td><strong>Approved Date:</strong></td>
                <td>{{ $document_data->approved_at ? \Carbon\Carbon::parse($document_data->approved_at)->format('d-M-Y H:i:s') : 'N/A' }}
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h5>Recruitment Information</h5>
        <table class="table table-borderless">
            <tr>
                <td width="150"><strong>Department:</strong></td>
                <td>{{ $document_data->department->department_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Project:</strong></td>
                <td>{{ $document_data->project->project_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Position:</strong></td>
                <td>{{ $document_data->position->position_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Level:</strong></td>
                <td>{{ $document_data->level->level_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Required Qty:</strong></td>
                <td>{{ $document_data->required_qty ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Required Date:</strong></td>
                <td>{{ $document_data->required_date ? $document_data->required_date->format('d-M-Y') : 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Employment Type:</strong></td>
                <td>{{ ucfirst($document_data->employment_type ?? 'N/A') }}</td>
            </tr>
            <tr>
                <td><strong>Request Reason:</strong></td>
                <td>{{ formatRequestReason($document_data->request_reason ?? null, $document_data->other_reason ?? null) }}
                </td>
            </tr>
        </table>
    </div>
</div>
