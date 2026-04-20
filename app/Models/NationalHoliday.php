<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class NationalHoliday extends Model
{
    protected $fillable = [
        'holiday_date',
        'name',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'holiday_date' => 'date',
    ];

    /**
     * Dates as Y-m-d strings for JS date pickers (daterangepicker isInvalidDate).
     *
     * @return list<string>
     */
    public static function datesForJs(): array
    {
        return static::query()
            ->orderBy('holiday_date')
            ->pluck('holiday_date')
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->values()
            ->all();
    }

    /**
     * Rows for leave-request forms: human-readable lines (date + optional name).
     *
     * @return list<array{date: string, line: string}>
     */
    public static function displayRowsForForms(): array
    {
        return static::query()
            ->orderBy('holiday_date')
            ->get(['holiday_date', 'name'])
            ->map(function ($row) {
                $d = $row->holiday_date;
                $line = $row->name
                    ? $d->format('d M Y').' — '.$row->name
                    : $d->format('d M Y');

                return [
                    'date' => $d->format('Y-m-d'),
                    'line' => $line,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<string> Human-readable labels for validation errors
     */
    public static function labelsOverlappingRange(string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        $rows = static::query()
            ->where('holiday_date', '>=', $start->toDateString())
            ->where('holiday_date', '<=', $end->toDateString())
            ->orderBy('holiday_date')
            ->get(['holiday_date', 'name']);

        $labels = [];
        foreach ($rows as $row) {
            $d = $row->holiday_date->format('d/m/Y');
            $labels[] = $row->name ? "{$row->name} ({$d})" : $d;
        }

        return $labels;
    }

    public static function isHolidayDate(string $ymd): bool
    {
        return static::query()->where('holiday_date', $ymd)->exists();
    }

    /**
     * Y-m-d => display name for JS tooltips on calendar cells.
     *
     * @return array<string, string>
     */
    public static function mapDateToNameForJs(): array
    {
        return static::query()
            ->orderBy('holiday_date')
            ->get(['holiday_date', 'name'])
            ->mapWithKeys(function ($row) {
                $d = $row->holiday_date;
                $ymd = $d instanceof Carbon ? $d->format('Y-m-d') : Carbon::parse($d)->format('Y-m-d');
                $label = $row->name && trim((string) $row->name) !== ''
                    ? trim((string) $row->name)
                    : 'National holiday';

                return [$ymd => $label];
            })
            ->all();
    }

    /**
     * Days that count as leave in [start, end]: for non-roster excludes national holidays and weekends;
     * for roster projects counts every calendar day in the range (national holidays included).
     */
    public static function countBillableLeaveDaysInRange(string $startYmd, string $endYmd, bool $isNonRoster): int
    {
        $start = Carbon::parse($startYmd)->startOfDay();
        $end = Carbon::parse($endYmd)->startOfDay();

        $holidaySet = [];
        if ($isNonRoster) {
            $holidayRows = static::query()
                ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
                ->pluck('holiday_date');

            foreach ($holidayRows as $h) {
                $holidaySet[Carbon::parse($h)->format('Y-m-d')] = true;
            }
        }

        $count = 0;
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $ymd = $d->format('Y-m-d');
            if ($isNonRoster && isset($holidaySet[$ymd])) {
                continue;
            }
            if ($isNonRoster && ($d->isSaturday() || $d->isSunday())) {
                continue;
            }
            $count++;
        }

        return $count;
    }
}
