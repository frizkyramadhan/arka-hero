<?php

/**
 * Format nominal Indonesia: ribuan (.) desimal (,)
 * Contoh: format_amount_id(1234567.5) -> "1.234.567,50"
 */
function format_amount_id($number, int $decimals = 2): string
{
    if ($number === null || $number === '') {
        return '';
    }
    $n = (float) $number;
    $formatted = number_format(abs($n), $decimals, ',', '.');

    return ($n < 0 ? '-' : '').$formatted;
}

function showDateTime($carbon, $format = 'd M Y @ H:i')
{
    if (! $carbon) {
        return '-';
    }

    return $carbon->translatedFormat($format);
}

/**
 * Format request reason for display
 */
function formatRequestReason($requestReason, $otherReason = null)
{
    if (empty($requestReason)) {
        return '-';
    }

    return match ($requestReason) {
        'replacement_resign' => 'Replacement - Resign, Termination, End of Contract',
        'replacement_promotion' => 'Replacement - Promotion, Mutation, Demotion',
        'additional_workplan' => 'Additional - Workplan',
        'other' => $otherReason ?: 'Other',
        // Legacy values (for backward compatibility)
        'replacement' => 'Replacement (Legacy)',
        'additional' => 'Additional (Legacy)',
        default => ucfirst(str_replace('_', ' ', $requestReason))
    };
}

/**
 * Active projects the current user may use (user_project pivot; administrators see all active).
 *
 * @return \Illuminate\Support\Collection<int, \App\Models\Project>
 */
function user_accessible_projects(bool $onlyActive = true): \Illuminate\Support\Collection
{
    return \App\Support\UserProject::projectsForSelect(null, $onlyActive);
}

/**
 * @return array<int>|null null if unrestricted (administrator)
 */
function user_project_assignment_scope(): ?array
{
    return \App\Support\UserProject::assignmentScope();
}
