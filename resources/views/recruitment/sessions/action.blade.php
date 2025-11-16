@php
    $isFptk = isset($fptk) && $fptk !== null;
    $isMpp = isset($mpp) && $mpp !== null;
@endphp

@if($isFptk)
    <!-- View Button for FPTK -->
    @can('recruitment-sessions.show')
        <a href="{{ route('recruitment.sessions.show', $fptk->id) }}" class="btn btn-sm btn-info" title="View FPTK Details">
            <i class="fas fa-eye"></i>
        </a>
    @endcan

    <!-- Add Candidate Button for FPTK -->
    @can('recruitment-sessions.create')
        <button type="button" class="btn btn-sm btn-primary add-candidate-btn" data-fptk-id="{{ $fptk->id }}"
            data-fptk-number="{{ $fptk->request_number }}" data-position="{{ $fptk->position->position_name ?? 'N/A' }}"
            title="Add Candidate to FPTK">
            <i class="fas fa-plus"></i>
        </button>
    @endcan
@elseif($isMpp && isset($mpp_detail))
    <!-- View Button for MPP Detail (using same view as FPTK) -->
    @can('recruitment-sessions.show')
        <a href="{{ route('recruitment.sessions.show', $mpp_detail->id) }}" class="btn btn-sm btn-info" title="View MPP Detail Sessions">
            <i class="fas fa-eye"></i>
        </a>
    @endcan

    <!-- Add Candidate Button for MPP Detail -->
    @can('recruitment-sessions.create')
        @if($mpp->status === 'active' && !$mpp_detail->fulfilled_at)
            <button type="button" class="btn btn-sm btn-success add-candidate-mpp-btn" 
                data-mpp-detail-id="{{ $mpp_detail->id }}"
                data-mpp-number="{{ $mpp->mpp_number }}" 
                data-position-name="{{ $mpp_detail->position->position_name ?? 'N/A' }}"
                title="Add Candidate to MPP Detail">
                <i class="fas fa-plus"></i>
            </button>
        @endif
    @endcan
@endif
