@php
    $showRoute = $personal ? 'supplies.orders.my-orders.show' : 'supplies.orders.show';
    $editRoute = $personal ? 'supplies.orders.my-orders.edit' : 'supplies.orders.edit';
@endphp
<a href="{{ route($showRoute, $model) }}" class="btn btn-info btn-sm" title="View">
    <i class="fas fa-eye"></i>
</a>
@if ($model->canBeEditedBy(auth()->user()))
    <a href="{{ route($editRoute, $model) }}" class="btn btn-primary btn-sm" title="Edit">
        <i class="fas fa-pen"></i>
    </a>
@endif
