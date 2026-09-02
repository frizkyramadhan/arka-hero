<?php

namespace App\Http\Controllers;

use App\Exports\SupplyItemExport;
use App\Imports\SupplyItemImport;
use App\Models\SupplyItem;
use App\Models\SupplyItemCategory;
use App\Services\SupplyStock;
use App\Support\UserProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class SupplyItemController extends Controller
{
    /** @var array<string, array{in: int, out: int, ending: int}> */
    private array $stockTotalsCache = [];

    /** @var \Illuminate\Support\Collection<int, SupplyItemCategory>|null */
    private $categoriesCache = null;

    public function __construct()
    {
        $this->middleware('permission:supplies.catalog.show')->only(['index', 'data', 'export', 'template']);
        $this->middleware('permission:supplies.catalog.create')->only(['store']);
        $this->middleware('permission:supplies.catalog.edit')->only(['update']);
        $this->middleware('permission:supplies.catalog.delete')->only(['destroy']);
        $this->middleware('permission:supplies.catalog.create|supplies.catalog.edit')->only(['import']);
        $this->middleware('auth')->only(['search']);
    }

    public function index()
    {
        $title = 'Catalog';
        $subtitle = 'Supply items';
        $projects = UserProject::projectsForSelect();
        $defaultProjectId = $this->defaultBalanceProjectId($projects);
        $categories = SupplyItemCategory::query()->orderBy('name')->get();
        $categoryCodePreviews = $categories
            ->where('status', 'active')
            ->mapWithKeys(fn (SupplyItemCategory $category) => [
                $category->id => SupplyItem::previewCode($category),
            ])
            ->all();

        return view('supplies.catalog.index', compact(
            'title', 'subtitle', 'projects', 'categories', 'categoryCodePreviews', 'defaultProjectId'
        ));
    }

    public function data(Request $request)
    {
        $projectId = $request->filled('project_id') ? (int) $request->project_id : null;

        $query = $this->filteredCatalogQuery($request);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('category_label', fn ($row) => display_text($row->categoryLabel()))
            ->addColumn('stock_in', function ($row) use ($projectId) {
                if (! $projectId) {
                    return '—';
                }

                return $this->stockTotals($row->id, $projectId)['in'];
            })
            ->addColumn('stock_out', function ($row) use ($projectId) {
                if (! $projectId) {
                    return '—';
                }

                return $this->stockTotals($row->id, $projectId)['out'];
            })
            ->addColumn('ending_balance', function ($row) use ($projectId) {
                if (! $projectId) {
                    return '—';
                }

                return $this->stockTotals($row->id, $projectId)['ending'];
            })
            ->addColumn('status_badge', function ($row) {
                $class = $row->status === 'active' ? 'success' : 'secondary';

                return '<span class="badge badge-'.$class.'">'.e(ucfirst($row->status)).'</span>';
            })
            ->addColumn('action', function ($model) {
                $categories = $this->categoriesForForms();

                return view('supplies.catalog.action', compact('model', 'categories'))->render();
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    public function export(Request $request)
    {
        return Excel::download(
            new SupplyItemExport($this->filteredCatalogQuery($request)),
            'supply-catalog-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template()
    {
        $empty = SupplyItem::query()->whereRaw('1 = 0')->with('category');

        return Excel::download(
            new SupplyItemExport($empty),
            'supply-catalog-import-template.xlsx'
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
            $import = new SupplyItemImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            if ($failures->isNotEmpty()) {
                return back()->with('failures', $this->formatImportFailures($failures));
            }

            $message = "Import completed: {$import->created} created, {$import->updated} updated.";

            return redirect()->route('supplies.catalog.index')->with('toast_success', $message);
        } catch (ValidationException $e) {
            return back()->with('failures', $this->formatImportFailures($e->failures()));
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Import failed: '.$e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $category = SupplyItemCategory::query()->findOrFail($data['supply_item_category_id']);

        try {
            DB::beginTransaction();
            $data['code'] = SupplyItem::nextCode($category);
            SupplyItem::create($data);
            DB::commit();

            return redirect()->route('supplies.catalog.index')
                ->with('toast_success', 'Item added to catalog.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add item: '.$e->getMessage());
        }
    }

    public function update(Request $request, SupplyItem $supplyItem)
    {
        $data = $this->validated($request, $supplyItem);
        unset($data['supply_item_category_id']);

        try {
            DB::beginTransaction();
            $supplyItem->update($data);
            DB::commit();

            return redirect()->route('supplies.catalog.index')
                ->with('toast_success', 'Catalog item updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update item: '.$e->getMessage());
        }
    }

    public function destroy(SupplyItem $supplyItem)
    {
        if ($supplyItem->hasMovements()) {
            return back()->with('toast_error', 'Cannot delete an item that has stock movements or orders.');
        }

        $supplyItem->delete();

        return redirect()->route('supplies.catalog.index')
            ->with('toast_success', 'Catalog item deleted.');
    }

    public function search(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $items = SupplyItem::query()
            ->active()
            ->with('category:id,name,prefix')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->limit(30)
            ->get(['id', 'code', 'name', 'description', 'supply_item_category_id', 'stock_unit']);

        return response()->json($items);
    }

    private function validated(Request $request, ?SupplyItem $item = null): array
    {
        return $request->validate([
            'supply_item_category_id' => [$item ? 'sometimes' : 'required', 'exists:supply_item_categories,id'],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'stock_unit' => 'required|string|max:50',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, SupplyItemCategory>
     */
    private function categoriesForForms()
    {
        return $this->categoriesCache ??= SupplyItemCategory::query()->orderBy('name')->get();
    }

    /**
     * @return array{in: int, out: int, ending: int}
     */
    private function stockTotals(string $itemId, int $projectId): array
    {
        $key = $itemId.':'.$projectId;

        return $this->stockTotalsCache[$key] ??= SupplyStock::totals($itemId, $projectId);
    }

    /**
     * Default project for per-project stock columns on the catalog list.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\Project>  $projects
     */
    private function defaultBalanceProjectId($projects): ?int
    {
        $projectIds = $projects->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($projectIds === []) {
            return null;
        }

        $user = auth()->user();
        $user?->loadMissing('employee.activeAdministration');
        $adminProjectId = (int) ($user?->employee?->activeAdministration?->project_id ?? 0);

        if ($adminProjectId > 0 && in_array($adminProjectId, $projectIds, true)) {
            return $adminProjectId;
        }

        if (count($projectIds) === 1) {
            return $projectIds[0];
        }

        return null;
    }

    private function filteredCatalogQuery(Request $request): Builder
    {
        $query = SupplyItem::query()
            ->with('category')
            ->orderBy('code');

        if ($request->filled('category_id')) {
            $query->where('supply_item_category_id', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        return $query;
    }

    protected function formatImportFailures(iterable $failures)
    {
        return collect($failures)->map(function ($failure) {
            $values = $failure->values();
            $attribute = $failure->attribute();
            $value = is_array($values) && array_key_exists($attribute, $values) ? $values[$attribute] : null;

            return [
                'sheet' => 'Catalog',
                'row' => $failure->row(),
                'attribute' => $attribute,
                'value' => $value,
                'errors' => implode(', ', $failure->errors()),
            ];
        });
    }
}
