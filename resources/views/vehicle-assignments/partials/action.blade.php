<div class="btn-group">
    <a href="{{ route('vehicle-assignments.show', $model) }}" class="btn btn-sm btn-info" title="View">
        <i class="fas fa-eye"></i>
    </a>
    @can('vehicle-assignments.print')
    <a href="{{ route('vehicle-assignments.print', $model) }}" class="btn btn-sm btn-secondary" target="_blank"
        title="Print">
        <i class="fas fa-print"></i>
    </a>
    @endcan
    @if ($model->status === \App\Models\VehicleAssignment::STATUS_DRAFT)
        @can('vehicle-assignments.edit')
        <a href="{{ route('vehicle-assignments.edit', $model) }}" class="btn btn-sm btn-warning" title="Edit">
            <i class="fas fa-edit"></i>
        </a>
        @endcan
    @endif
</div>
