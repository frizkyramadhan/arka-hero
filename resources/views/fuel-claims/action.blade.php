@can('fuel-claims.show')
<a href="{{ route('fuel-claims.show', $model) }}" class="btn btn-info btn-sm" title="Detail">
    <i class="fas fa-eye"></i>
</a>
<a href="{{ route('fuel-claims.print', $model) }}" class="btn btn-default btn-sm" title="Cetak nota" target="_blank">
    <i class="fas fa-print"></i>
</a>
@endcan
