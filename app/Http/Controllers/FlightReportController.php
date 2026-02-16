<?php

namespace App\Http\Controllers;

use App\Models\FlightRequest;
use App\Models\FlightRequestIssuanceDetail;
use App\Models\BusinessPartner;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FlightReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:flight-issuances.show')->only('index', 'flightManagement', 'flightManagementData', 'exportFlightManagement');
    }

    /**
     * Reports index - daftar report flight management (referensi: leave.reports.index)
     */
    public function index()
    {
        return view('flight-reports.index', [
            'title' => 'Flight Reports',
            'subtitle' => 'Flight Management Analytics & Reports',
        ]);
    }

    /**
     * Flight Management Report - halaman report, data dimuat via DataTables (server-side) setelah filter
     * Referensi: leave.reports.monitoring - data tidak dimuat di awal, hanya setelah filter
     */
    public function flightManagement(Request $request)
    {
        $title = 'Report Flight Management';
        $businessPartners = BusinessPartner::active()->get();
        $filters = $request->only(['issued_number', 'business_partner_id', 'date_from', 'date_to']);

        return view('flight-reports.flight-management', compact('title', 'businessPartners', 'filters'));
    }

    /**
     * Data untuk DataTables server-side Report Flight Management.
     * Mengembalikan kosong jika tidak ada filter (sama seperti leave report).
     */
    public function flightManagementData(Request $request)
    {
        $hasFilter = $request->filled('date_from') || $request->filled('date_to')
            || $request->filled('business_partner_id') || $request->filled('issued_number');

        if (!$hasFilter) {
            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $query = FlightRequestIssuanceDetail::query()
            ->with(['issuance.businessPartner', 'employee.activeAdministration.project'])
            ->join('flight_request_issuances', 'flight_request_issuance_details.flight_request_issuance_id', '=', 'flight_request_issuances.id')
            ->join('flight_request_issuance', 'flight_request_issuances.id', '=', 'flight_request_issuance.flight_request_issuance_id')
            ->join('flight_requests', 'flight_request_issuance.flight_request_id', '=', 'flight_requests.id')
            ->orderBy('flight_request_issuances.issued_number', 'asc')
            ->orderBy('flight_request_issuance_details.ticket_order', 'asc')
            ->orderBy('flight_requests.requested_at', 'asc')
            ->select('flight_request_issuance_details.*', 'flight_requests.id as flight_request_id');

        if ($request->filled('date_from')) {
            $query->where('flight_request_issuances.issued_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('flight_request_issuances.issued_date', '<=', $request->date_to);
        }
        if ($request->filled('business_partner_id') && $request->business_partner_id !== 'all') {
            $query->where('flight_request_issuances.business_partner_id', $request->business_partner_id);
        }
        if ($request->filled('issued_number')) {
            $query->where('flight_request_issuances.issued_number', 'like', '%' . $request->issued_number . '%');
        }

        $filteredRecords = $query->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $length = min(max($length, 1), 500);

        $details = $query->skip($start)->take($length)->get()->unique('id')->values();
        $frIds = $details->pluck('flight_request_id')->unique()->filter()->values()->all();
        $flightRequests = FlightRequest::with('details')->whereIn('id', $frIds)->get()->keyBy('id');
        $rows = $this->buildReportRows($details, $flightRequests, $start);

        $data = array_map(function ($row) {
            return [
                'DT_RowIndex' => $row['no'],
                'nama' => $row['nama'],
                'nik' => $row['nik'],
                'site' => $row['site'],
                'rute' => $row['rute'],
                'kode_booking' => $row['kode_booking'],
                'departure' => $row['departure'],
                'arrival' => $row['arrival'],
                'advance_display' => $row['advance_display'],
                'company_amount' => $row['company_amount'],
                'tanggal_fr_masuk' => $row['tanggal_fr_masuk'],
                'tanggal_issued' => $row['tanggal_issued'],
                'target' => $row['target'],
                'no_lg' => $row['no_lg'],
                'vendor' => $row['vendor'],
                'harga' => $row['harga'],
                'service_charge' => $row['service_charge'],
                'jumlah' => $row['jumlah'],
            ];
        }, $rows);

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Export Flight Management report to Excel (same filters as report; requires at least one filter).
     */
    public function exportFlightManagement(Request $request)
    {
        $hasFilter = $request->filled('date_from') || $request->filled('date_to')
            || $request->filled('business_partner_id') || $request->filled('issued_number');

        if (!$hasFilter) {
            return redirect()->route('flight.reports.flight-management')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $query = FlightRequestIssuanceDetail::query()
            ->with(['issuance.businessPartner', 'employee.activeAdministration.project'])
            ->join('flight_request_issuances', 'flight_request_issuance_details.flight_request_issuance_id', '=', 'flight_request_issuances.id')
            ->join('flight_request_issuance', 'flight_request_issuances.id', '=', 'flight_request_issuance.flight_request_issuance_id')
            ->join('flight_requests', 'flight_request_issuance.flight_request_id', '=', 'flight_requests.id')
            ->orderBy('flight_request_issuances.issued_number', 'asc')
            ->orderBy('flight_request_issuance_details.ticket_order', 'asc')
            ->orderBy('flight_requests.requested_at', 'asc')
            ->select('flight_request_issuance_details.*', 'flight_requests.id as flight_request_id');

        if ($request->filled('date_from')) {
            $query->where('flight_request_issuances.issued_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('flight_request_issuances.issued_date', '<=', $request->date_to);
        }
        if ($request->filled('business_partner_id') && $request->business_partner_id !== 'all') {
            $query->where('flight_request_issuances.business_partner_id', $request->business_partner_id);
        }
        if ($request->filled('issued_number')) {
            $query->where('flight_request_issuances.issued_number', 'like', '%' . $request->issued_number . '%');
        }

        $details = $query->get()->unique('id')->values();
        $frIds = $details->pluck('flight_request_id')->unique()->filter()->values()->all();
        $flightRequests = FlightRequest::with('details')->whereIn('id', $frIds)->get()->keyBy('id');
        $rows = $this->buildReportRows($details, $flightRequests, 0);

        $exportData = collect($rows)->map(function ($row) {
            return [
                'No' => $row['no'],
                'Name' => $row['nama'],
                'NIK' => $row['nik'],
                'Site' => $row['site'],
                'Route' => $row['rute'],
                'Booking Code' => $row['kode_booking'],
                'Departure' => $row['departure'],
                'Arrival' => $row['arrival'],
                '151 (Advance)' => $row['advance_display'] !== '-' ? 'Rp ' . $row['advance_display'] : '-',
                '622 (Company)' => $row['company_amount'] !== '-' ? 'Rp ' . $row['company_amount'] : '-',
                'FR Request Date' => $row['tanggal_fr_masuk'],
                'Issued Date' => $row['tanggal_issued'],
                'Target' => $row['target'],
                'No. LG' => $row['no_lg'],
                'Vendor' => $row['vendor'],
                'Price' => 'Rp ' . $row['harga'],
                'Service Charge' => $row['service_charge'] !== '-' ? 'Rp ' . $row['service_charge'] : '-',
                'Total' => 'Rp ' . $row['jumlah'],
            ];
        });

        return Excel::download(new class($exportData) implements FromCollection, WithHeadings {
            private $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return [
                    'No', 'Name', 'NIK', 'Site', 'Route', 'Booking Code', 'Departure', 'Arrival',
                    '151 (Advance)', '622 (Company)', 'FR Request Date', 'Issued Date', 'Target',
                    'No. LG', 'Vendor', 'Price', 'Service Charge', 'Total',
                ];
            }
        }, 'flight_management_report.xlsx');
    }

    private function buildReportRows($details, $flightRequests, int $startIndex = 0): array
    {
        $rows = [];
        $index = 0;
        foreach ($details as $detail) {
            $index++;
            $no = $startIndex + $index;
            $issuance = $detail->issuance;
            $fr = isset($detail->flight_request_id) ? ($flightRequests[$detail->flight_request_id] ?? null) : null;

            $depSegment = $fr ? $fr->details->where('segment_type', 'departure')->sortBy('segment_order')->first() : null;
            $retSegment = $fr ? $fr->details->where('segment_type', 'return')->sortBy('segment_order')->first() : null;

            $rute = '-';
            if ($depSegment) {
                $rute = trim($depSegment->departure_city . ' ' . $depSegment->arrival_city);
                if ($retSegment) {
                    $rute .= ' / ' . trim($retSegment->departure_city . ' ' . $retSegment->arrival_city);
                }
            }

            $depDate = $depSegment && $depSegment->flight_date ? $depSegment->flight_date->format('j-M-y') : '-';
            $arrDate = $retSegment && $retSegment->flight_date ? $retSegment->flight_date->format('j-M-y') : $depDate;

            $tanggalFrMasuk = $fr && $fr->requested_at ? $fr->requested_at->format('d/m/Y') : '-';
            $tanggalIssued = $issuance->issued_date ? $issuance->issued_date->format('d/m/Y') : '-';

            $target = '-';
            if ($issuance->issued_date && $fr && $fr->requested_at) {
                $target = (int) $fr->requested_at->startOfDay()->diffInDays($issuance->issued_date->startOfDay(), false);
            }

            $companyAmount = $detail->company_amount !== null && $detail->company_amount !== '' ? (float) $detail->company_amount : null;
            $advanceAmount = $detail->advance_amount !== null && $detail->advance_amount !== '' ? (float) $detail->advance_amount : null;
            $ticketPrice = $detail->ticket_price !== null ? (float) $detail->ticket_price : 0;
            $serviceCharge = $detail->service_charge !== null ? (float) $detail->service_charge : 0;
            $jumlah = $ticketPrice + $serviceCharge;

            $hasCompany = $companyAmount !== null && $companyAmount != 0;
            $companyFormatted = $hasCompany ? number_format($companyAmount, 0, ',', '.') : '-';
            $hasAdvance = $advanceAmount !== null && $advanceAmount != 0;
            $advanceFormatted = $hasAdvance ? number_format($advanceAmount, 0, ',', '.') : '-';

            $hargaFormatted = number_format($ticketPrice, 0, ',', '.');
            $serviceChargeFormatted = $serviceCharge > 0 ? number_format($serviceCharge, 0, ',', '.') : '-';
            $jumlahFormatted = number_format($jumlah, 0, ',', '.');

            // NIK dan Site dari employee (administration active); jika penumpang manual isi '-'
            $nik = '-';
            $site = '-';
            if ($detail->employee_id && $detail->employee) {
                $admin = $detail->employee->activeAdministration;
                $nik = $admin && $admin->nik !== null && $admin->nik !== '' ? $admin->nik : '-';
                $site = $admin && $admin->project
                    ? ($admin->project->project_code ?? '-')
                    : '-';
            }

            $rows[] = [
                'no' => $no,
                'nama' => $detail->resolved_passenger_name ?? '-',
                'nik' => $nik,
                'site' => $site,
                'rute' => $rute,
                'kode_booking' => $detail->booking_code ?? '-',
                'departure' => $depDate,
                'arrival' => $arrDate,
                'company_amount' => $companyFormatted,
                'advance_display' => $advanceFormatted,
                'tanggal_fr_masuk' => $tanggalFrMasuk,
                'tanggal_issued' => $tanggalIssued,
                'target' => $target,
                'no_lg' => $issuance->issued_number ?? '-',
                'vendor' => $issuance->businessPartner->bp_name ?? '-',
                'harga' => $hargaFormatted,
                'service_charge' => $serviceChargeFormatted,
                'jumlah' => $jumlahFormatted,
            ];
        }
        return $rows;
    }
}
