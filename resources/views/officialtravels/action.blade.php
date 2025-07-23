<!-- View button -->
<a href="{{ route('officialtravels.show', $model->id) }}" class="btn btn-icon btn-info btn-sm">
    <i class="fas fa-eye"></i>
</a>

{{-- <!-- Edit button - only for draft status -->
@if ($model->official_travel_status == 'draft')
    @can('officialtravel.edit')
        <a href="{{ route('officialtravels.edit', $model->id) }}" class="btn btn-icon btn-primary btn-sm">
            <i class="fas fa-pen-square"></i>
        </a>
    @endcan
@endif

<!-- Delete button - only for draft status -->
@if ($model->official_travel_status == 'draft')
    @can('officialtravel.delete')
        <form action="{{ route('officialtravels.destroy', $model->id) }}" method="post"
            onsubmit="return confirm('Are you sure you want to delete this official travel?')" class="d-inline">
            @method('delete')
            @csrf
            <button class="btn btn-icon btn-danger btn-sm">
                <i class="fas fa-trash"></i>
            </button>
        </form>
    @endcan
@endif

<!-- Recommend button - for draft status and pending recommendation -->
@if ($model->official_travel_status == 'draft' && $model->recommendation_status == 'pending')
    @can('official-travels.recommend')
        @if (Auth::id() == $model->recommendation_by)
            <a href="{{ route('officialtravels.showRecommendForm', $model->id) }}" class="btn btn-icon btn-warning btn-sm">
                <i class="fas fa-thumbs-up"></i>
            </a>
        @endif
    @endcan
@endif

<!-- Approve button - for recommended status and pending approval -->
@if ($model->recommendation_status == 'approved' && $model->approval_status == 'pending')
    @can('official-travels.approve')
        @if (Auth::id() == $model->approval_by)
            <a href="{{ route('officialtravels.showApprovalForm', $model->id) }}" class="btn btn-icon btn-success btn-sm">
                <i class="fas fa-check-circle"></i>
            </a>
        @endif
    @endcan
@endif



<!-- Arrival Stamp button - for open status without arrival -->
@if ($model->official_travel_status == 'open' && !$model->arrival_check_by)
    @can('officialtravel.stamp')
        <a href="{{ route('officialtravels.showArrivalForm', $model->id) }}" class="btn btn-icon btn-secondary btn-sm">
            <i class="fas fa-plane-arrival"></i>
        </a>
    @endcan
@endif

<!-- Departure Stamp button - for open status with arrival but without departure -->
@if ($model->official_travel_status == 'open' && $model->arrival_check_by && !$model->departure_check_by)
    @can('officialtravel.stamp')
        <a href="{{ route('officialtravels.showDepartureForm', $model->id) }}" class="btn btn-icon btn-dark btn-sm">
            <i class="fas fa-plane-departure"></i>
        </a>
    @endcan
@endif --}}
