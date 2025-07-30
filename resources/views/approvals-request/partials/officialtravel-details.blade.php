<div class="row">
    <div class="col-md-6">
        <h5>Document Information</h5>
        <table class="table table-borderless">
            <tr>
                <td width="150"><strong>Document Number:</strong></td>
                <td>{{ $document_data->nomor ?? 'N/A' }}</td>
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
        <h5>Travel Information</h5>
        <table class="table table-borderless">
            <tr>
                <td width="150"><strong>Traveler:</strong></td>
                <td>{{ $document_data->traveler->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Project:</strong></td>
                <td>{{ $document_data->project->project_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Department:</strong></td>
                <td>{{ $document_data->department->department_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Travel Date:</strong></td>
                <td>{{ $document_data->official_travel_date ? $document_data->official_travel_date->format('d-M-Y') : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td><strong>Destination:</strong></td>
                <td>{{ $document_data->destination ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Purpose:</strong></td>
                <td>{{ $document_data->purpose ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>
</div>
