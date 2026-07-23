<?php

namespace App\Http\Controllers;

use App\Models\RoomConsumptionRequest;
use App\Support\UserProject;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Facades\Excel;

class RoomConsumptionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:room-consumption-requests.show')->only([
            'index',
            'requestMonitoring',
            'requestMonitoringData',
            'exportRequestMonitoring',
        ]);
    }

    public function index()
    {
        return view('room-consumption-reports.index', [
            'title' => 'Room & Consumption Reports',
            'subtitle' => 'RCR analytics & reports',
        ]);
    }

    public function requestMonitoring(Request $request)
    {
        $title = 'Report Room & Consumption Requests';
        $projects = UserProject::projectsForSelect();
        $filters = $request->only([
            'status', 'project_id', 'date_from', 'date_to',
            'request_number', 'requester_q', 'room_q', 'title_q',
        ]);

        return view('room-consumption-reports.request-monitoring', compact('title', 'projects', 'filters'));
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
            $s = $row->start_time ? Carbon::parse($row->start_time)->format('H:i') : '';
            $e = $row->end_time ? Carbon::parse($row->end_time)->format('H:i') : '';
            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'request_number' => e($row->request_number ?? '—'),
                'project_label' => e(($row->project->project_code ?? '').' - '.($row->project->project_name ?? '')),
                'room_name' => e($row->meetingRoom->room_name ?? '—'),
                'meeting_title' => e($row->meeting_title ?? '—'),
                'meeting_date_fmt' => $row->meeting_date?->format('d/m/Y') ?? '—',
                'created_at_fmt' => $row->created_at?->format('d/m/Y') ?? '—',
                'target_days' => e($this->formatTargetDays($this->targetDays($row))),
                'time_range' => e(trim("{$s} - {$e}", ' -')),
                'status_badge' => $this->statusBadgeHtml($row->status),
                'requester' => e($row->requestedBy->name ?? '—'),
                'actions' => '<a href="'.route('room-consumption-requests.show', $row).'" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>',
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
            return redirect()->route('room-consumption-requests.reports.request-monitoring')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $query = $this->baseReportQuery();
        $this->applyReportFilters($query, $request);
        $rows = $query->limit(5000)->get();

        $exportData = $rows->values()->map(function ($row, $idx) {
            $s = $row->start_time ? Carbon::parse($row->start_time)->format('H:i') : '';
            $e = $row->end_time ? Carbon::parse($row->end_time)->format('H:i') : '';
            $targetDays = $this->targetDays($row);

            return [
                'No' => $idx + 1,
                'Reg. No' => $row->request_number ?? '—',
                'Project' => ($row->project->project_code ?? '').' - '.($row->project->project_name ?? ''),
                'Room' => $row->meetingRoom->room_name ?? '—',
                'Meeting Title' => $row->meeting_title ?? '—',
                'Meeting Date' => $row->meeting_date?->format('Y-m-d') ?? '—',
                'Created At' => $row->created_at?->format('Y-m-d') ?? '—',
                'Target (days)' => $targetDays === null ? '—' : $targetDays,
                'Time' => trim("{$s} - {$e}", ' -'),
                'Status' => $row->status,
                'Requester' => $row->requestedBy->name ?? '—',
                'Need Zoom' => $row->need_zoom ? 'Yes' : 'No',
            ];
        });

        return Excel::download(new class($exportData) implements FromCollection, WithHeadings, WithStrictNullComparison
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
                    'No', 'Reg. No', 'Project', 'Room', 'Meeting Title', 'Meeting Date',
                    'Created At', 'Target (days)', 'Time', 'Status', 'Requester', 'Need Zoom',
                ];
            }
        }, 'room_consumption_requests_report.xlsx');
    }

    private function baseReportQuery(): Builder
    {
        $query = RoomConsumptionRequest::query()
            ->select('room_consumption_requests.*')
            ->with(['project', 'meetingRoom', 'requestedBy'])
            ->orderByDesc('room_consumption_requests.created_at');

        UserProject::scopeToAssignedProjects($query, 'project_id');

        return $query;
    }

    private function reportHasActiveFilters(Request $request): bool
    {
        return $request->filled('status')
            || $request->filled('project_id')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('request_number')
            || $request->filled('requester_q')
            || $request->filled('room_q')
            || $request->filled('title_q');
    }

    private function applyReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('project_id') && $request->project_id !== 'all') {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('meeting_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('meeting_date', '<=', $request->date_to);
        }
        if ($request->filled('request_number')) {
            $query->where('request_number', 'like', '%'.$request->request_number.'%');
        }
        if ($request->filled('requester_q')) {
            $q = $request->requester_q;
            $query->whereHas('requestedBy', fn ($w) => $w->where('name', 'like', "%{$q}%"));
        }
        if ($request->filled('room_q')) {
            $q = $request->room_q;
            $query->whereHas('meetingRoom', fn ($w) => $w->where('room_name', 'like', "%{$q}%"));
        }
        if ($request->filled('title_q')) {
            $query->where('meeting_title', 'like', '%'.$request->title_q.'%');
        }
    }

    private function statusBadgeHtml(string $status): string
    {
        $map = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'dark',
            'completed' => 'primary',
        ];
        $class = $map[$status] ?? 'secondary';

        return '<span class="badge badge-'.$class.'">'.e(ucfirst($status)).'</span>';
    }

    /**
     * Days between request creation and meeting date (meeting_date − created_at, date only).
     */
    private function targetDays(RoomConsumptionRequest $row): ?int
    {
        if (! $row->created_at || ! $row->meeting_date) {
            return null;
        }

        return (int) $row->created_at->copy()->startOfDay()->diffInDays(
            $row->meeting_date->copy()->startOfDay(),
            false
        );
    }

    private function formatTargetDays(?int $days): string
    {
        if ($days === null) {
            return '—';
        }

        return $days.' hari';
    }
}
