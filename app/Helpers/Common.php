<?php
function showDateTime($carbon, $format = "d M Y @ H:i")
{
    if (!$carbon) {
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
