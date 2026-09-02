<?php

namespace App\Http\Controllers;

use App\Models\SupplyItem;
use App\Models\SupplyItemCategory;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Models\SupplyStockInItem;
use App\Models\SupplyStockOutItem;
use App\Services\SupplyStock;
use App\Support\UserProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class SupplyReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.reports.show')->only([
            'index',
            'stockCard', 'stockCardData', 'exportStockCard',
            'stockMovement', 'stockMovementData', 'exportStockMovement',
            'orderMonitoring', 'orderMonitoringData', 'exportOrderMonitoring',
            'orderFulfillment', 'orderFulfillmentData', 'exportOrderFulfillment',
        ]);
    }

    public function index()
    {
        return view('supplies-reports.index', [
            'title' => 'Supplies Reports',
            'subtitle' => 'Stock and supply order analytics',
        ]);
    }

    // ─── Stock Card ─────────────────────────────────────────────────────────

    public function stockCard(Request $request)
    {
        return view('supplies-reports.stock-card', [
            'title' => 'Stock Card Report',
            'projects' => UserProject::projectsForSelect(),
            'categories' => SupplyItemCategory::query()->orderBy('name')->get(),
            'filters' => $request->only(['project_id', 'category_id', 'status', 'q']),
        ]);
    }

    public function stockCardData(Request $request)
    {
        if (! $this->stockCardHasFilters($request)) {
            return $this->emptyDataTables($request);
        }

        $projectId = (int) $request->project_id;
        $query = $this->stockCardQuery($request);
        $filteredRecords = (clone $query)->count();

        $start = (int) $request->input('start', 0);
        $length = min(max((int) $request->input('length', 10), 1), 500);

        $rows = $query->skip($start)->take($length)->get();
        $data = [];

        foreach ($rows as $i => $item) {
            $totals = SupplyStock::totals($item->id, $projectId);
            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'code' => '<code>'.e($item->code).'</code>',
                'name' => display_text($item->name),
                'category_label' => display_text($item->categoryLabel()),
                'stock_unit' => display_text($item->stock_unit),
                'stock_in' => $totals['in'],
                'stock_out' => $totals['out'],
                'ending_balance' => $totals['ending'],
                'status_badge' => $this->itemStatusBadge($item->status),
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function exportStockCard(Request $request)
    {
        if (! $this->stockCardHasFilters($request)) {
            return redirect()->route('supplies.reports.stock-card')
                ->with('toast_error', 'Please select a project before exporting.');
        }

        $projectId = (int) $request->project_id;
        $rows = $this->stockCardQuery($request)->limit(5000)->get();

        $exportData = $rows->map(function ($item, $idx) use ($projectId) {
            $totals = SupplyStock::totals($item->id, $projectId);

            return [
                'No' => $idx + 1,
                'Item code' => $item->code,
                'Name' => $item->name,
                'Category' => $item->category->name ?? '—',
                'Stock unit' => $item->stock_unit,
                'Stock In' => $totals['in'],
                'Stock Out' => $totals['out'],
                'Ending balance' => $totals['ending'],
                'Status' => $item->status,
            ];
        });

        return $this->excelDownload($exportData, [
            'No', 'Item code', 'Name', 'Category', 'Stock unit',
            'Stock In', 'Stock Out', 'Ending balance', 'Status',
        ], 'supply_stock_card_report.xlsx');
    }

    // ─── Stock Movement ─────────────────────────────────────────────────────

    public function stockMovement(Request $request)
    {
        return view('supplies-reports.stock-movement', [
            'title' => 'Stock Movement Report',
            'projects' => UserProject::projectsForSelect(),
            'filters' => $request->only(['project_id', 'doc_type', 'date_from', 'date_to', 'q']),
        ]);
    }

    public function stockMovementData(Request $request)
    {
        if (! $this->movementHasFilters($request)) {
            return $this->emptyDataTables($request);
        }

        $allRows = $this->movementRows($request);
        $filteredRecords = $allRows->count();

        $start = (int) $request->input('start', 0);
        $length = min(max((int) $request->input('length', 10), 1), 500);

        $page = $allRows->slice($start, $length)->values();
        $data = [];

        foreach ($page as $i => $row) {
            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'stock_date_fmt' => $row['stock_date_fmt'],
                'doc_type_badge' => $row['doc_type_badge'],
                'document_number' => e($row['document_number']),
                'project_code' => e($row['project_code']),
                'item_code' => '<code>'.e($row['item_code']).'</code>',
                'item_name' => display_text($row['item_name']),
                'quantity_fmt' => $row['quantity_fmt'],
                'reference' => $row['reference'],
                'extra' => $row['extra'],
                'actions' => $row['actions'],
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function exportStockMovement(Request $request)
    {
        if (! $this->movementHasFilters($request)) {
            return redirect()->route('supplies.reports.stock-movement')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $exportData = $this->movementRows($request)->take(5000)->values()->map(function ($row, $idx) {
            return [
                'No' => $idx + 1,
                'Date' => $row['stock_date_raw'],
                'Type' => $row['doc_type'],
                'Document No' => $row['document_number'],
                'Project' => $row['project_code'],
                'Item code' => $row['item_code'],
                'Item name' => $row['item_name'],
                'Quantity' => $row['quantity'],
                'Reference' => strip_tags($row['reference']),
                'Location / PIC' => strip_tags($row['extra']),
            ];
        });

        return $this->excelDownload($exportData, [
            'No', 'Date', 'Type', 'Document No', 'Project', 'Item code',
            'Item name', 'Quantity', 'Reference', 'Location / PIC',
        ], 'supply_stock_movement_report.xlsx');
    }

    // ─── Order Monitoring ───────────────────────────────────────────────────

    public function orderMonitoring(Request $request)
    {
        return view('supplies-reports.order-monitoring', [
            'title' => 'Supply Order Monitoring',
            'projects' => UserProject::projectsForSelect(),
            'filters' => $request->only([
                'status', 'project_id', 'date_from', 'date_to', 'order_number', 'requester_q',
            ]),
        ]);
    }

    public function orderMonitoringData(Request $request)
    {
        if (! $this->orderMonitoringHasFilters($request)) {
            return $this->emptyDataTables($request);
        }

        $query = $this->orderMonitoringQuery($request);
        $filteredRecords = (clone $query)->count();

        $start = (int) $request->input('start', 0);
        $length = min(max((int) $request->input('length', 10), 1), 500);

        $rows = $query->skip($start)->take($length)->get();
        $data = [];

        foreach ($rows as $i => $row) {
            $lineCount = $row->items->count();
            $totalOrdered = $row->items->sum('quantity_ordered');
            $totalReceived = $row->items->sum(fn ($line) => $line->quantityReceived());

            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'order_number' => e($row->order_number),
                'project_name' => display_text($row->project->project_name ?? null),
                'order_date_fmt' => $row->order_date?->format('d/m/Y') ?? '—',
                'status_badge' => $this->orderStatusBadge($row->status),
                'department' => display_text($row->department->department_name ?? null),
                'requester' => display_text($row->requestedBy->name ?? null),
                'lines_summary' => $lineCount.' line(s) · '.$totalOrdered.' ordered · '.$totalReceived.' received',
                'actions' => '<a href="'.route('supplies.orders.show', $row).'" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function exportOrderMonitoring(Request $request)
    {
        if (! $this->orderMonitoringHasFilters($request)) {
            return redirect()->route('supplies.reports.order-monitoring')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $rows = $this->orderMonitoringQuery($request)->limit(5000)->get();

        $exportData = $rows->values()->map(function ($row, $idx) {
            $totalOrdered = $row->items->sum('quantity_ordered');
            $totalReceived = $row->items->sum(fn ($line) => $line->quantityReceived());

            return [
                'No' => $idx + 1,
                'Order No' => $row->order_number,
                'Project' => $row->project->project_name ?? '—',
                'Order date' => $row->order_date?->format('Y-m-d') ?? '—',
                'Status' => $row->status,
                'Department' => $row->department->department_name ?? '—',
                'Requester' => $row->requestedBy->name ?? '—',
                'Lines' => $row->items->count(),
                'Qty ordered' => $totalOrdered,
                'Qty received' => $totalReceived,
            ];
        });

        return $this->excelDownload($exportData, [
            'No', 'Order No', 'Project', 'Order date', 'Status', 'Department',
            'Requester', 'Lines', 'Qty ordered', 'Qty received',
        ], 'supply_order_monitoring_report.xlsx');
    }

    // ─── Order Fulfillment ──────────────────────────────────────────────────

    public function orderFulfillment(Request $request)
    {
        return view('supplies-reports.order-fulfillment', [
            'title' => 'Order Fulfillment Gap',
            'projects' => UserProject::projectsForSelect(),
            'filters' => $request->only(['project_id', 'order_number', 'q']),
        ]);
    }

    public function orderFulfillmentData(Request $request)
    {
        if (! $this->fulfillmentHasFilters($request)) {
            return $this->emptyDataTables($request);
        }

        $allRows = $this->fulfillmentRows($request);
        $filteredRecords = $allRows->count();

        $start = (int) $request->input('start', 0);
        $length = min(max((int) $request->input('length', 10), 1), 500);

        $page = $allRows->slice($start, $length)->values();
        $data = [];

        foreach ($page as $i => $row) {
            $data[] = [
                'DT_RowIndex' => $start + $i + 1,
                'order_number' => '<a href="'.route('supplies.orders.show', $row['order_id']).'">'.e($row['order_number']).'</a>',
                'project_code' => e($row['project_code']),
                'item_code' => '<code>'.e($row['item_code']).'</code>',
                'item_name' => display_text($row['item_name']),
                'quantity_ordered' => $row['quantity_ordered'],
                'quantity_received' => $row['quantity_received'],
                'quantity_outstanding' => '<span class="badge badge-warning">'.e((string) $row['quantity_outstanding']).'</span>',
                'actions' => auth()->user()?->can('supplies.stock-in.create')
                    ? '<a href="'.route('supplies.stock-ins.create', ['supply_order_id' => $row['order_id']]).'" class="btn btn-sm btn-success" title="Record Stock In"><i class="fas fa-dolly"></i></a>'
                    : '—',
            ];
        }

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $filteredRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    public function exportOrderFulfillment(Request $request)
    {
        if (! $this->fulfillmentHasFilters($request)) {
            return redirect()->route('supplies.reports.order-fulfillment')
                ->with('toast_error', 'Please apply at least one filter before exporting.');
        }

        $exportData = $this->fulfillmentRows($request)->take(5000)->values()->map(function ($row, $idx) {
            return [
                'No' => $idx + 1,
                'Order No' => $row['order_number'],
                'Project' => $row['project_code'],
                'Item code' => $row['item_code'],
                'Item name' => $row['item_name'],
                'Qty ordered' => $row['quantity_ordered'],
                'Qty received' => $row['quantity_received'],
                'Outstanding' => $row['quantity_outstanding'],
            ];
        });

        return $this->excelDownload($exportData, [
            'No', 'Order No', 'Project', 'Item code', 'Item name',
            'Qty ordered', 'Qty received', 'Outstanding',
        ], 'supply_order_fulfillment_report.xlsx');
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    private function emptyDataTables(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
        ]);
    }

    private function stockCardHasFilters(Request $request): bool
    {
        return $request->filled('project_id') && (string) $request->project_id !== 'all';
    }

    private function stockCardQuery(Request $request): Builder
    {
        $query = SupplyItem::query()
            ->with('category')
            ->orderBy('code');

        if ($request->filled('category_id') && (string) $request->category_id !== 'all') {
            $query->where('supply_item_category_id', $request->category_id);
        }
        if ($request->filled('status') && (string) $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $term = '%'.addcslashes(trim((string) $request->q), '%_\\').'%';
            $query->where(function ($w) use ($term) {
                $w->where('code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        return $query;
    }

    private function movementHasFilters(Request $request): bool
    {
        return $request->filled('project_id')
            || $request->filled('doc_type')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('q');
    }

    private function movementRows(Request $request): Collection
    {
        $rows = collect();
        $docType = $request->input('doc_type', '');

        if ($docType === '' || $docType === 'all' || $docType === 'SI') {
            $siQuery = SupplyStockInItem::query()
                ->with(['item', 'stockIn.project', 'stockIn.order'])
                ->whereHas('stockIn', function (Builder $q) use ($request) {
                    UserProject::scopeToAssignedProjects($q, 'project_id');
                    if ($request->filled('project_id') && (string) $request->project_id !== 'all') {
                        $q->where('project_id', $request->project_id);
                    }
                    if ($request->filled('date_from')) {
                        $q->whereDate('stock_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $q->whereDate('stock_date', '<=', $request->date_to);
                    }
                });

            if ($request->filled('q')) {
                $term = '%'.addcslashes(trim((string) $request->q), '%_\\').'%';
                $siQuery->where(function ($w) use ($term) {
                    $w->whereHas('item', fn (Builder $q) => $q->where('code', 'like', $term)->orWhere('name', 'like', $term))
                        ->orWhereHas('stockIn', fn (Builder $q) => $q->where('document_number', 'like', $term));
                });
            }

            foreach ($siQuery->get() as $line) {
                $header = $line->stockIn;
                $rows->push([
                    'sort_key' => ($header->stock_date?->format('Y-m-d') ?? '').$header->document_number,
                    'stock_date_fmt' => $header->stock_date?->format('d/m/Y') ?? '—',
                    'stock_date_raw' => $header->stock_date?->format('Y-m-d') ?? '',
                    'doc_type' => 'Stock In',
                    'doc_type_badge' => '<span class="badge badge-success">Stock In</span>',
                    'document_number' => $header->document_number,
                    'project_code' => $header->project->project_code ?? '—',
                    'item_code' => $line->item->code ?? '—',
                    'item_name' => $line->item->name ?? '—',
                    'quantity' => (int) $line->quantity,
                    'quantity_fmt' => '<span class="text-success font-weight-bold">+'.e((string) $line->quantity).'</span>',
                    'reference' => $header->order
                        ? '<a href="'.route('supplies.orders.show', $header->order).'">'.e($header->order->order_number).'</a>'
                        : '—',
                    'extra' => display_text($line->remarks ?? null, '—'),
                    'actions' => '<a href="'.route('supplies.stock-ins.show', $header).'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>',
                ]);
            }
        }

        if ($docType === '' || $docType === 'all' || $docType === 'SO') {
            $soQuery = SupplyStockOutItem::query()
                ->with(['item', 'stockOut.project'])
                ->whereHas('stockOut', function (Builder $q) use ($request) {
                    UserProject::scopeToAssignedProjects($q, 'project_id');
                    if ($request->filled('project_id') && (string) $request->project_id !== 'all') {
                        $q->where('project_id', $request->project_id);
                    }
                    if ($request->filled('date_from')) {
                        $q->whereDate('stock_date', '>=', $request->date_from);
                    }
                    if ($request->filled('date_to')) {
                        $q->whereDate('stock_date', '<=', $request->date_to);
                    }
                });

            if ($request->filled('q')) {
                $term = '%'.addcslashes(trim((string) $request->q), '%_\\').'%';
                $soQuery->where(function ($w) use ($term) {
                    $w->whereHas('item', fn (Builder $q) => $q->where('code', 'like', $term)->orWhere('name', 'like', $term))
                        ->orWhereHas('stockOut', fn (Builder $q) => $q->where('document_number', 'like', $term));
                });
            }

            foreach ($soQuery->get() as $line) {
                $header = $line->stockOut;
                $rows->push([
                    'sort_key' => ($header->stock_date?->format('Y-m-d') ?? '').$header->document_number,
                    'stock_date_fmt' => $header->stock_date?->format('d/m/Y') ?? '—',
                    'stock_date_raw' => $header->stock_date?->format('Y-m-d') ?? '',
                    'doc_type' => 'Stock Out',
                    'doc_type_badge' => '<span class="badge badge-danger">Stock Out</span>',
                    'document_number' => $header->document_number,
                    'project_code' => $header->project->project_code ?? '—',
                    'item_code' => $line->item->code ?? '—',
                    'item_name' => $line->item->name ?? '—',
                    'quantity' => (int) $line->quantity,
                    'quantity_fmt' => '<span class="text-danger font-weight-bold">-'.e((string) $line->quantity).'</span>',
                    'reference' => '—',
                    'extra' => display_text(trim(($line->location ?? '').($line->person_in_charge ? ' / '.$line->person_in_charge : '')), '—'),
                    'actions' => '<a href="'.route('supplies.stock-outs.show', $header).'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>',
                ]);
            }
        }

        return $rows->sortByDesc('sort_key')->values();
    }

    private function orderMonitoringHasFilters(Request $request): bool
    {
        return $request->filled('status')
            || $request->filled('project_id')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('order_number')
            || $request->filled('requester_q');
    }

    private function orderMonitoringQuery(Request $request): Builder
    {
        $query = SupplyOrder::query()
            ->with(['project', 'department', 'requestedBy', 'items'])
            ->orderByDesc('supply_orders.created_at');

        UserProject::scopeToAssignedProjects($query, 'project_id');

        if ($request->filled('status') && (string) $request->status !== 'all') {
            $query->where('supply_orders.status', $request->status);
        }
        if ($request->filled('project_id') && (string) $request->project_id !== 'all') {
            $query->where('supply_orders.project_id', $request->project_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('supply_orders.order_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('supply_orders.order_date', '<=', $request->date_to);
        }
        if ($request->filled('order_number')) {
            $term = '%'.addcslashes(trim((string) $request->order_number), '%_\\').'%';
            $query->where('supply_orders.order_number', 'like', $term);
        }
        if ($request->filled('requester_q')) {
            $term = '%'.addcslashes(trim((string) $request->requester_q), '%_\\').'%';
            $query->whereHas('requestedBy', fn (Builder $q) => $q->where('name', 'like', $term));
        }

        return $query;
    }

    private function fulfillmentHasFilters(Request $request): bool
    {
        return true;
    }

    private function fulfillmentRows(Request $request): Collection
    {
        $query = SupplyOrderItem::query()
            ->with(['order.project', 'item'])
            ->whereHas('order', function (Builder $q) use ($request) {
                $q->where('status', SupplyOrder::STATUS_APPROVED);
                UserProject::scopeToAssignedProjects($q, 'project_id');
                if ($request->filled('project_id') && (string) $request->project_id !== 'all') {
                    $q->where('project_id', $request->project_id);
                }
                if ($request->filled('order_number')) {
                    $term = '%'.addcslashes(trim((string) $request->order_number), '%_\\').'%';
                    $q->where('order_number', 'like', $term);
                }
            })
            ->whereRaw('quantity_ordered > (
                SELECT COALESCE(SUM(quantity), 0) FROM supply_stock_in_items
                WHERE supply_order_item_id = supply_order_items.id
            )');

        if ($request->filled('q')) {
            $term = '%'.addcslashes(trim((string) $request->q), '%_\\').'%';
            $query->whereHas('item', fn (Builder $q) => $q->where('code', 'like', $term)->orWhere('name', 'like', $term));
        }

        return $query->get()->map(function (SupplyOrderItem $line) {
            $received = $line->quantityReceived();
            $outstanding = $line->quantityOutstanding();

            return [
                'order_id' => $line->supply_order_id,
                'order_number' => $line->order->order_number ?? '—',
                'project_code' => $line->order->project->project_code ?? '—',
                'item_code' => $line->item->code ?? '—',
                'item_name' => $line->item->name ?? '—',
                'quantity_ordered' => $line->quantity_ordered,
                'quantity_received' => $received,
                'quantity_outstanding' => $outstanding,
            ];
        })->sortBy('order_number')->values();
    }

    private function orderStatusBadge(string $status): string
    {
        $map = [
            'draft' => 'secondary',
            'submitted' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'dark',
            'closed' => 'info',
        ];
        $class = $map[$status] ?? 'light';

        return '<span class="badge badge-'.$class.'">'.e(ucfirst($status)).'</span>';
    }

    private function itemStatusBadge(string $status): string
    {
        $class = $status === 'active' ? 'success' : 'secondary';

        return '<span class="badge badge-'.$class.'">'.e(ucfirst($status)).'</span>';
    }

    private function excelDownload(Collection $exportData, array $headings, string $filename)
    {
        return Excel::download(new class($exportData, $headings) implements FromCollection, WithHeadings
        {
            public function __construct(
                private readonly Collection $data,
                private readonly array $headings
            ) {}

            public function collection()
            {
                return $this->data;
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, $filename);
    }
}
