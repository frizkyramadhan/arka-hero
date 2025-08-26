<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use App\Models\LetterSubject;
use App\Models\Administration;
use App\Models\LetterCategory;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LetterAdministrationExport;
use App\Imports\LetterAdministrationImport;
use App\Imports\Sheets\InternalSheetImport;
use Maatwebsite\Excel\Validators\ValidationException;

class LetterNumberController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:letter-numbers.show')->only(['index', 'show']);
        $this->middleware('permission:letter-numbers.create')->only('create');
        $this->middleware('permission:letter-numbers.edit')->only('edit');
        $this->middleware('permission:letter-numbers.delete')->only('destroy');
    }

    public function index()
    {
        $title = 'Letter Number Administration';
        $subtitle = 'Letter Numbers List';
        $categories = LetterCategory::where('is_active', 1)->orderBy('category_code', 'asc')->get();

        return view('letter-numbers.index', compact('title', 'subtitle', 'categories'));
    }

    public function getLetterNumbers(Request $request)
    {
        $letterNumbers = LetterNumber::with([
            'category',
            'subject',
            'administration.employee',
            'administration.project',
            'project',
            'user',
            'reservedBy',
            'usedBy'
        ])
            ->when($request->letter_number, function ($query, $letterNumber) {
                return $query->where('letter_number', 'like', '%' . $letterNumber . '%');
            })
            ->when($request->letter_category_id, function ($query, $category) {
                return $query->where('letter_category_id', $category);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                return $query->where('letter_date', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                return $query->where('letter_date', '<=', $date);
            })
            ->when($request->destination, function ($query, $destination) {
                return $query->where('destination', 'like', '%' . $destination . '%');
            })
            ->when($request->remarks, function ($query, $remarks) {
                return $query->where('remarks', 'like', '%' . $remarks . '%');
            })
            ->orderBy('created_at', 'desc')
            ->orderBy('sequence_number', 'desc');

        return datatables()->of($letterNumbers)
            ->addIndexColumn()
            ->addColumn('category_name', function ($row) {
                return $row->category->category_name ?? '-';
            })
            ->addColumn('subject_display', function ($row) {
                return $row->subject->subject_name ?? $row->custom_subject ?? '-';
            })
            ->addColumn('letter_date', function ($row) {
                return $row->letter_date ? date('d-m-Y', strtotime($row->letter_date)) : '-';
            })
            ->addColumn('destination', function ($row) {
                return $row->destination ?
                    '<span title="' . htmlspecialchars($row->destination) . '">' .
                    (strlen($row->destination) > 30 ? substr($row->destination, 0, 27) . '...' : $row->destination) . '</span>' : '-';
            })
            // ->addColumn('employee_display', function ($row) {
            //     if ($row->administration && $row->administration->employee) {
            //         return $row->administration->employee->fullname .
            //             ' (' . $row->administration->nik . ')';
            //     }
            //     return '-';
            // })
            // ->addColumn('project_display', function ($row) {
            //     // Prioritas: project dari administration, lalu project langsung
            //     $project = $row->administration && $row->administration->project
            //         ? $row->administration->project
            //         : $row->project;
            //     return $project ? $project->project_name : '-';
            // })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'reserved' => '<span class="badge badge-warning">Reserved</span>',
                    'used' => '<span class="badge badge-success">Used</span>',
                    'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('remarks', function ($row) {
                return $row->remarks ?
                    '<span title="' . htmlspecialchars($row->remarks) . '">' .
                    (strlen($row->remarks) > 50 ? substr($row->remarks, 0, 47) . '...' : $row->remarks) . '</span>' : '-';
            })
            ->addColumn('action', function ($row) {
                return view('letter-numbers.action', compact('row'));
            })
            ->rawColumns(['status_badge', 'destination', 'remarks', 'action'])
            ->toJson();
    }

    public function create($categoryCode = null)
    {
        $title = 'Create Letter Number';
        $categories = LetterCategory::where('is_active', 1)->orderBy('category_code', 'asc')->get();

        // Menggunakan administrations aktif untuk dropdown karyawan
        $administrations = Administration::with(['employee', 'project', 'position'])
            ->active()
            ->orderBy('nik')
            ->get();
        $projects = Project::orderBy('project_name')->get();

        $selectedCategory = null;
        $subjects = collect();

        if ($categoryCode) {
            $selectedCategory = LetterCategory::where('category_code', $categoryCode)->first();
            if ($selectedCategory) {
                $subjects = LetterSubject::where('letter_category_id', $selectedCategory->id)
                    ->where('is_active', 1)
                    ->get();
            }
        }

        // Get estimated next numbers for all categories
        $estimatedNextNumbers = LetterNumber::getEstimatedNextNumbersForAllCategories();

        // Get last numbers for each category for context
        $lastNumbersByCategory = [];
        $letterCountsByCategory = [];
        foreach ($categories as $category) {
            $lastNumbersByCategory[$category->id] = LetterNumber::getLastNumbersForCategory($category->id, 3);
            $letterCountsByCategory[$category->id] = LetterNumber::getLetterCountForCategory($category->id);
        }

        return view('letter-numbers.create', compact(
            'title',
            'categories',
            'subjects',
            'administrations',
            'projects',
            'selectedCategory',
            'estimatedNextNumbers',
            'lastNumbersByCategory',
            'letterCountsByCategory'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'letter_category_id' => 'required|exists:letter_categories,id',
            'letter_date' => 'required|date',
            'destination' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
        ];

        // Dynamic validation based on category
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            switch ($category->category_code) {
                case 'A':
                case 'B':
                    $rules['classification'] = 'nullable|in:Umum,Lembaga Pendidikan,Pemerintah';
                    break;

                case 'PKWT':
                    // PKWT no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['duration'] = 'nullable';
                    $rules['start_date'] = 'nullable|date';
                    $rules['end_date'] = 'nullable|date|after:start_date';
                    $rules['pkwt_type'] = 'required|in:PKWT, PKWTT';
                    break;

                case 'PAR':
                    // PAR no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['par_type'] = 'required|in:new hire,promosi,mutasi,demosi';
                    break;

                case 'CRTE':
                    // CRTE no longer requires administration_id (NIK) - allowing import without NIK
                    break;
                case 'SKPK':
                    // SKPK no longer requires administration_id (NIK)
                    break;

                case 'FR':
                    $rules['ticket_classification'] = 'required|in:Pesawat,Kereta Api,Bus';
                    break;
            }
        }

        $request->validate($rules);

        $letterNumber = new LetterNumber();
        $letterNumber->fill($request->all());
        $letterNumber->user_id = auth()->id();
        $letterNumber->save();

        return redirect()->route('letter-numbers.index')
            ->with('toast_success', 'Letter number created successfully: ' . $letterNumber->letter_number);
    }

    public function show($id)
    {
        $letterNumber = LetterNumber::with([
            'category',
            'subject',
            'administration.employee',
            'administration.project',
            'project',
            'reservedBy',
            'usedBy'
        ])
            ->findOrFail($id);

        $title = 'Letter Number Details';
        $relatedDocument = $letterNumber->relatedDocument();

        return view('letter-numbers.show', compact('title', 'letterNumber', 'relatedDocument'));
    }

    public function edit($id)
    {
        $letterNumber = LetterNumber::findOrFail($id);

        // Hanya bisa edit jika status masih reserved
        if ($letterNumber->status !== 'reserved') {
            return redirect()->route('letter-numbers.index')
                ->with('toast_error', 'Letter number cannot be edited because it has been used or cancelled');
        }

        $title = 'Edit Letter Number';
        $categories = LetterCategory::where('is_active', 1)->orderBy('category_code', 'asc')->get();
        $administrations = Administration::with(['employee', 'project', 'position'])
            ->active()
            ->orderBy('nik')
            ->get();
        $projects = Project::orderBy('project_name')->get();
        $subjects = LetterSubject::where('letter_category_id', $letterNumber->letter_category_id)
            ->where('is_active', 1)
            ->get();

        return view('letter-numbers.edit', compact(
            'title',
            'letterNumber',
            'categories',
            'subjects',
            'administrations',
            'projects'
        ));
    }

    public function update(Request $request, $id)
    {
        $letterNumber = LetterNumber::findOrFail($id);

        // Hanya bisa update jika status masih reserved
        if ($letterNumber->status !== 'reserved') {
            return redirect()->route('letter-numbers.index')
                ->with('toast_error', 'Letter number cannot be updated because it has been used or cancelled');
        }

        $rules = [
            'letter_date' => 'required|date',
            'destination' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
        ];

        // Dynamic validation based on category
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            switch ($category->category_code) {
                case 'A':
                case 'B':
                    $rules['classification'] = 'nullable|in:Umum,Lembaga Pendidikan,Pemerintah';
                    break;

                case 'PKWT':
                    // PKWT no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['duration'] = 'nullable';
                    $rules['start_date'] = 'nullable|date';
                    $rules['end_date'] = 'nullable|date|after:start_date';
                    $rules['pkwt_type'] = 'nullable|in:PKWT, PKWTT';
                    break;

                case 'PAR':
                    // PAR no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['par_type'] = 'required|in:new hire,promosi,mutasi,demosi';
                    break;

                case 'CRTE':
                    // CRTE no longer requires administration_id (NIK) - allowing import without NIK
                    break;
                case 'SKPK':
                    // SKPK no longer requires administration_id (NIK)
                    break;
            }
        }

        $request->validate($rules);

        $letterNumber->fill($request->all());
        $letterNumber->save();

        return redirect()->route('letter-numbers.index')
            ->with('toast_success', 'Letter number updated successfully: ' . $letterNumber->letter_number);
    }

    public function destroy($id)
    {
        try {
            $letterNumber = LetterNumber::findOrFail($id);

            // Hanya bisa delete jika status masih reserved dan tidak ada related document
            if ($letterNumber->status !== 'reserved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Letter number cannot be deleted because it has been used or cancelled'
                ], 400);
            }

            if ($letterNumber->related_document_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Letter number cannot be deleted because it is linked to a document'
                ], 400);
            }

            $letterNumber->delete();

            return response()->json([
                'success' => true,
                'message' => 'Letter number deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the letter number'
            ], 500);
        }
    }

    public function cancel($id)
    {
        try {
            $letterNumber = LetterNumber::findOrFail($id);

            // Hanya bisa cancel jika status masih reserved
            if ($letterNumber->status !== 'reserved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Letter number cannot be cancelled because it has been used or already cancelled'
                ], 400);
            }

            $letterNumber->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Letter number cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the letter number'
            ], 500);
        }
    }

    /**
     * API: Get available letter numbers for specific category
     */
    public function getAvailableNumbers($categoryCode)
    {
        try {
            $category = LetterCategory::where('category_code', $categoryCode)->firstOrFail();

            $numbers = LetterNumber::where('letter_category_id', $category->id)
                ->where('status', 'reserved')
                ->with('subject') // Eager load subject
                ->orderBy('letter_date', 'desc')
                ->get();

            // Format data to match what the component expects
            $formattedNumbers = $numbers->map(function ($number) {
                return [
                    'id' => $number->id,
                    'letter_number' => $number->letter_number,
                    'letter_date' => $number->letter_date,
                    'subject_name' => $number->subject->subject_name ?? $number->custom_subject,
                    'remarks' => $number->remarks,
                ];
            });

            return response()->json(['success' => true, 'data' => $formattedNumbers]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => "Category '{$categoryCode}' not found."], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching numbers.'], 500);
        }
    }

    public function getSubjectsForCategory($categoryId)
    {
        $subjects = LetterSubject::where('letter_category_id', $categoryId)
            ->active()
            ->ordered()
            ->get(['id', 'subject_name']);

        return response()->json($subjects);
    }

    public function getNextSequenceNumber($categoryId)
    {
        $nextSequence = LetterNumber::getNextSequenceNumber($categoryId);

        return response()->json(['next_sequence' => $nextSequence]);
    }

    public function markAsUsedManually($id)
    {
        try {
            $letterNumber = LetterNumber::findOrFail($id);

            // Double check conditions to be safe
            if ($letterNumber->status !== 'reserved') {
                return redirect()->route('letter-numbers.show', $id)
                    ->with('toast_error', 'Failed: Letter number is not in reserved status.');
            }

            if ($letterNumber->related_document_id) {
                return redirect()->route('letter-numbers.show', $id)
                    ->with('toast_error', 'Failed: Letter number is already linked to a document.');
            }

            $letterNumber->status = 'used';
            $letterNumber->used_at = now();
            $letterNumber->used_by = auth()->id();
            $letterNumber->save();

            return redirect()->route('letter-numbers.show', $id)
                ->with('toast_success', 'Letter number has been manually marked as used.');
        } catch (\Exception $e) {
            return redirect()->route('letter-numbers.show', $id)
                ->with('toast_error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new LetterAdministrationExport, 'letter-numbers-' . date('Y-m-d') . '.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xls,xlsx'
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes'    => 'The file must be a file of type: xls, xlsx.'
        ]);

        try {
            $import = new LetterAdministrationImport();
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();

            if ($failures->isNotEmpty()) {
                $formattedFailures = collect();
                foreach ($failures as $failure) {
                    $formattedFailures->push([
                        'sheet'     => 'Letter Import',
                        'row'       => $failure->row(),
                        'attribute' => $failure->attribute(),
                        'value'     => $failure->values()[$failure->attribute()] ?? null,
                        'errors'    => implode(', ', $failure->errors()),
                    ]);
                }
                return back()->with('failures', $formattedFailures);
            }

            return redirect()->route('letter-numbers.index')->with('toast_success', 'Data imported successfully!');
        } catch (ValidationException $e) {
            $failures = collect();
            foreach ($e->failures() as $failure) {
                $failures->push([
                    'sheet'     => 'Letter Import',
                    'row'       => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'value'     => $failure->values()[$failure->attribute()] ?? null,
                    'errors'    => implode(', ', $failure->errors()),
                ]);
            }
            return back()->with('failures', $failures);
        } catch (\Throwable $e) {
            $failures = collect([
                [
                    'sheet'     => 'System Error',
                    'row'       => '-',
                    'attribute' => 'General Error',
                    'value'     => null,
                    'errors'    => 'An error occurred during import: ' . $e->getMessage()
                ]
            ]);
            return back()->with('failures', $failures);
        }
    }
}
