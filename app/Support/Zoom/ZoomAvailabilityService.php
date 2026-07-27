<?php

namespace App\Support\Zoom;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Zoom Meeting ID availability (same logic as IT WO Zoom_m::get_availability).
 * Used as HERO fallback when rest-server availability API is unavailable (404).
 */
class ZoomAvailabilityService
{
    public function __construct()
    {
        $helper = __DIR__.'/zoom_parser_helper.php';
        if (is_file($helper) && ! function_exists('zoom_build_availability')) {
            require_once $helper;
        }
    }

    /**
     * @return array{date: string, accounts: array<string, array<string, mixed>>}
     */
    public function getAvailability(string $date): array
    {
        $date = substr($date, 0, 10);
        $rows = $this->getRecapByDate($date);
        $availability = zoom_build_availability($rows, $date);

        $accounts = [];
        foreach (['131', '132', '134'] as $code) {
            $info = $availability[$code] ?? null;
            $bookings = [];
            if ($info && ! empty($info['bookings'])) {
                foreach ($info['bookings'] as $b) {
                    $bookings[] = [
                        'id_wo' => isset($b['id_wo']) ? (int) $b['id_wo'] : null,
                        'no_wo' => $b['no_wo'] ?? null,
                        'name' => $b['name'] ?? null,
                        'meeting_time' => $b['meeting_time'] ?? null,
                        'status' => $b['status'] ?? null,
                        'room_name' => $b['room_name'] ?? null,
                    ];
                }
            }

            $accounts[$code] = [
                'account' => $code,
                'room_names' => ($info && ! empty($info['room_names']))
                    ? $info['room_names']
                    : zoom_account_room_names($code),
                'status' => $info['status'] ?? 'available',
                'slots' => $info['slots'] ?? [],
                'bookings' => $bookings,
            ];
        }

        return [
            'date' => $date,
            'accounts' => $accounts,
        ];
    }

    public function isAvailable(): bool
    {
        try {
            DB::connection('it_wo')->getPdo();

            return true;
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * @return list<object>
     */
    private function getZoomWos(): array
    {
        $sql = "
            SELECT
                w.id_wo,
                w.no_wo,
                w.name,
                w.issue,
                w.date,
                w.status,
                a.detail AS activity_detail
            FROM wo w
            LEFT JOIN (
                SELECT a1.id_wo, a1.detail
                FROM activity a1
                INNER JOIN (
                    SELECT id_wo, MAX(id_act) AS max_act
                    FROM activity
                    WHERE detail LIKE '%Meeting ID%' OR detail LIKE '%MeetingID%'
                    GROUP BY id_wo
                ) latest ON latest.id_wo = a1.id_wo AND latest.max_act = a1.id_act
            ) a ON a.id_wo = w.id_wo
            WHERE w.id_kategori = ?
              AND w.status != 'Canceled'
            ORDER BY w.id_wo DESC
        ";

        return DB::connection('it_wo')->select($sql, [8]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getRecapByDate(string $date): array
    {
        $rows = [];

        foreach ($this->getZoomWos() as $wo) {
            $accounts = zoom_parse_activity_detail($wo->activity_detail ?? null);
            $sessions = zoom_parse_issue_sessions($wo->issue ?? null, $wo->date ?? null);
            $built = zoom_build_rows_for_wo($wo, $accounts, $sessions);
            foreach ($built as $row) {
                if (($row['meeting_date'] ?? null) === $date) {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }
}
