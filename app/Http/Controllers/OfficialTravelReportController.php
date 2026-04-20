<?php

namespace App\Http\Controllers;

use App\Models\Officialtravel;
use App\Support\UserProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class OfficialTravelReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:official-travels.show')->only([
            'index',
            'travelRequests',
            'travelRequestsData',
            'exportTravelRequests',
        ]);
    }

    /**
     * Daftar report official travel (referensi: flight.reports.index / overtime.reports.index).
     */
    public function index()
    {
        return view('official-travel-reports.index', [
            'title' => 'Official Travel Reports',
            'subtitle' => 'Official travel analytics & reports',
        ]);
    }

    /**
     * Report daftar LOT — DataTables setelah minimal satu filter (referensi: flight.reports.flight-management).
     */
    public function travelRequests(Request $request)
    {
        $title = 'Report Official Travel Requests';
        $projects = UserProject::projectsForSelect();
        $filters = $request->only([
            'status', 'project_id', 'date_from', 'date_to',
            'lot_number', 'destination', 'traveler_q', 'purpose_q',
        ]);

        return view('official-travel-reports.travel-requests', compact('title', 'projects', 'filters'));
    }

    public function travelRequestsData(Request $request)
    {
        if (! $this->reportHasActiveFilters($request)) {
            return response()->json([
                'draw' => (int) $request->input('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $query = $this->baseReportQuery();
        $this->applyReportFilters($query, $request);

        $filteredRecords = (clone $query)->count();

        $start = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $length = min(max($length, 1), 500);

        $rows = $query->skip($start)->take($length)->get();

        $data = [];
        foreach ($rows as $i => $row) {
            $traveler = $row->traveler;
            $emp = $traveler && $traveler->employee ? $traveler->employee : null;
            $nik = e($traveler->nik ?? '—');
            $name = e($emp->fullname ?? '—');
            $travelerCell = $traveler
                ? '<div class="text-nowrap small">'.$nik.'</div><div class="text-muted small">'.$name.'</div>'
                : '<span class="text-muted">—</span>';

            $purposeHtml = $this->purposeCellHtml($row->purpose);

            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'official_travel_number' => e($row->official_travel_number ?? '—'),
                'official_travel_date_fmt' => $row->official_travel_date?->format('d/m/Y') ?? '—',
                'traveler_html' => $travelerCell,
                'project_name' => e($row->project->project_name ?? '—'),
                'destination' => e($row->destination ?? '—'),
                'purpose_html' => $purposeHtml,
                'duration' => e($row->duration ?? '—'),
                'transportation' => e($row->transportation->transportation_name ?? '—'),
                'accommodation' => e($row->accommodation->accommodation_name ?? '—'),
                'status_badge' => $this->statusBadgeHtml($row),
                'letter_number' => e($row->letter_number ?? '—'),
                'created_at_fmt' => $row->created_at?->format('d/m/Y H:i') ?? '—',
                'actions' => '<a href="'.route('officialtravels.show', $row).'" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function exportTravelRequests(Request $request)
    {
        if (! $this->reportHasActiveFilters($request)) {
            return redirect()->route('officialtravels.reports.travel-requests')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $query = $this->baseReportQuery();
        $this->applyReportFilters($query, $request);

        $rows = $query->limit(5000)->get();

        $exportData = $rows->values()->map(function ($row, $idx) {
            $traveler = $row->traveler;
            $emp = $traveler && $traveler->employee ? $traveler->employee : null;

            return [
                'No' => $idx + 1,
                'LOT No.' => $row->official_travel_number ?? '—',
                'LOT Date' => $row->official_travel_date?->format('Y-m-d') ?? '—',
                'NIK' => $traveler->nik ?? '—',
                'Traveler' => $emp->fullname ?? '—',
                'Project' => $row->project->project_name ?? '—',
                'Destination' => $row->destination ?? '—',
                'Purpose' => $row->purpose ?? '—',
                'Duration' => $row->duration ?? '—',
                'Departure from' => $row->departure_from?->format('Y-m-d') ?? '—',
                'Transportation' => $row->transportation->transportation_name ?? '—',
                'Accommodation' => $row->accommodation->accommodation_name ?? '—',
                'Status' => $this->statusPlainLabel($row),
                'Letter No.' => $row->letter_number ?? '—',
                'Created at' => $row->created_at?->format('Y-m-d H:i:s') ?? '—',
            ];
        });

        return Excel::download(new class($exportData) implements FromCollection, WithHeadings
        {
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
                    'No', 'LOT No.', 'LOT Date', 'NIK', 'Traveler', 'Project', 'Destination', 'Purpose',
                    'Duration', 'Departure from', 'Transportation', 'Accommodation', 'Status', 'Letter No.', 'Created at',
                ];
            }
        }, 'official_travel_requests_report.xlsx');
    }

    private function baseReportQuery(): Builder
    {
        $query = Officialtravel::query()
            ->select('officialtravels.*')
            ->with(['traveler.employee', 'project', 'transportation', 'accommodation'])
            ->orderByDesc('officialtravels.created_at');

        UserProject::scopeToAssignedProjects($query, 'official_travel_origin');

        return $query;
    }

    private function reportHasActiveFilters(Request $request): bool
    {
        return $request->filled('status')
            || $request->filled('project_id')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('lot_number')
            || $request->filled('destination')
            || $request->filled('traveler_q')
            || $request->filled('purpose_q');
    }

    private function applyReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('status') && $request->status !== 'all') {
            if ($request->status === 'pending_hr') {
                $query->where('officialtravels.submitted_by_user', true)
                    ->whereNull('officialtravels.letter_number_id');
            } else {
                $query->where('officialtravels.status', $request->status);
            }
        }
        if ($request->filled('project_id') && $request->project_id !== 'all') {
            $query->where('officialtravels.official_travel_origin', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('officialtravels.official_travel_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('officialtravels.official_travel_date', '<=', $request->date_to);
        }
        if ($request->filled('lot_number')) {
            $term = '%'.addcslashes(trim((string) $request->lot_number), '%_\\').'%';
            $query->where('officialtravels.official_travel_number', 'like', $term);
        }
        if ($request->filled('destination')) {
            $term = '%'.addcslashes(trim((string) $request->destination), '%_\\').'%';
            $query->where('officialtravels.destination', 'like', $term);
        }
        if ($request->filled('traveler_q')) {
            $term = '%'.addcslashes(trim((string) $request->traveler_q), '%_\\').'%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('traveler', fn (Builder $q2) => $q2->where('nik', 'like', $term))
                    ->orWhereHas('traveler.employee', fn (Builder $q3) => $q3->where('fullname', 'like', $term));
            });
        }
        if ($request->filled('purpose_q')) {
            $term = '%'.addcslashes(trim((string) $request->purpose_q), '%_\\').'%';
            $query->where('officialtravels.purpose', 'like', $term);
        }
    }

    private function purposeCellHtml(?string $purpose): string
    {
        if ($purpose === null || $purpose === '') {
            return '<span class="text-muted">—</span>';
        }

        $escaped = e($purpose);

        return '<div class="text-left text-break small official-travel-report-purpose" title="'.$escaped.'">'.$escaped.'</div>';
    }

    private function statusBadgeHtml(Officialtravel $row): string
    {
        if ($row->submitted_by_user && empty($row->letter_number_id)) {
            return '<span class="badge badge-info">Menunggu Konfirmasi HR</span>';
        }

        switch ($row->status) {
            case Officialtravel::STATUS_DRAFT:
                return '<span class="badge badge-warning">Draft</span>';
            case Officialtravel::STATUS_SUBMITTED:
                return '<span class="badge badge-info">Submitted</span>';
            case Officialtravel::STATUS_APPROVED:
                return '<span class="badge badge-success">Approved</span>';
            case Officialtravel::STATUS_REJECTED:
                return '<span class="badge badge-danger">Rejected</span>';
            case Officialtravel::STATUS_CANCELLED:
                return '<span class="badge badge-dark">Cancelled</span>';
            case Officialtravel::STATUS_CLOSED:
                return '<span class="badge badge-secondary">Closed</span>';
            default:
                return '<span class="badge badge-light">'.e(ucfirst((string) $row->status)).'</span>';
        }
    }

    private function statusPlainLabel(Officialtravel $row): string
    {
        if ($row->submitted_by_user && empty($row->letter_number_id)) {
            return 'Menunggu Konfirmasi HR';
        }

        return Officialtravel::getStatusOptions()[$row->status] ?? ucfirst((string) $row->status);
    }
}
