<?php

namespace App\Http\Controllers;

use App\Exports\SupplyStockInExport;
use App\Imports\SupplyStockInImport;
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
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SupplyStockInController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.stock-in.show')->only(['index', 'data', 'show', 'print', 'export', 'template']);
        $this->middleware('permission:supplies.stock-in.create')->only(['create', 'store']);
        $this->middleware('permission:supplies.stock-in.edit')->only(['edit', 'update']);
        $this->middleware('permission:supplies.stock-in.create|supplies.stock-in.edit')->only(['import']);
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
        $query = $this->filteredHeaderQuery($request);

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

    public function export(Request $request)
    {
        return Excel::download(
            new SupplyStockInExport($this->exportRows($request)),
            'supply-stock-in-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template()
    {
        return Excel::download(
            new SupplyStockInExport(collect()),
            'supply-stock-in-import-template.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xls,xlsx'],
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'The file must be an Excel file (.xls or .xlsx).',
        ]);

        try {
            $import = new SupplyStockInImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            if ($failures->isNotEmpty()) {
                return back()->with('failures', $this->formatImportFailures($failures));
            }

            $message = "Import completed: {$import->created} created, {$import->updated} updated.";

            return redirect()->route('supplies.stock-ins.index')->with('toast_success', $message);
        } catch (ValidationException $e) {
            return back()->with('failures', $this->formatImportFailures($e->failures()));
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Import failed: '.$e->getMessage());
        }
    }

    public function create(Request $request)
    {
        $title = 'Record Stock In';
        $subtitle = 'Add a receipt';
        $projects = UserProject::projectsForSelect();
        $items = SupplyItem::query()->active()->orderBy('code')->get(['id', 'code', 'name', 'description', 'stock_unit']);
        $prefillOrder = null;
        $prefillLines = [];
        $stockIn = null;

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
            'documentNumberPreviews', 'previewDocumentNumber', 'stockIn'
        ));
    }

    public function edit(SupplyStockIn $supplyStockIn)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockIn->project_id)) {
            return $r;
        }

        $supplyStockIn->load(['project', 'order', 'items.item']);

        $title = 'Edit Stock In';
        $subtitle = $supplyStockIn->document_number;
        $projects = UserProject::projectsForSelect();
        $items = SupplyItem::query()->active()->orderBy('code')->get(['id', 'code', 'name', 'description', 'stock_unit']);
        $prefillOrder = $supplyStockIn->order;
        $prefillLines = $supplyStockIn->items->map(fn ($line) => [
            'supply_item_id' => $line->supply_item_id,
            'supply_order_item_id' => $line->supply_order_item_id,
            'quantity' => $line->quantity,
            'description' => $line->item->description ?? '',
            'remarks' => $line->remarks ?? '',
        ])->values()->all();
        $documentNumberPreviews = [];
        $previewDocumentNumber = $supplyStockIn->document_number;
        $stockIn = $supplyStockIn;

        return view('supplies.stock-ins.form', compact(
            'title', 'subtitle', 'projects', 'items', 'prefillOrder', 'prefillLines',
            'documentNumberPreviews', 'previewDocumentNumber', 'stockIn'
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
        $data = $this->validatedPayload($request);

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

            if ($error = $this->assertOrderLinesOk($order, $data['items'])) {
                DB::rollBack();

                return back()->withInput()->with('toast_error', $error);
            }

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

            $this->syncItems($stockIn, $data['items']);

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

    public function update(Request $request, SupplyStockIn $supplyStockIn)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockIn->project_id)) {
            return $r;
        }

        $data = $this->validatedPayload($request, forUpdate: true);
        $supplyStockIn->load(['items', 'order.items']);

        // Project is locked on edit
        $data['project_id'] = (int) $supplyStockIn->project_id;
        $order = $supplyStockIn->order;

        try {
            DB::beginTransaction();

            $oldByItem = $supplyStockIn->items->groupBy('supply_item_id')->map->sum('quantity')->all();
            $newByItem = collect($data['items'])->groupBy('supply_item_id')->map->sum('quantity')->all();

            if ($error = $this->assertStockInBalancesOk((int) $supplyStockIn->project_id, $oldByItem, $newByItem)) {
                DB::rollBack();

                return back()->withInput()->with('toast_error', $error);
            }

            if ($error = $this->assertOrderLinesOk($order, $data['items'], $supplyStockIn)) {
                DB::rollBack();

                return back()->withInput()->with('toast_error', $error);
            }

            $supplyStockIn->update([
                'stock_date' => $data['stock_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $supplyStockIn->items()->delete();
            $this->syncItems($supplyStockIn, $data['items']);

            DB::commit();

            return redirect()->route('supplies.stock-ins.show', $supplyStockIn)
                ->with('toast_success', 'Stock In updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update Stock In: '.$e->getMessage());
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

    private function validatedPayload(Request $request, bool $forUpdate = false): array
    {
        return $request->validate([
            'project_id' => [$forUpdate ? 'nullable' : 'required', 'exists:projects,id'],
            'stock_date' => 'required|date',
            'notes' => 'nullable|string',
            'supply_order_id' => 'nullable|exists:supply_orders,id',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.remarks' => 'nullable|string|max:500',
            'items.*.supply_order_item_id' => 'nullable|exists:supply_order_items,id',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(SupplyStockIn $stockIn, array $items): void
    {
        foreach ($items as $line) {
            SupplyStockInItem::create([
                'supply_stock_in_id' => $stockIn->id,
                'supply_item_id' => $line['supply_item_id'],
                'quantity' => $line['quantity'],
                'remarks' => $line['remarks'] ?? null,
                'supply_order_item_id' => $line['supply_order_item_id'] ?? null,
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function assertOrderLinesOk(?SupplyOrder $order, array $items, ?SupplyStockIn $current = null): ?string
    {
        foreach ($items as $line) {
            $orderItemId = $line['supply_order_item_id'] ?? null;
            if (! $orderItemId) {
                continue;
            }
            if (! $order) {
                return 'Order line can only be used when receiving a Supply Order.';
            }
            $orderItem = $order->items->firstWhere('id', $orderItemId);
            if (! $orderItem) {
                return 'Order line does not belong to this Supply Order.';
            }
            if ($orderItem->supply_item_id !== $line['supply_item_id']) {
                return 'Item must match the order line.';
            }

            $alreadyOnThisDoc = $current
                ? (int) $current->items->where('supply_order_item_id', $orderItemId)->sum('quantity')
                : 0;
            $maxAllowed = $orderItem->quantityOutstanding() + $alreadyOnThisDoc;
            if ((int) $line['quantity'] > $maxAllowed) {
                return "Quantity exceeds outstanding ({$maxAllowed}).";
            }
        }

        return null;
    }

    /**
     * @param  array<string, int>  $oldByItem
     * @param  array<string, int>  $newByItem
     */
    private function assertStockInBalancesOk(int $projectId, array $oldByItem, array $newByItem): ?string
    {
        $itemIds = array_unique(array_merge(array_keys($oldByItem), array_keys($newByItem)));
        foreach ($itemIds as $itemId) {
            $old = (int) ($oldByItem[$itemId] ?? 0);
            $new = (int) ($newByItem[$itemId] ?? 0);
            $ending = SupplyStock::endingBalance($itemId, $projectId);
            if ($ending - $old + $new < 0) {
                $item = SupplyItem::query()->find($itemId);
                $label = trim(($item->code ?? '').' '.($item->name ?? 'Item'));

                return "{$label}: ending balance would become negative.";
            }
        }

        return null;
    }

    private function filteredHeaderQuery(Request $request)
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

        return $query;
    }

    private function exportRows(Request $request)
    {
        $headers = $this->filteredHeaderQuery($request)->with(['project', 'items.item'])->get();

        return $headers->flatMap(function (SupplyStockIn $header) {
            return $header->items->map(function (SupplyStockInItem $line) use ($header) {
                return (object) [
                    'document_number' => $header->document_number,
                    'project_code' => $header->project->project_code ?? '',
                    'stock_date' => $header->stock_date?->format('Y-m-d'),
                    'notes' => $header->notes ?? '',
                    'item_code' => $line->item->code ?? '',
                    'stock_unit' => $line->item->stock_unit ?? '',
                    'quantity' => $line->quantity,
                    'remarks' => $line->remarks ?? '',
                ];
            });
        })->values();
    }

    protected function formatImportFailures(iterable $failures)
    {
        return collect($failures)->map(function ($failure) {
            $values = $failure->values();
            $attribute = $failure->attribute();
            $value = is_array($values) && array_key_exists($attribute, $values) ? $values[$attribute] : null;

            return [
                'sheet' => 'Stock In',
                'row' => $failure->row(),
                'attribute' => $attribute,
                'value' => $value,
                'errors' => implode(', ', $failure->errors()),
            ];
        });
    }
}
