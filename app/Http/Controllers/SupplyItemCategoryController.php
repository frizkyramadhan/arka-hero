<?php

namespace App\Http\Controllers;

use App\Models\SupplyItemCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SupplyItemCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:supplies.item-categories.show')->only(['index', 'data']);
        $this->middleware('permission:supplies.item-categories.create')->only(['store']);
        $this->middleware('permission:supplies.item-categories.edit')->only(['update']);
        $this->middleware('permission:supplies.item-categories.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'Item Categories';
        $subtitle = 'Supply item categories';

        return view('supplies.categories.index', compact('title', 'subtitle'));
    }

    public function data()
    {
        $query = SupplyItemCategory::query()
            ->withCount('items')
            ->orderBy('name');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('status_badge', function ($row) {
                $class = $row->status === 'active' ? 'success' : 'secondary';

                return '<span class="badge badge-'.$class.'">'.e(ucfirst($row->status)).'</span>';
            })
            ->addColumn('action', function ($model) {
                return view('supplies.categories.action', compact('model'))->render();
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            DB::beginTransaction();
            SupplyItemCategory::create($data);
            DB::commit();

            return redirect()->route('supplies.item-categories.index')
                ->with('toast_success', 'Item Category added.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add Item Category: '.$e->getMessage());
        }
    }

    public function update(Request $request, SupplyItemCategory $supplyItemCategory)
    {
        $data = $this->validated($request, $supplyItemCategory);

        if (! $supplyItemCategory->canChangePrefix()) {
            unset($data['prefix']);
        }

        try {
            DB::beginTransaction();
            $supplyItemCategory->update($data);
            DB::commit();

            return redirect()->route('supplies.item-categories.index')
                ->with('toast_success', 'Item Category updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update Item Category: '.$e->getMessage());
        }
    }

    public function destroy(SupplyItemCategory $supplyItemCategory)
    {
        if ($supplyItemCategory->items()->exists()) {
            return back()->with('toast_error', 'Cannot delete an Item Category that has catalog items.');
        }

        $supplyItemCategory->delete();

        return redirect()->route('supplies.item-categories.index')
            ->with('toast_success', 'Item Category deleted.');
    }

    private function validated(Request $request, ?SupplyItemCategory $category = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'prefix' => [
                $category && ! $category->canChangePrefix() ? 'sometimes' : 'required',
                'string',
                'max:10',
                'regex:/^[A-Za-z]+$/',
                Rule::unique('supply_item_categories', 'prefix')->ignore($category?->id),
            ],
            'description' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
