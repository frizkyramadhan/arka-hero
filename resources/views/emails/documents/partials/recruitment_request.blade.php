{{-- FPTK / Recruitment Request — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\RecruitmentRequest $recruitmentRequest */
    $recruitmentRequest->loadNotificationRelations();
    $qty = (int) ($recruitmentRequest->required_qty ?? 0);
    $projectLabel = $recruitmentRequest->project
        ? trim(($recruitmentRequest->project->project_code ?? '').' - '.($recruitmentRequest->project->project_name ?? ''))
        : '—';
    $ageLabel = null;
    if ($recruitmentRequest->required_age_min && $recruitmentRequest->required_age_max) {
        $ageLabel = $recruitmentRequest->required_age_min.' - '.$recruitmentRequest->required_age_max.' years';
    } elseif ($recruitmentRequest->required_age_min) {
        $ageLabel = 'Min '.$recruitmentRequest->required_age_min.' years';
    } elseif ($recruitmentRequest->required_age_max) {
        $ageLabel = 'Max '.$recruitmentRequest->required_age_max.' years';
    }
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">FPTK Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Request Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->request_number ?: $reference }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Letter Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->letter_number ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Department</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->department?->department_name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Project</td>
        <td style="{{ $emailUi['value'] }}">{{ $projectLabel !== '' ? $projectLabel : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Position</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->position?->position_name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Level</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->level?->name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Required Quantity</td>
        <td style="{{ $emailUi['value'] }}">{{ $qty }} {{ $qty > 1 ? 'persons' : 'person' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Required Date</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->required_date ? format_date_with_weekday($recruitmentRequest->required_date) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Employment Type</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->employment_type ? ucfirst(str_replace('_', ' ', $recruitmentRequest->employment_type)) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Request Reason</td>
        <td style="{{ $emailUi['value'] }}">{{ formatRequestReason($recruitmentRequest->request_reason, $recruitmentRequest->other_reason ?? null) }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Theory Test</td>
        <td style="{{ $emailUi['value'] }}">
            @if ($recruitmentRequest->requires_theory_test)
                Required <span style="{{ $emailUi['meta'] }}">(mekanik/teknis)</span>
            @else
                Not Required <span style="{{ $emailUi['meta'] }}">(non-teknis)</span>
            @endif
        </td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Created By</td>
        <td style="{{ $emailUi['valueLast'] }}">{{ $recruitmentRequest->createdBy?->name ?: '—' }}</td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Job Description &amp; Requirements</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Job Description</td>
        <td style="{{ $emailUi['value'] }} white-space: pre-wrap;">{{ $recruitmentRequest->job_description ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Gender</td>
        <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->required_gender ? ucfirst($recruitmentRequest->required_gender) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ ($ageLabel || filled($recruitmentRequest->required_education) || filled($recruitmentRequest->required_skills) || filled($recruitmentRequest->required_experience) || filled($recruitmentRequest->required_physical) || filled($recruitmentRequest->required_mental) || filled($recruitmentRequest->other_requirements)) ? $emailUi['label'] : $emailUi['labelLast'] }}">Marital Status</td>
        <td style="{{ ($ageLabel || filled($recruitmentRequest->required_education) || filled($recruitmentRequest->required_skills) || filled($recruitmentRequest->required_experience) || filled($recruitmentRequest->required_physical) || filled($recruitmentRequest->required_mental) || filled($recruitmentRequest->other_requirements)) ? $emailUi['value'] : $emailUi['valueLast'] }}">{{ $recruitmentRequest->required_marital_status ? ucfirst($recruitmentRequest->required_marital_status) : '—' }}</td>
    </tr>
    @if ($ageLabel)
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Age Range</td>
            <td style="{{ $emailUi['value'] }}">{{ $ageLabel }}</td>
        </tr>
    @endif
    @if (filled($recruitmentRequest->required_education))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Education</td>
            <td style="{{ $emailUi['value'] }}">{{ $recruitmentRequest->required_education }}</td>
        </tr>
    @endif
    @if (filled($recruitmentRequest->required_skills))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Required Skills</td>
            <td style="{{ $emailUi['value'] }} white-space: pre-wrap;">{{ $recruitmentRequest->required_skills }}</td>
        </tr>
    @endif
    @if (filled($recruitmentRequest->required_experience))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Required Experience</td>
            <td style="{{ $emailUi['value'] }} white-space: pre-wrap;">{{ $recruitmentRequest->required_experience }}</td>
        </tr>
    @endif
    @if (filled($recruitmentRequest->required_physical))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Physical Requirements</td>
            <td style="{{ $emailUi['value'] }} white-space: pre-wrap;">{{ $recruitmentRequest->required_physical }}</td>
        </tr>
    @endif
    @if (filled($recruitmentRequest->required_mental))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Mental Requirements</td>
            <td style="{{ $emailUi['value'] }} white-space: pre-wrap;">{{ $recruitmentRequest->required_mental }}</td>
        </tr>
    @endif
    @if (filled($recruitmentRequest->other_requirements))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Other Requirements</td>
            <td style="{{ $emailUi['valueLast'] }} white-space: pre-wrap;">{{ $recruitmentRequest->other_requirements }}</td>
        </tr>
    @endif
</table>

@if (! empty($approvalOrder) || ! empty($remarks))
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        @if (! empty($approvalOrder))
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ ! empty($remarks) ? $emailUi['label'] : $emailUi['labelLast'] }}">Approval Step</td>
                <td style="{{ ! empty($remarks) ? $emailUi['value'] : $emailUi['valueLast'] }}">#{{ $approvalOrder }}</td>
            </tr>
        @endif
        @if (! empty($remarks))
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Approval Remarks</td>
                <td style="{{ $emailUi['valueLast'] }}">{{ $remarks }}</td>
            </tr>
        @endif
    </table>
@endif
