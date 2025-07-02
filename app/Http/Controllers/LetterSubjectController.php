<?php

namespace App\Http\Controllers;

use App\Models\LetterSubject;
use App\Models\LetterCategory;
use Illuminate\Http\Request;

class LetterSubjectController extends Controller
{
    /**
     * Get subjects by category code
     */
    public function getByCategory($categoryCode)
    {
        try {
            $subjects = LetterSubject::where('category_code', $categoryCode)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get(['id', 'subject_name']);

            return response()->json($subjects);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load subjects',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available subjects for specific document type and category
     */
    public function getAvailableSubjectsForDocument($documentType, $categoryCode)
    {
        try {
            $subjects = LetterSubject::where('category_code', $categoryCode)
                ->where('is_active', 1)
                ->orderBy('subject_name')
                ->get(['id', 'subject_name']);

            return response()->json([
                'success' => true,
                'data' => $subjects,
                'count' => $subjects->count(),
                'document_type' => $documentType,
                'category_code' => $categoryCode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load subjects',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $title = 'Master Perihal Surat';
        $subtitle = 'Daftar Perihal Surat';

        return view('letter-subjects.index', compact('title', 'subtitle'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $title = 'Tambah Perihal Surat';

        return view('letter-subjects.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'category_code' => 'required|exists:letter_categories,category_code',
            'is_active' => 'required|boolean',
        ], [
            'subject_name.required' => 'Nama subject harus diisi',
            'subject_name.max' => 'Nama subject maksimal 255 karakter',
            'category_code.required' => 'Category code harus diisi',
            'category_code.exists' => 'Category code tidak valid',
            'is_active.required' => 'Status aktif harus diisi',
            'is_active.boolean' => 'Status aktif harus berupa true/false',
        ]);

        try {
            LetterSubject::create([
                'subject_name' => $request->subject_name,
                'category_code' => $request->category_code,
                'is_active' => $request->is_active ?? 1,
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('letter-subjects.index')
                ->with('toast_success', 'Perihal surat berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Gagal menambahkan perihal surat: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LetterSubject $letterSubject)
    {
        $title = 'Detail Perihal Surat';

        return view('letter-subjects.show', compact('title', 'letterSubject'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LetterSubject $letterSubject)
    {
        $title = 'Edit Perihal Surat';

        return view('letter-subjects.edit', compact('title', 'letterSubject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LetterSubject $letterSubject)
    {
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'category_code' => 'required|exists:letter_categories,category_code',
            'is_active' => 'required|boolean',
        ], [
            'subject_name.required' => 'Nama subject harus diisi',
            'subject_name.max' => 'Nama subject maksimal 255 karakter',
            'category_code.required' => 'Category code harus diisi',
            'category_code.exists' => 'Category code tidak valid',
            'is_active.required' => 'Status aktif harus diisi',
            'is_active.boolean' => 'Status aktif harus berupa true/false',
        ]);

        try {
            $letterSubject->update([
                'subject_name' => $request->subject_name,
                'category_code' => $request->category_code,
                'is_active' => $request->is_active ?? 1,
            ]);

            return redirect()->route('letter-subjects.index')
                ->with('toast_success', 'Perihal surat berhasil diupdate');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Gagal mengupdate perihal surat: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $letterSubject = LetterSubject::findOrFail($id);

            // Check if subject is being used
            if ($letterSubject->letterNumbers()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete subject because it is still being used in letter numbers'
                ], 400);
            }

            $letterSubject->delete();

            return response()->json([
                'success' => true,
                'message' => 'Letter subject deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete letter subject: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display subjects for specific category
     */
    public function indexByCategory($categoryCode)
    {
        $category = LetterCategory::where('category_code', $categoryCode)->firstOrFail();
        $title = 'Letter Subjects - ' . $category->category_name;
        $subtitle = 'Manage subjects for ' . $category->category_name . ' (' . $categoryCode . ')';

        return view('letter-subjects.index-by-category', compact('title', 'subtitle', 'category'));
    }

    /**
     * Get subjects data for DataTables (category specific)
     */
    public function getSubjectsByCategory(Request $request, $categoryCode)
    {
        $subjects = LetterSubject::where('category_code', $categoryCode)
            ->with('user')
            ->orderBy('subject_name', 'asc');

        return datatables()->of($subjects)
            ->addIndexColumn()
            ->addColumn('subject_name', function ($subject) {
                return $subject->subject_name;
            })
            ->addColumn('is_active', function ($subject) {
                if ($subject->is_active == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } else {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->addColumn('created_by', function ($subject) {
                return $subject->user ? $subject->user->name : '-';
            })
            ->addColumn('created_at', function ($subject) {
                return $subject->created_at ? $subject->created_at->format('d/m/Y H:i') : '-';
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('subject_name', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($subject) {
                return view('letter-subjects.action-by-category', compact('subject'))->render();
            })
            ->rawColumns(['is_active', 'action'])
            ->toJson();
    }

    /**
     * Store subject for specific category
     */
    public function storeByCategory(Request $request, $categoryCode)
    {
        // Simplified validation rules for store (without user_id requirement)
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ], [
            'subject_name.required' => 'Nama subject harus diisi',
            'subject_name.max' => 'Nama subject maksimal 255 karakter',
            'is_active.required' => 'Status aktif harus diisi',
            'is_active.boolean' => 'Status aktif harus berupa true/false',
        ]);

        try {
            LetterSubject::create([
                'subject_name' => $request->subject_name,
                'category_code' => $categoryCode,
                'is_active' => $request->is_active,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Letter subject added successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add letter subject: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update subject for specific category
     */
    public function updateByCategory(Request $request, $categoryCode, $id)
    {
        // Simplified validation rules for update (without user_id requirement)
        $request->validate([
            'subject_name' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ], [
            'subject_name.required' => 'Nama subject harus diisi',
            'subject_name.max' => 'Nama subject maksimal 255 karakter',
            'is_active.required' => 'Status aktif harus diisi',
            'is_active.boolean' => 'Status aktif harus berupa true/false',
        ]);

        try {
            $subject = LetterSubject::where('category_code', $categoryCode)->findOrFail($id);

            $subject->update([
                'subject_name' => $request->subject_name,
                'is_active' => $request->is_active,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Letter subject updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update letter subject: ' . $e->getMessage()
            ], 500);
        }
    }
}
