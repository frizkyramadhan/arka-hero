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
}
