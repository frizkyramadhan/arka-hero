<?php

namespace App\Http\Controllers;

use App\Models\NationalHoliday;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NationalHolidayController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:national-holidays.show')->only('index', 'data');
        $this->middleware('permission:national-holidays.create')->only('store');
        $this->middleware('permission:national-holidays.edit')->only('update');
        $this->middleware('permission:national-holidays.delete')->only('destroy');
    }

    public function index(): View
    {
        $user = auth()->user();

        $currentYear = (int) date('Y');
        $years = range($currentYear - 10, $currentYear + 5);
        rsort($years);

        $months = [
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
        ];

        return view('national-holidays.index', [
            'title' => 'National Holidays',
            'showActionColumn' => $user->can('national-holidays.edit') || $user->can('national-holidays.delete'),
            'years' => $years,
            'months' => $months,
        ]);
    }

    /**
     * Server-side DataTables JSON for national holidays list.
     */
    public function data(Request $request): JsonResponse
    {
        $query = NationalHoliday::query()->select('national_holidays.*');

        if ($request->filled('filter_year')) {
            $year = (int) $request->input('filter_year');
            if ($year > 0) {
                $query->whereYear('holiday_date', $year);
            }
        }

        if ($request->filled('filter_month')) {
            $month = (int) $request->input('filter_month');
            if ($month >= 1 && $month <= 12) {
                $query->whereMonth('holiday_date', $month);
            }
        }

        if ($request->filled('filter_name')) {
            $name = trim((string) $request->input('filter_name'));
            if ($name !== '') {
                $query->where('name', 'like', '%'.$name.'%');
            }
        }

        $user = auth()->user();
        $canEdit = $user->can('national-holidays.edit');
        $canDelete = $user->can('national-holidays.delete');

        $dt = datatables()
            ->of($query)
            ->addIndexColumn()
            ->editColumn('holiday_date', function (NationalHoliday $row) {
                return $row->holiday_date->format('d F Y');
            })

            ->editColumn('name', function (NationalHoliday $row) {
                return $row->name ? e($row->name) : '—';
            });

        if ($canEdit || $canDelete) {
            $dt->addColumn('action', function (NationalHoliday $row) use ($canEdit, $canDelete) {
                $html = '<div class="text-center">';
                if ($canEdit) {
                    $html .= '<button type="button" class="btn btn-sm btn-warning btn-edit-holiday mr-1" title="Edit" data-date="'
                        .$row->holiday_date->format('Y-m-d').'" data-name="'.e($row->name).'" data-update-url="'
                        .route('leave.national-holidays.update', $row).'"><i class="fas fa-edit"></i></button>';
                }
                if ($canDelete) {
                    $html .= '<form method="post" action="'.route('leave.national-holidays.destroy', $row)
                        .'" class="d-inline form-delete-holiday">'
                        .csrf_field().method_field('DELETE')
                        .'<button type="button" class="btn btn-sm btn-danger btn-delete-holiday" title="Delete"><i class="fas fa-trash"></i></button></form>';
                }
                $html .= '</div>';

                return $html;
            })->rawColumns(['action']);
        }

        return $dt->make(true);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'holiday_date' => 'required|date|unique:national_holidays,holiday_date',
            'name' => 'nullable|string|max:255',
        ], [
            'holiday_date.unique' => 'This date is already registered as a national holiday.',
        ]);

        NationalHoliday::create($validated);

        return redirect()
            ->route('leave.national-holidays.index')
            ->with('toast_success', 'National holiday added successfully.');
    }

    public function update(Request $request, NationalHoliday $nationalHoliday): RedirectResponse
    {
        $validated = $request->validate([
            'holiday_date' => [
                'required',
                'date',
                Rule::unique('national_holidays', 'holiday_date')->ignore($nationalHoliday->id),
            ],
            'name' => 'nullable|string|max:255',
        ], [
            'holiday_date.unique' => 'This date is already registered as a national holiday.',
        ]);

        $nationalHoliday->update($validated);

        return redirect()
            ->route('leave.national-holidays.index')
            ->with('toast_success', 'National holiday updated successfully.');
    }

    public function destroy(NationalHoliday $nationalHoliday): RedirectResponse
    {
        $nationalHoliday->delete();

        return redirect()
            ->route('leave.national-holidays.index')
            ->with('toast_success', 'National holiday deleted successfully.');
    }
}
