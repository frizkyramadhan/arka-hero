<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SupplyItem;
use App\Models\SupplyOrder;
use App\Models\SupplyStockIn;
use App\Models\SupplyStockInItem;
use App\Services\SupplyStock;
use App\Support\UserProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyStockInController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.stock-in.show')->only(['index', 'data', 'show', 'print']);
        $this->middleware('permission:supplies.stock-in.create')->only(['create', 'store']);
        $this->middleware('permission:supplies.stock-in.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'Stock In';
        $subtitle = 'Stock receipts';
        $projects = UserProject::projectsForSelect();

        return view('supplies.stock-ins.index', compact('title', 'subtitle', 'projects'));
    }

    public function data(Request $request)
    {
        $query = SupplyStockIn::query()
            ->with(['project', 'order', 'createdBy'])
            ->withCount('items')
            ->orderByDesc('stock_date')
            ->orderByDesc('created_at');

        UserProject::scopeToAssignedProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('date1') && $request->filled('date2')) {
            $query->whereBetween('stock_date', [$request->date1, $request->date2]);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('stock_date', fn ($row) => $row->stock_date?->format('d/m/Y'))
            ->addColumn('project_label', fn ($row) => display_text(trim(($row->project->project_code ?? '').' - '.($row->project->project_name ?? ''), ' -')))
            ->addColumn('order_label', fn ($row) => display_text($row->order->order_number ?? null))
            ->addColumn('action', function ($model) {
                return view('supplies.stock-ins.action', compact('model'))->render();
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        $title = 'Record Stock In';
        $subtitle = 'Add a receipt';
        $projects = UserProject::projectsForSelect();
        $items = SupplyItem::query()->active()->orderBy('code')->get(['id', 'code', 'name', 'description', 'stock_unit']);
        $prefillOrder = null;
        $prefillLines = [];

        if ($request->filled('supply_order_id')) {
            $prefillOrder = SupplyOrder::query()
                ->with(['project', 'items.item'])
                ->find($request->supply_order_id);

            if ($prefillOrder) {
                abort_unless(UserProject::canAccessProjectId((int) $prefillOrder->project_id), 403);
                abort_unless($prefillOrder->canReceive(), 403);

                $prefillLines = $prefillOrder->items
                    ->filter(fn ($line) => $line->quantityOutstanding() > 0)
                    ->map(fn ($line) => [
                        'supply_item_id' => $line->supply_item_id,
                        'supply_order_item_id' => $line->id,
                        'quantity' => $line->quantityOutstanding(),
                        'description' => $line->item->description ?? '',
                        'remarks' => $line->remarks ?? '',
                    ])
                    ->values()
                    ->all();
            }
        }

        $documentNumberPreviews = $projects->mapWithKeys(function ($project) {
            return [
                $project->id => SupplyStockIn::previewNumber((int) $project->id, $project->project_code),
            ];
        })->all();

        $selectedProjectId = old('project_id', $prefillOrder?->project_id);
        $previewDocumentNumber = $selectedProjectId && isset($documentNumberPreviews[$selectedProjectId])
            ? $documentNumberPreviews[$selectedProjectId]
            : '';

        return view('supplies.stock-ins.form', compact(
            'title', 'subtitle', 'projects', 'items', 'prefillOrder', 'prefillLines',
            'documentNumberPreviews', 'previewDocumentNumber'
        ));
    }

    public function show(SupplyStockIn $supplyStockIn)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockIn->project_id)) {
            return $r;
        }

        $supplyStockIn->load(['project', 'order', 'createdBy', 'items.item']);

        return view('supplies.stock-ins.show', [
            'title' => 'Stock In',
            'subtitle' => $supplyStockIn->document_number,
            'stockIn' => $supplyStockIn,
        ]);
    }

    public function print(SupplyStockIn $supplyStockIn)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockIn->project_id)) {
            return $r;
        }

        $supplyStockIn->load(['project', 'order', 'createdBy', 'items.item']);

        return view('supplies.stock-ins.print', [
            'stockIn' => $supplyStockIn,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'stock_date' => 'required|date',
            'notes' => 'nullable|string',
            'supply_order_id' => 'nullable|exists:supply_orders,id',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.remarks' => 'nullable|string|max:500',
            'items.*.supply_order_item_id' => 'nullable|exists:supply_order_items,id',
        ]);

        if ($r = UserProject::guardProjectInAssignmentScope((int) $data['project_id'])) {
            return $r;
        }

        $order = null;
        if (! empty($data['supply_order_id'])) {
            $order = SupplyOrder::query()->with('items')->findOrFail($data['supply_order_id']);
            if (! $order->canReceive()) {
                return back()->withInput()->with('toast_error', 'Stock In can only be linked to an approved Supply Order.');
            }
            if ((int) $order->project_id !== (int) $data['project_id']) {
                return back()->withInput()->with('toast_error', 'Project must match the Supply Order.');
            }
        }

        try {
            DB::beginTransaction();

            $project = Project::query()->findOrFail($data['project_id']);
            $number = SupplyStockIn::allocateNumber((int) $project->id, $project->project_code);

            $stockIn = SupplyStockIn::create([
                'document_number' => $number['document_number'],
                'document_sequence' => $number['document_sequence'],
                'project_id' => $project->id,
                'stock_date' => $data['stock_date'],
                'notes' => $data['notes'] ?? null,
                'supply_order_id' => $order?->id,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['items'] as $line) {
                $orderItemId = $line['supply_order_item_id'] ?? null;
                if ($orderItemId) {
                    if (! $order) {
                        DB::rollBack();

                        return back()->withInput()->with('toast_error', 'Order line can only be used when receiving a Supply Order.');
                    }
                    $orderItem = $order->items->firstWhere('id', $orderItemId);
                    if (! $orderItem) {
                        DB::rollBack();

                        return back()->withInput()->with('toast_error', 'Order line does not belong to this Supply Order.');
                    }
                    if ($orderItem->supply_item_id !== $line['supply_item_id']) {
                        DB::rollBack();

                        return back()->withInput()->with('toast_error', 'Item must match the order line.');
                    }
                    $outstanding = $orderItem->quantityOutstanding();
                    if ((int) $line['quantity'] > $outstanding) {
                        DB::rollBack();

                        return back()->withInput()->with('toast_error', "Quantity exceeds outstanding ({$outstanding}).");
                    }
                }

                SupplyStockInItem::create([
                    'supply_stock_in_id' => $stockIn->id,
                    'supply_item_id' => $line['supply_item_id'],
                    'quantity' => $line['quantity'],
                    'remarks' => $line['remarks'] ?? null,
                    'supply_order_item_id' => $orderItemId,
                ]);
            }

            DB::commit();

            $redirect = $order
                ? redirect()->route('supplies.orders.show', $order)
                : redirect()->route('supplies.stock-ins.show', $stockIn);

            return $redirect->with('toast_success', 'Stock In recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to record Stock In: '.$e->getMessage());
        }
    }

    public function destroy(SupplyStockIn $supplyStockIn)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockIn->project_id)) {
            return $r;
        }

        $supplyStockIn->load('items.item');

        foreach ($supplyStockIn->items as $line) {
            $ending = SupplyStock::endingBalance($line->supply_item_id, (int) $supplyStockIn->project_id);
            if ($ending - (int) $line->quantity < 0) {
                $label = trim(($line->item->code ?? '').' '.($line->item->name ?? 'Item'));

                return back()->with('toast_error', "Cannot delete: {$label} ending balance would become negative.");
            }
        }

        $supplyStockIn->delete();

        return redirect()->route('supplies.stock-ins.index')->with('toast_success', 'Stock In deleted.');
    }
}
