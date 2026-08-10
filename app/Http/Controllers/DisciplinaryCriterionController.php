<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryCriterion;
use App\Models\EmployeeDisciplinary;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DisciplinaryCriterionController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:disciplinary-criteria.show')->only(['index', 'getData', 'options']);
        $this->middleware('permission:disciplinary-criteria.create')->only(['store']);
        $this->middleware('permission:disciplinary-criteria.edit')->only(['update', 'changeStatus']);
        $this->middleware('permission:disciplinary-criteria.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'PP Criteria';
        $subtitle = 'List of Company Regulation Criteria';
        $sanctionTypes = $this->sanctionTypeOptions();

        return view('disciplinary-criteria.index', compact('title', 'subtitle', 'sanctionTypes'));
    }

    public function getData(Request $request)
    {
        $query = DisciplinaryCriterion::query()->orderBy('sanction_type')->orderBy('sort_order')->orderBy('code');

        if ($request->filled('sanction_type')) {
            $query->where('sanction_type', $request->sanction_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('article_reference', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('sanction_type_label', function (DisciplinaryCriterion $row) {
                return $this->sanctionTypeOptions()[$row->sanction_type]
                    ?? (EmployeeDisciplinary::TYPE_LABELS[$row->sanction_type] ?? strtoupper($row->sanction_type));
            })
            ->addColumn('is_active', function (DisciplinaryCriterion $row) {
                return $row->is_active
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', 'disciplinary-criteria.action')
            ->rawColumns(['is_active', 'action'])
            ->toJson();
    }

    public function options(Request $request)
    {
        $request->validate([
            'sanction_type' => 'required|in:counseling,sp1,sp2,sp3',
        ]);

        $items = DisciplinaryCriterion::query()
            ->active()
            ->forSanctionType($request->sanction_type)
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get(['id', 'code', 'title', 'article_reference', 'description', 'sanction_type']);

        return response()->json([
            'data' => $items->map(fn (DisciplinaryCriterion $c) => [
                'id' => $c->id,
                'text' => $c->display_label,
                'code' => $c->code,
                'title' => $c->title,
                'article_reference' => $c->article_reference,
                'description' => $c->description,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DisciplinaryCriterion::create($data);

        return redirect()->route('disciplinary-criteria.index')
            ->with('toast_success', 'PP criterion added successfully');
    }

    public function update(Request $request, DisciplinaryCriterion $disciplinaryCriterion)
    {
        $data = $this->validated($request, $disciplinaryCriterion->id);
        $disciplinaryCriterion->update($data);

        return redirect()->route('disciplinary-criteria.index')
            ->with('toast_success', 'PP criterion updated successfully');
    }

    public function destroy(DisciplinaryCriterion $disciplinaryCriterion)
    {
        if ($disciplinaryCriterion->disciplinaries()->exists()) {
            $disciplinaryCriterion->update(['is_active' => false]);

            return redirect()->route('disciplinary-criteria.index')
                ->with('toast_success', 'Criterion is in use. Status set to inactive.');
        }

        $disciplinaryCriterion->delete();

        return redirect()->route('disciplinary-criteria.index')
            ->with('toast_success', 'PP criterion deleted successfully');
    }

    public function changeStatus(DisciplinaryCriterion $disciplinaryCriterion)
    {
        $disciplinaryCriterion->update(['is_active' => ! $disciplinaryCriterion->is_active]);

        return redirect()->route('disciplinary-criteria.index')
            ->with('toast_success', 'PP criterion status changed successfully');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:disciplinary_criteria,code,'.($ignoreId ?? 'NULL').',id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'article_reference' => 'nullable|string|max:255',
            'sanction_type' => 'required|in:counseling,sp1,sp2,sp3',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    protected function sanctionTypeOptions(): array
    {
        return [
            'counseling' => 'Counseling',
            'sp1' => 'Warning Letter I (SP1)',
            'sp2' => 'Warning Letter II (SP2)',
            'sp3' => 'First & Final Warning (SP3)',
        ];
    }
}
