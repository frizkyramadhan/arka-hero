<?php

namespace App\Http\Controllers;

use App\Exports\SupplyStockOutExport;
use App\Imports\SupplyStockOutImport;
use App\Models\Project;
use App\Models\SupplyItem;
use App\Models\SupplyStockOut;
use App\Models\SupplyStockOutItem;
use App\Services\SupplyStock;
use App\Support\UserProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SupplyStockOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.stock-out.show')->only(['index', 'data', 'show', 'print', 'export', 'template']);
        $this->middleware('permission:supplies.stock-out.create')->only(['create', 'store']);
        $this->middleware('permission:supplies.stock-out.edit')->only(['edit', 'update']);
        $this->middleware('permission:supplies.stock-out.create|supplies.stock-out.edit')->only(['import']);
        $this->middleware('permission:supplies.stock-out.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'Stock Out';
        $subtitle = 'Stock issues';
        $projects = UserProject::projectsForSelect();

        return view('supplies.stock-outs.index', compact('title', 'subtitle', 'projects'));
    }

    public function data(Request $request)
    {
        $query = $this->filteredHeaderQuery($request);

        return datatables()->of($query)
            ->addIndexColumn()
            ->editColumn('stock_date', fn ($row) => $row->stock_date?->format('d/m/Y'))
            ->addColumn('project_label', fn ($row) => display_text(trim(($row->project->project_code ?? '').' - '.($row->project->project_name ?? ''), ' -')))
            ->addColumn('action', function ($model) {
                return view('supplies.stock-outs.action', compact('model'))->render();
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function export(Request $request)
    {
        return Excel::download(
            new SupplyStockOutExport($this->exportRows($request)),
            'supply-stock-out-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template()
    {
        return Excel::download(
            new SupplyStockOutExport(collect()),
            'supply-stock-out-import-template.xlsx'
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
            $import = new SupplyStockOutImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            if ($failures->isNotEmpty()) {
                return back()->with('failures', $this->formatImportFailures($failures));
            }

            $message = "Import completed: {$import->created} created, {$import->updated} updated.";

            return redirect()->route('supplies.stock-outs.index')->with('toast_success', $message);
        } catch (ValidationException $e) {
            return back()->with('failures', $this->formatImportFailures($e->failures()));
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Import failed: '.$e->getMessage());
        }
    }

    public function create()
    {
        $title = 'Record Stock Out';
        $subtitle = 'Issue from stock';
        $projects = UserProject::projectsForSelect();
        $items = SupplyItem::query()->active()->orderBy('code')->get(['id', 'code', 'name', 'description', 'stock_unit']);
        $stockOut = null;

        $documentNumberPreviews = $projects->mapWithKeys(function ($project) {
            return [
                $project->id => SupplyStockOut::previewNumber((int) $project->id, $project->project_code),
            ];
        })->all();

        $selectedProjectId = old('project_id');
        $previewDocumentNumber = $selectedProjectId && isset($documentNumberPreviews[$selectedProjectId])
            ? $documentNumberPreviews[$selectedProjectId]
            : '';

        return view('supplies.stock-outs.form', compact(
            'title', 'subtitle', 'projects', 'items', 'documentNumberPreviews', 'previewDocumentNumber', 'stockOut'
        ));
    }

    public function edit(SupplyStockOut $supplyStockOut)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockOut->project_id)) {
            return $r;
        }

        $supplyStockOut->load(['project', 'items.item']);

        $title = 'Edit Stock Out';
        $subtitle = $supplyStockOut->document_number;
        $projects = UserProject::projectsForSelect();
        $items = SupplyItem::query()->active()->orderBy('code')->get(['id', 'code', 'name', 'description', 'stock_unit']);
        $documentNumberPreviews = [];
        $previewDocumentNumber = $supplyStockOut->document_number;
        $stockOut = $supplyStockOut;
        $prefillLines = $supplyStockOut->items->map(fn ($line) => [
            'supply_item_id' => $line->supply_item_id,
            'quantity' => $line->quantity,
            'location' => $line->location,
            'person_in_charge' => $line->person_in_charge,
            'description' => $line->item->description ?? '',
        ])->values()->all();

        return view('supplies.stock-outs.form', compact(
            'title', 'subtitle', 'projects', 'items', 'documentNumberPreviews', 'previewDocumentNumber', 'stockOut', 'prefillLines'
        ));
    }

    public function show(SupplyStockOut $supplyStockOut)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockOut->project_id)) {
            return $r;
        }

        $supplyStockOut->load(['project', 'createdBy', 'items.item']);

        return view('supplies.stock-outs.show', [
            'title' => 'Stock Out',
            'subtitle' => $supplyStockOut->document_number,
            'stockOut' => $supplyStockOut,
        ]);
    }

    public function print(SupplyStockOut $supplyStockOut)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockOut->project_id)) {
            return $r;
        }

        $supplyStockOut->load(['project', 'createdBy', 'items.item']);

        return view('supplies.stock-outs.print', [
            'stockOut' => $supplyStockOut,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedPayload($request);

        if ($r = UserProject::guardProjectInAssignmentScope((int) $data['project_id'])) {
            return $r;
        }

        $qtyByItem = collect($data['items'])->groupBy('supply_item_id')->map->sum('quantity')->all();

        try {
            DB::beginTransaction();

            if ($error = $this->assertStockOutBalancesOk((int) $data['project_id'], [], $qtyByItem)) {
                DB::rollBack();

                return back()->withInput()->with('toast_error', $error);
            }

            $project = Project::query()->findOrFail($data['project_id']);
            $number = SupplyStockOut::allocateNumber((int) $project->id, $project->project_code);

            $stockOut = SupplyStockOut::create([
                'document_number' => $number['document_number'],
                'document_sequence' => $number['document_sequence'],
                'project_id' => $project->id,
                'stock_date' => $data['stock_date'],
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            $this->syncItems($stockOut, $data['items']);

            DB::commit();

            return redirect()->route('supplies.stock-outs.show', $stockOut)
                ->with('toast_success', 'Stock Out recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to record Stock Out: '.$e->getMessage());
        }
    }

    public function update(Request $request, SupplyStockOut $supplyStockOut)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockOut->project_id)) {
            return $r;
        }

        $data = $this->validatedPayload($request, forUpdate: true);
        $supplyStockOut->load('items');

        $data['project_id'] = (int) $supplyStockOut->project_id;
        $oldByItem = $supplyStockOut->items->groupBy('supply_item_id')->map->sum('quantity')->all();
        $newByItem = collect($data['items'])->groupBy('supply_item_id')->map->sum('quantity')->all();

        try {
            DB::beginTransaction();

            if ($error = $this->assertStockOutBalancesOk((int) $supplyStockOut->project_id, $oldByItem, $newByItem)) {
                DB::rollBack();

                return back()->withInput()->with('toast_error', $error);
            }

            $supplyStockOut->update([
                'stock_date' => $data['stock_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            $supplyStockOut->items()->delete();
            $this->syncItems($supplyStockOut, $data['items']);

            DB::commit();

            return redirect()->route('supplies.stock-outs.show', $supplyStockOut)
                ->with('toast_success', 'Stock Out updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update Stock Out: '.$e->getMessage());
        }
    }

    public function destroy(SupplyStockOut $supplyStockOut)
    {
        if ($r = UserProject::guardProjectInAssignmentScope((int) $supplyStockOut->project_id)) {
            return $r;
        }

        $supplyStockOut->delete();

        return redirect()->route('supplies.stock-outs.index')->with('toast_success', 'Stock Out deleted.');
    }

    private function validatedPayload(Request $request, bool $forUpdate = false): array
    {
        return $request->validate([
            'project_id' => [$forUpdate ? 'nullable' : 'required', 'exists:projects,id'],
            'stock_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.location' => 'required|string|max:255',
            'items.*.person_in_charge' => 'required|string|max:255',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(SupplyStockOut $stockOut, array $items): void
    {
        foreach ($items as $line) {
            SupplyStockOutItem::create([
                'supply_stock_out_id' => $stockOut->id,
                'supply_item_id' => $line['supply_item_id'],
                'quantity' => $line['quantity'],
                'location' => $line['location'],
                'person_in_charge' => $line['person_in_charge'],
            ]);
        }
    }

    /**
     * @param  array<string, int>  $oldByItem
     * @param  array<string, int>  $newByItem
     */
    private function assertStockOutBalancesOk(int $projectId, array $oldByItem, array $newByItem): ?string
    {
        $itemIds = array_unique(array_merge(array_keys($oldByItem), array_keys($newByItem)));
        SupplyItem::query()->whereIn('id', $itemIds)->lockForUpdate()->get();

        foreach ($itemIds as $itemId) {
            $old = (int) ($oldByItem[$itemId] ?? 0);
            $new = (int) ($newByItem[$itemId] ?? 0);
            $ending = SupplyStock::endingBalance($itemId, $projectId);
            if ($ending + $old - $new < 0) {
                $item = SupplyItem::query()->find($itemId);
                $label = trim(($item->code ?? '').' '.($item->name ?? 'Item'));

                return "{$label}: quantity exceeds ending balance (".($ending + $old).').';
            }
        }

        return null;
    }

    private function filteredHeaderQuery(Request $request)
    {
        $query = SupplyStockOut::query()
            ->with(['project', 'createdBy'])
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

        return $headers->flatMap(function (SupplyStockOut $header) {
            return $header->items->map(function (SupplyStockOutItem $line) use ($header) {
                return (object) [
                    'document_number' => $header->document_number,
                    'project_code' => $header->project->project_code ?? '',
                    'stock_date' => $header->stock_date?->format('Y-m-d'),
                    'notes' => $header->notes ?? '',
                    'item_code' => $line->item->code ?? '',
                    'stock_unit' => $line->item->stock_unit ?? '',
                    'quantity' => $line->quantity,
                    'location' => $line->location,
                    'person_in_charge' => $line->person_in_charge,
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
                'sheet' => 'Stock Out',
                'row' => $failure->row(),
                'attribute' => $attribute,
                'value' => $value,
                'errors' => implode(', ', $failure->errors()),
            ];
        });
    }
}
