<?php

namespace App\Exports;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VehicleExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected Builder $query;

    public function __construct(?Builder $query = null)
    {
        $this->query = $query ?? Vehicle::query()
            ->with(['documents' => function ($q) {
                $q->whereIn('document_type', ['stnk', 'pkb', 'kir'])
                    ->whereIn('status', ['active', 'expired', 'pending_renewal']);
            }])
            ->orderBy('kode');
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'kode',
            'license_plate',
            'pic',
            'lokasi',
            'keterangan',
            'status',
            'type',
            'ownership',
            'fuel_type',
            'transmission',
            'year',
            'odometer',
            'brand',
            'model',
            'project_code',
            'stnk_expiry',
            'stnk_days_remaining',
            'pkb_expiry',
            'pkb_days_remaining',
            'kir_expiry',
            'kir_days_remaining',
        ];
    }

    public function map($vehicle): array
    {
        /** @var Vehicle $vehicle */
        return [
            $vehicle->kode,
            $vehicle->license_plate,
            $vehicle->pic,
            $vehicle->lokasi,
            $vehicle->keterangan,
            $vehicle->status,
            $vehicle->type,
            $vehicle->ownership,
            $vehicle->fuel_type,
            $vehicle->transmission,
            $vehicle->year,
            $vehicle->odometer,
            $vehicle->brand,
            $vehicle->model,
            $vehicle->project_code,
            optional($vehicle->documentExpiry('stnk'))->format('Y-m-d'),
            $vehicle->daysRemainingFor('stnk'),
            optional($vehicle->documentExpiry('pkb'))->format('Y-m-d'),
            $vehicle->daysRemainingFor('pkb'),
            optional($vehicle->documentExpiry('kir'))->format('Y-m-d'),
            $vehicle->daysRemainingFor('kir'),
        ];
    }
}
