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

    /**
     * Check if user has access to project 000H
     */
    private function hasAccessToProject000H()
    {
        return auth()->user()->projects()
            ->where('project_status', 1)
            ->where('project_code', '000H')
            ->exists();
    }

    /**
     * Get filtered letter categories based on user access
     */
    private function getFilteredCategories()
    {
        $query = LetterCategory::where('is_active', 1);

        // If user doesn't have access to project 000H, exclude restricted categories
        if (!$this->hasAccessToProject000H()) {
            $query->whereNotIn('category_code', ['CRTE', 'PKWT', 'SKPK']);
        }

        return $query->orderBy('category_code', 'asc')->get();
    }

    /**
     * Validate category access based on user permissions
     */
    private function validateCategoryAccess($categoryCode)
    {
        if (in_array($categoryCode, ['CRTE', 'PKWT', 'SKPK']) && !$this->hasAccessToProject000H()) {
            abort(403, 'You do not have permission to use this letter category.');
        }
    }

    public function index()
    {
        $title = 'Letter Number Administration';
        $subtitle = 'Letter Numbers List';
        $categories = $this->getFilteredCategories();
        $projects = auth()->user()->projects()->where('project_status', 1)->orderBy('project_code', 'asc')->get();

        return view('letter-numbers.index', compact('title', 'subtitle', 'categories', 'projects'));
    }

    public function getLetterNumbers(Request $request)
    {
        // Get user's accessible project IDs
        $userProjects = auth()->user()->projects()->where('project_status', 1)->pluck('projects.id')->toArray();

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
            ->whereIn('project_id', $userProjects)
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
            ->when($request->project_id, function ($query, $projectId) {
                return $query->where('project_id', $projectId);
            })
            ->orderBy('id', 'desc');

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
            ->addColumn('project_display', function ($row) {
                // Ambil project_code langsung dari letter_numbers.project_id
                if ($row->project) {
                    return $row->project->project_code;
                }
                return $row->project_code ?? '-';
            })
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
        $categories = $this->getFilteredCategories();

        // Menggunakan administrations aktif untuk dropdown karyawan
        $administrations = Administration::with(['employee', 'project', 'position'])
            ->active()
            ->orderBy('nik')
            ->get();
        $projects = auth()->user()->projects()->where('project_status', 1)->orderBy('project_code', 'asc')->get();

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
        // Since numbering is per project, we'll show for first project as default preview
        // User can see actual preview when they select a project
        $defaultProjectId = $projects->first() ? $projects->first()->id : null;
        $estimatedNextNumbers = $defaultProjectId
            ? LetterNumber::getEstimatedNextNumbersForAllCategories(null, [$defaultProjectId])
            : [];

        // Get last numbers for each category for context (for default project)
        $lastNumbersByCategory = [];
        $letterCountsByCategory = [];
        foreach ($categories as $category) {
            $lastNumbersByCategory[$category->id] = $defaultProjectId
                ? LetterNumber::getLastNumbersForCategory($category->id, 3, null, $defaultProjectId, null)
                : collect();
            $letterCountsByCategory[$category->id] = $defaultProjectId
                ? LetterNumber::getLetterCountForCategory($category->id, null, $defaultProjectId, null)
                : 0;
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
            'project_id' => 'required|exists:projects,id',
        ];

        // Validate category access based on user permissions
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            $this->validateCategoryAccess($category->category_code);
        }

        // Dynamic validation based on category
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

        // Set project_id based on category BEFORE fill()
        // Categories with employee-template (PKWT, PAR, CRTE, SKPK): ALWAYS use project from request (select project), NEVER from employee
        // Other categories: priority from administration, then from request
        $categoriesWithEmployeeTemplate = ['PKWT', 'PAR', 'CRTE', 'SKPK'];
        if ($category && in_array($category->category_code, $categoriesWithEmployeeTemplate)) {
            // Categories with employee-template: ONLY use project from request->project_id (select project)
            // Completely ignore administration project_id for these categories
            $letterNumber->project_id = $request->project_id;
        } else {
            // Other categories: priority from administration, then from request
            if ($request->administration_id) {
                $administration = Administration::find($request->administration_id);
                if ($administration && $administration->project_id) {
                    $letterNumber->project_id = $administration->project_id;
                }
            }
            // If project_id not set from administration, use from request (or null)
            if (!$letterNumber->project_id && $request->project_id) {
                $letterNumber->project_id = $request->project_id;
            }
        }

        // Fill other fields (project_id already set above, so won't be overridden)
        $letterNumber->fill($request->all());

        // Ensure categories with employee-template project_id is NOT overridden by fill()
        if ($category && in_array($category->category_code, $categoriesWithEmployeeTemplate)) {
            $letterNumber->project_id = $request->project_id;
        }

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
        $categories = $this->getFilteredCategories();
        $administrations = Administration::with(['employee', 'project', 'position'])
            ->active()
            ->orderBy('nik')
            ->get();
        $projects = auth()->user()->projects()->where('project_status', 1)->orderBy('project_code', 'asc')->get();
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
            'project_code' => 'nullable|string|max:50',
            'project_id' => 'required|exists:projects,id',
        ];

        // Validate category access based on user permissions
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            $this->validateCategoryAccess($category->category_code);
        }

        // Dynamic validation based on category
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

        // Set project_id based on category BEFORE fill()
        // Categories with employee-template (PKWT, PAR, CRTE, SKPK): ALWAYS use project from request (select project), NEVER from employee
        // Other categories: priority from administration, then from request
        $categoriesWithEmployeeTemplate = ['PKWT', 'PAR', 'CRTE', 'SKPK'];
        if ($category && in_array($category->category_code, $categoriesWithEmployeeTemplate)) {
            // Categories with employee-template: ONLY use project from request->project_id (select project)
            // Completely ignore administration project_id for these categories
            $letterNumber->project_id = $request->project_id;
        } else {
            // Other categories: priority from administration, then from request
            if ($request->administration_id) {
                $administration = Administration::find($request->administration_id);
                if ($administration && $administration->project_id) {
                    $letterNumber->project_id = $administration->project_id;
                }
            }
            // If project_id not set from administration, use from request (or keep existing)
            if (!$letterNumber->project_id && $request->project_id) {
                $letterNumber->project_id = $request->project_id;
            }
        }

        // Fill other fields (project_id already set above, so won't be overridden)
        $letterNumber->fill($request->all());

        // Ensure categories with employee-template project_id is NOT overridden by fill()
        if ($category && in_array($category->category_code, $categoriesWithEmployeeTemplate)) {
            $letterNumber->project_id = $request->project_id;
        }

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
                    /** @var \Maatwebsite\Excel\Validators\Failure $failure */
                    $values = $failure->values();
                    $attribute = $failure->attribute();
                    $value = is_array($values) && array_key_exists($attribute, $values) ? $values[$attribute] : null;

                    // For project-related errors, use project_code as attribute and show value
                    $displayValue = $value;
                    $displayAttribute = ucwords(str_replace('_', ' ', $attribute));

                    if (in_array($attribute, ['project_code', 'project_id', 'project'])) {
                        // Always use project_code as attribute name
                        $displayAttribute = 'project_code';
                        $projectCode = is_array($values) && array_key_exists('project_code', $values) ? $values['project_code'] : null;
                        $projectId = is_array($values) && array_key_exists('project_id', $values) ? $values['project_id'] : null;

                        // Show value, or empty if both are empty
                        if ($projectCode) {
                            $displayValue = $projectCode;
                        } elseif ($projectId) {
                            $displayValue = $projectId;
                        } else {
                            $displayValue = ''; // Empty if not provided
                        }
                    }

                    $formattedFailures->push([
                        'sheet'     => 'Letter Import',
                        'row'       => $failure->row(),
                        'attribute' => $displayAttribute,
                        'value'     => $displayValue,
                        'errors'    => implode(', ', $failure->errors()),
                    ]);
                }
                return back()->with('failures', $formattedFailures);
            }

            return redirect()->route('letter-numbers.index')->with('toast_success', 'Data imported successfully!');
        } catch (ValidationException $e) {
            $failures = collect();
            foreach ($e->failures() as $failure) {
                /** @var \Maatwebsite\Excel\Validators\Failure $failure */
                $values = $failure->values();
                $attribute = $failure->attribute();
                $value = is_array($values) && array_key_exists($attribute, $values) ? $values[$attribute] : null;

                // For project-related errors, use project_code as attribute and show value
                $displayValue = $value;
                $displayAttribute = ucwords(str_replace('_', ' ', $attribute));

                if (in_array($attribute, ['project_code', 'project_id', 'project'])) {
                    // Always use project_code as attribute name
                    $displayAttribute = 'project_code';
                    $projectCode = is_array($values) && array_key_exists('project_code', $values) ? $values['project_code'] : null;
                    $projectId = is_array($values) && array_key_exists('project_id', $values) ? $values['project_id'] : null;

                    // Show value, or empty if both are empty
                    if ($projectCode) {
                        $displayValue = $projectCode;
                    } elseif ($projectId) {
                        $displayValue = $projectId;
                    } else {
                        $displayValue = ''; // Empty if not provided
                    }
                }

                $failures->push([
                    'sheet'     => 'Letter Import',
                    'row'       => $failure->row(),
                    'attribute' => $displayAttribute,
                    'value'     => $displayValue,
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
