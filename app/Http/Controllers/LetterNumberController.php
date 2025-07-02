<?php

namespace App\Http\Controllers;

use App\Models\LetterNumber;
use App\Models\LetterCategory;
use App\Models\LetterSubject;
use App\Models\Administration;
use App\Models\Project;
use Illuminate\Http\Request;

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
        $categories = LetterCategory::where('is_active', 1)->get();

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
            ->when($request->category_code, function ($query, $category) {
                return $query->where('category_code', $category);
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
        $categories = LetterCategory::where('is_active', 1)->get();

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
            $subjects = LetterSubject::where('category_code', $categoryCode)
                ->where('is_active', 1)
                ->get();
        }

        return view('letter-numbers.create', compact(
            'title',
            'categories',
            'subjects',
            'administrations',
            'projects',
            'selectedCategory'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'category_code' => 'required|exists:letter_categories,category_code',
            'letter_date' => 'required|date',
            'destination' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
        ];

        // Dynamic validation based on category
        switch ($request->category_code) {
            case 'A':
            case 'B':
                $rules['classification'] = 'nullable|in:Umum,Lembaga Pendidikan,Pemerintah';
                break;

            case 'PKWT':
                $rules['administration_id'] = 'required|exists:administrations,id';
                $rules['duration'] = 'required|string';
                $rules['start_date'] = 'required|date';
                $rules['end_date'] = 'required|date|after:start_date';
                $rules['pkwt_type'] = 'required|in:PKWT I,PKWT II,PKWT III';
                break;

            case 'PAR':
                $rules['administration_id'] = 'required|exists:administrations,id';
                $rules['par_type'] = 'required|in:new hire,promosi,mutasi,demosi';
                break;

            case 'CRTE':
            case 'SKPK':
                $rules['administration_id'] = 'required|exists:administrations,id';
                break;

            case 'FR':
                $rules['ticket_classification'] = 'required|in:Pesawat,Kereta Api,Bus';
                break;
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
        $categories = LetterCategory::where('is_active', 1)->get();
        $administrations = Administration::with(['employee', 'project', 'position'])
            ->active()
            ->orderBy('nik')
            ->get();
        $projects = Project::orderBy('project_name')->get();
        $subjects = LetterSubject::where('category_code', $letterNumber->category_code)
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

        // Dynamic validation berdasarkan kategori yang sudah ada
        switch ($letterNumber->category_code) {
            case 'PKWT':
                $rules['administration_id'] = 'required|exists:administrations,id';
                $rules['duration'] = 'required|string';
                $rules['start_date'] = 'required|date';
                $rules['end_date'] = 'required|date|after:start_date';
                $rules['pkwt_type'] = 'required|in:PKWT I,PKWT II,PKWT III';
                break;

            case 'PAR':
                $rules['administration_id'] = 'required|exists:administrations,id';
                $rules['par_type'] = 'required|in:new hire,promosi,mutasi,demosi';
                break;

            case 'CRTE':
            case 'SKPK':
                $rules['administration_id'] = 'required|exists:administrations,id';
                break;
        }

        $request->validate($rules);

        $letterNumber->fill($request->all());
        $letterNumber->save();

        return redirect()->route('letter-numbers.index')
            ->with('toast_success', 'Letter number updated successfully');
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
            $letterNumbers = LetterNumber::with(['subject', 'administration.employee'])
                ->where('category_code', $categoryCode)
                ->where('status', 'reserved')
                ->orderBy('sequence_number', 'desc')
                ->limit(20)
                ->get()
                ->map(function ($number) {
                    return [
                        'id' => $number->id,
                        'letter_number' => $number->letter_number,
                        'subject_name' => $number->subject->subject_name ?? null,
                        'remarks' => $number->remarks ?? null,
                        'letter_date' => $number->letter_date ? $number->letter_date->format('d/m/Y') : null,
                        'employee_name' => $number->administration && $number->administration->employee
                            ? $number->administration->employee->fullname
                            : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $letterNumbers,
                'count' => $letterNumbers->count(),
                'category_code' => $categoryCode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load available letter numbers',
                'message' => $e->getMessage()
            ], 500);
        }
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
}
