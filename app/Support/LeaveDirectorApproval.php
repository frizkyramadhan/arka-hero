<?php

namespace App\Support;

class LeaveDirectorApproval
{
    public const ERROR = 'Approver cuti Director harus level Director dan bukan pemohon sendiri.';

    /**
     * At least one approver. Every approver must be another Director.
     *
     * @param  array<int, array{employee_id?: int|string|null, is_director?: bool}>  $approvers
     */
    public static function error(?string $applicantLevelName, int|string|null $applicantEmployeeId, array $approvers): ?string
    {
        if ($applicantLevelName !== 'Director') {
            return null;
        }

        if (count($approvers) < 1) {
            return self::ERROR;
        }

        foreach ($approvers as $approver) {
            $samePerson = (string) ($approver['employee_id'] ?? '') !== ''
                && (string) $approver['employee_id'] === (string) $applicantEmployeeId;

            if (empty($approver['is_director']) || $samePerson) {
                return self::ERROR;
            }
        }

        return null;
    }

    /**
     * First user id that is not the applicant. Director leave keeps a single peer step.
     *
     * @param  array<int, int|string>  $userIds
     */
    public static function firstPeerId(array $userIds, int|string|null $excludeUserId): ?int
    {
        foreach ($userIds as $userId) {
            if ($excludeUserId !== null && (int) $userId === (int) $excludeUserId) {
                continue;
            }

            return (int) $userId;
        }

        return null;
    }
}
