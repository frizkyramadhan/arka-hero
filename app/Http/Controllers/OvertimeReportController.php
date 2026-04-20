<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Support\UserProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class OvertimeReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:overtime-requests.show')->only([
            'index',
            'requestMonitoring',
            'requestMonitoringData',
            'exportRequestMonitoring',
        ]);
    }

    /**
     * Daftar report overtime (referensi: flight.reports.index).
     */
    public function index()
    {
        return view('overtime-reports.index', [
            'title' => 'Overtime Reports',
            'subtitle' => 'Overtime analytics & reports',
        ]);
    }

    /**
     * Report permintaan lembur — data via DataTables setelah filter (referensi: flight.reports.flight-management).
     */
    public function requestMonitoring(Request $request)
    {
        $title = 'Report Overtime Requests';
        $projects = UserProject::projectsForSelect();
        $filters = $request->only([
            'status', 'project_id', 'date_from', 'date_to',
            'register_number', 'requester_q', 'employee_q', 'remarks_q',
        ]);

        return view('overtime-reports.request-monitoring', compact('title', 'projects', 'filters'));
    }

    public function requestMonitoringData(Request $request)
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
            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'register_number' => e($row->register_number ?? '—'),
                'project_name' => e($row->project->project_name ?? '—'),
                'overtime_date_fmt' => $row->overtime_date?->format('d/m/Y') ?? '—',
                'status_badge' => $this->statusBadgeHtml($row->status),
                'requester' => e($row->requestedBy->name ?? '—'),
                'employees_html' => $this->employeesListHtml($row),
                'remarks_html' => $this->remarksCellHtml($row->remarks),
                'requested_at_fmt' => $row->requested_at?->format('d/m/Y H:i') ?? '—',
                'actions' => '<a href="'.route('overtime.requests.show', $row).'" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function exportRequestMonitoring(Request $request)
    {
        if (! $this->reportHasActiveFilters($request)) {
            return redirect()->route('overtime.reports.request-monitoring')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $query = $this->baseReportQuery();
        $this->applyReportFilters($query, $request);

        $rows = $query->limit(5000)->get();

        $exportData = $rows->values()->map(function ($row, $idx) {
            $employees = $row->details->map(function ($d) {
                $nik = $d->administration->nik ?? '—';
                $name = optional($d->administration->employee)->fullname ?? '—';

                return $nik.' — '.$name;
            })->implode('; ');

            return [
                'No' => $idx + 1,
                'Register No.' => $row->register_number ?? '—',
                'Project' => $row->project->project_name ?? '—',
                'Overtime Date' => $row->overtime_date?->format('Y-m-d') ?? '—',
                'Status' => $row->status,
                'Requester' => $row->requestedBy->name ?? '—',
                'Employees' => $employees !== '' ? $employees : '—',
                'Remarks' => $row->remarks ?? '—',
                'Requested At' => $row->requested_at?->format('Y-m-d H:i:s') ?? '—',
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
                    'No', 'Register No.', 'Project', 'Overtime Date', 'Status',
                    'Requester', 'Employees', 'Remarks', 'Requested At',
                ];
            }
        }, 'overtime_requests_report.xlsx');
    }

    private function baseReportQuery(): Builder
    {
        $query = OvertimeRequest::query()
            ->select('overtime_requests.*')
            ->with(['project', 'requestedBy', 'details.administration.employee'])
            ->orderByDesc('overtime_requests.created_at');

        UserProject::scopeToAssignedProjects($query, 'project_id');

        return $query;
    }

    private function reportHasActiveFilters(Request $request): bool
    {
        return $request->filled('status')
            || $request->filled('project_id')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('register_number')
            || $request->filled('requester_q')
            || $request->filled('employee_q')
            || $request->filled('remarks_q');
    }

    private function applyReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('overtime_requests.status', $request->status);
        }
        if ($request->filled('project_id') && (string) $request->project_id !== 'all') {
            $query->where('overtime_requests.project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('overtime_requests.overtime_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('overtime_requests.overtime_date', '<=', $request->date_to);
        }
        if ($request->filled('register_number')) {
            $term = '%'.addcslashes(trim((string) $request->register_number), '%_\\').'%';
            $query->where('overtime_requests.register_number', 'like', $term);
        }
        if ($request->filled('requester_q')) {
            $term = '%'.addcslashes(trim((string) $request->requester_q), '%_\\').'%';
            $query->whereHas('requestedBy', fn (Builder $q) => $q->where('name', 'like', $term));
        }
        if ($request->filled('employee_q')) {
            $term = '%'.addcslashes(trim((string) $request->employee_q), '%_\\').'%';
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('details.administration', fn (Builder $q2) => $q2->where('nik', 'like', $term))
                    ->orWhereHas('details.administration.employee', fn (Builder $q3) => $q3->where('fullname', 'like', $term));
            });
        }
        if ($request->filled('remarks_q')) {
            $term = '%'.addcslashes(trim((string) $request->remarks_q), '%_\\').'%';
            $query->where('overtime_requests.remarks', 'like', $term);
        }
    }

    private function employeesListHtml(OvertimeRequest $row): string
    {
        $lines = [];
        foreach ($row->details as $d) {
            $nik = e($d->administration->nik ?? '—');
            $name = e(optional($d->administration->employee)->fullname ?? '—');
            $lines[] = '<li class="mb-0">'.$nik.' — '.$name.'</li>';
        }

        if ($lines === []) {
            return '<span class="text-muted">—</span>';
        }

        return '<ul class="mb-0 pl-3 text-left overtime-report-employees">'
            .implode('', $lines).'</ul>';
    }

    private function remarksCellHtml(?string $remarks): string
    {
        if ($remarks === null || $remarks === '') {
            return '<span class="text-muted">—</span>';
        }

        return '<div class="text-left text-break overtime-report-remarks">'.nl2br(e($remarks)).'</div>';
    }

    private function statusBadgeHtml(string $status): string
    {
        $map = [
            OvertimeRequest::STATUS_DRAFT => 'secondary',
            OvertimeRequest::STATUS_PENDING => 'warning',
            OvertimeRequest::STATUS_APPROVED => 'success',
            OvertimeRequest::STATUS_REJECTED => 'danger',
            OvertimeRequest::STATUS_FINISHED => 'info',
        ];
        $c = $map[$status] ?? 'secondary';

        return '<span class="badge badge-'.$c.'">'.strtoupper(e($status)).'</span>';
    }
}
