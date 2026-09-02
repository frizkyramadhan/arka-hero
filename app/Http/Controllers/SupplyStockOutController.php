<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\SupplyItem;
use App\Models\SupplyStockOut;
use App\Models\SupplyStockOutItem;
use App\Services\SupplyStock;
use App\Support\UserProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SupplyStockOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.stock-out.show')->only(['index', 'data', 'show', 'print']);
        $this->middleware('permission:supplies.stock-out.create')->only(['create', 'store']);
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

    public function create()
    {
        $title = 'Record Stock Out';
        $subtitle = 'Issue from stock';
        $projects = UserProject::projectsForSelect();
        $items = SupplyItem::query()->active()->orderBy('code')->get(['id', 'code', 'name', 'description', 'stock_unit']);

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
            'title', 'subtitle', 'projects', 'items', 'documentNumberPreviews', 'previewDocumentNumber'
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
        $data = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'stock_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.supply_item_id' => 'required|exists:supply_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.location' => 'required|string|max:255',
            'items.*.person_in_charge' => 'required|string|max:255',
        ]);

        if ($r = UserProject::guardProjectInAssignmentScope((int) $data['project_id'])) {
            return $r;
        }

        $qtyByItem = [];
        foreach ($data['items'] as $line) {
            $id = $line['supply_item_id'];
            $qtyByItem[$id] = ($qtyByItem[$id] ?? 0) + (int) $line['quantity'];
        }

        try {
            DB::beginTransaction();

            $itemIds = array_keys($qtyByItem);
            SupplyItem::query()->whereIn('id', $itemIds)->lockForUpdate()->get();

            foreach ($qtyByItem as $itemId => $qty) {
                $balance = SupplyStock::endingBalance($itemId, (int) $data['project_id']);
                if ($qty > $balance) {
                    $item = SupplyItem::query()->find($itemId);
                    $label = trim(($item->code ?? '').' '.($item->name ?? 'Item'));
                    DB::rollBack();

                    return back()->withInput()->with('toast_error', "{$label}: quantity exceeds ending balance ({$balance}).");
                }
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

            foreach ($data['items'] as $line) {
                SupplyStockOutItem::create([
                    'supply_stock_out_id' => $stockOut->id,
                    'supply_item_id' => $line['supply_item_id'],
                    'quantity' => $line['quantity'],
                    'location' => $line['location'],
                    'person_in_charge' => $line['person_in_charge'],
                ]);
            }

            DB::commit();

            return redirect()->route('supplies.stock-outs.show', $stockOut)
                ->with('toast_success', 'Stock Out recorded.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to record Stock Out: '.$e->getMessage());
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
}
