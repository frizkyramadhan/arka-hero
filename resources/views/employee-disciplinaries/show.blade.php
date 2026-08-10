@extends('layouts.main')

@section('content')
    @php
        $statusMap = [
            'active' => ['label' => 'Active', 'class' => 'badge badge-success', 'icon' => 'fa-check-circle'],
            'expired' => ['label' => 'Expired', 'class' => 'badge badge-secondary', 'icon' => 'fa-hourglass-end'],
            'superseded' => ['label' => 'Superseded', 'class' => 'badge badge-warning', 'icon' => 'fa-exchange-alt'],
            'terminated' => ['label' => 'Terminated', 'class' => 'badge badge-danger', 'icon' => 'fa-user-slash'],
        ];
        $pill = $statusMap[$record->status] ?? [
            'label' => ucfirst($record->status),
            'class' => 'badge badge-secondary',
            'icon' => 'fa-question-circle',
        ];
        $projectLabel = '-';
        if ($administration && $administration->project) {
            $projectLabel = collect([
                $administration->project->project_code ?? null,
                $administration->project->project_name ?? null,
            ])->filter()->implode(' - ') ?: '-';
        }
    @endphp

    <div class="content-wrapper-custom">
        <div class="disc-header">
            <div class="disc-header-content">
                <div class="disc-employee">{{ $record->employee->fullname ?? '-' }}</div>
                <h1 class="disc-title">{{ $record->type_label }}</h1>
                <div class="disc-meta">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $record->effective_date->format('d M Y') }}
                    &mdash;
                    {{ $record->end_date->format('d M Y') }}
                    @if ($record->status === 'active')
                        <span class="ml-2">({{ $record->remaining_days }} days remaining)</span>
                    @endif
                </div>
                <div class="disc-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <div class="disc-content">
            <div class="row">
                <div class="col-lg-8">
                    <div class="disc-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-tie"></i> Employee Administration</h2>
                            <div class="card-tools">
                                <a href="{{ !empty($personalMode) ? route('employee-disciplinaries.my-records') : route('employee-disciplinaries.index') }}"
                                    class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                                @if (empty($personalMode))
                                    @can('employee-disciplinaries.edit')
                                        <a href="{{ route('employee-disciplinaries.edit', $record->id) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    @endcan
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #007bff;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Employee</div>
                                        <div class="info-value">{{ $record->employee->fullname ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #6c757d;">
                                        <i class="fas fa-id-card"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">ID Card (NIK KTP)</div>
                                        <div class="info-value">{{ $record->employee->identity_card ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #17a2b8;">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">NIK</div>
                                        <div class="info-value">{{ $administration->nik ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #28a745;">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Position</div>
                                        <div class="info-value">
                                            {{ optional(optional($administration)->position)->position_name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #fd7e14;">
                                        <i class="fas fa-sitemap"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Department</div>
                                        <div class="info-value">
                                            {{ optional(optional(optional($administration)->position)->department)->department_name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #6f42c1;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Project</div>
                                        <div class="info-value">{{ $projectLabel }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="disc-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Disciplinary Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #007bff;">
                                        <i class="fas fa-gavel"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Type</div>
                                        <div class="info-value">{{ $record->type_label }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #28a745;">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Effective Date</div>
                                        <div class="info-value">{{ $record->effective_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #dc3545;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Valid Until</div>
                                        <div class="info-value">{{ $record->end_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #ffc107;">
                                        <i class="fas fa-user-edit"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Created By</div>
                                        <div class="info-value">{{ $record->creator->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="description-section">
                                <h5><i class="fas fa-list-ul"></i> PP Criteria</h5>
                                @forelse ($record->criteria as $criterion)
                                    <div class="criteria-row">
                                        <span class="badge badge-primary">{{ $criterion->code }}</span>
                                        <span>{{ $criterion->title }}</span>
                                        @if ($criterion->article_reference)
                                            <small class="text-muted d-block mt-1">{{ $criterion->article_reference }}</small>
                                        @endif
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">-</p>
                                @endforelse
                            </div>

                            @if ($record->pp_notes)
                                <div class="description-section">
                                    <h5><i class="fas fa-sticky-note"></i> PP Notes</h5>
                                    <p>{{ $record->pp_notes }}</p>
                                </div>
                            @endif

                            <div class="description-section">
                                <h5><i class="fas fa-align-left"></i> Reason / Description</h5>
                                <p>{{ $record->reason }}</p>
                            </div>

                            <div class="document-section">
                                <h5><i class="fas fa-file-alt"></i> Supporting Document</h5>
                                @if ($record->document_path)
                                    <div class="document-actions">
                                        <a href="{{ route('employee-disciplinaries.download', $record->id) }}"
                                            class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Download Document
                                        </a>
                                    </div>
                                @elseif ($record->allowsDeferredDocument() && empty($personalMode))
                                    <p class="text-warning mb-2">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        Imported record — document not uploaded yet.
                                    </p>
                                    @can('employee-disciplinaries.edit')
                                        <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                            data-target="#uploadDocumentModal">
                                            <i class="fas fa-file-upload"></i> Upload Document
                                        </button>
                                    @endcan
                                @else
                                    <p class="text-muted mb-0">No document uploaded.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="disc-card">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-bar"></i> Summary</h2>
                        </div>
                        <div class="card-body">
                            <div class="statistics-grid">
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #007bff;">
                                        <i class="fas fa-info"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Status</div>
                                        <div class="stat-value">{{ $pill['label'] }}</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #28a745;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Remaining Days</div>
                                        <div class="stat-value">
                                            {{ $record->status === 'active' ? $record->remaining_days . ' days' : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #6f42c1;">
                                        <i class="fas fa-list"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">PP Criteria Count</div>
                                        <div class="stat-value">{{ $record->criteria->count() }}</div>
                                    </div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-icon" style="background-color: #17a2b8;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="stat-content">
                                        <div class="stat-label">Project</div>
                                        <div class="stat-value">
                                            {{ optional(optional($administration)->project)->project_code ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                                @if ($record->isImported())
                                    <div class="stat-item">
                                        <div class="stat-icon" style="background-color: #fd7e14;">
                                            <i class="fas fa-file-import"></i>
                                        </div>
                                        <div class="stat-content">
                                            <div class="stat-label">Imported (doc later)</div>
                                            <div class="stat-value">
                                                {{ $record->imported_at->format('d M Y H:i') }}
                                            </div>
                                            <small class="text-muted">Created via Excel import</small>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($record->allowsDeferredDocument() && empty($personalMode))
        @can('employee-disciplinaries.edit')
            <div class="modal fade" id="uploadDocumentModal" tabindex="-1" role="dialog"
                aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Supporting Document</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('employee-disciplinaries.upload-document', $record->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group mb-0">
                                    <label for="document">Document <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control-file" id="document" name="document"
                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                    <small class="form-text text-muted">Allowed: pdf, doc, docx, jpg, jpeg, png (max 5MB).</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-file-upload"></i> Upload
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif
@endsection

@section('styles')
    <style>
        .content-wrapper-custom {
            margin: -15px -15px 0;
        }

        .disc-header {
            position: relative;
            height: 120px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 20px 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .disc-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .disc-employee {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 4px;
            letter-spacing: 1px;
        }

        .disc-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0 0 6px;
        }

        .disc-meta {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .disc-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .disc-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .disc-content {
            padding: 0 20px 20px;
        }

        .disc-card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-head {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-head h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .disc-card .card-body {
            padding: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 15px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 12px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
        }

        .info-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
            flex-shrink: 0;
        }

        .info-content {
            flex: 1;
            min-width: 0;
        }

        .info-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 4px;
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: #2c3e50;
            font-weight: 600;
            word-break: break-word;
        }

        .statistics-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid #007bff;
        }

        .stat-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 2px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 0.9rem;
            color: #2c3e50;
            font-weight: 600;
            word-break: break-word;
        }

        .description-section,
        .document-section {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .description-section h5,
        .document-section h5 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .criteria-row {
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .criteria-row:last-child {
            border-bottom: none;
        }

        @media (max-width: 768px) {
            .disc-header {
                height: auto;
                padding: 15px;
            }

            .disc-header-content {
                padding-right: 80px;
            }

            .disc-title {
                font-size: 1.25rem;
            }

            .disc-status-pill {
                top: 15px;
                right: 15px;
            }

            .card-head {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
@endsection
