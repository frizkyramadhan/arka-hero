<?php

namespace App\Http\Controllers;

use App\Models\LetterCategory;
use Illuminate\Http\Request;

class LetterCategoryController extends Controller
{
    public function index()
    {
        $title = 'Letter Categories';
        $subtitle = 'List of Letter Categories';
        return view('letter-categories.index', compact('title', 'subtitle'));
    }

    public function getLetterCategories(Request $request)
    {
        $categories = LetterCategory::withCount('subjects')->orderBy('category_code', 'asc');

        return datatables()->of($categories)
            ->addIndexColumn()
            ->addColumn('category_code', function ($category) {
                return '<span class="badge badge-primary">' . $category->category_code . '</span>';
            })
            ->addColumn('category_name', function ($category) {
                return $category->category_name;
            })
            ->addColumn('description', function ($category) {
                return $category->description ? $category->description : '-';
            })
            ->addColumn('subjects_count', function ($category) {
                return '<span class="badge badge-info">' . $category->subjects_count . ' subjects</span>';
            })
            ->addColumn('is_active', function ($category) {
                if ($category->is_active == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($category->is_active == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('category_code', 'LIKE', "%$search%")
                            ->orWhere('category_name', 'LIKE', "%$search%")
                            ->orWhere('description', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'letter-categories.action')
            ->rawColumns(['category_code', 'subjects_count', 'is_active', 'action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'category_code' => 'required|unique:letter_categories,category_code|max:10',
            'category_name' => 'required|max:100',
            'description' => 'nullable',
            'is_active' => 'required|boolean',
        ], [
            'category_code.required' => 'Category Code is required',
            'category_code.unique' => 'Category Code already exists',
            'category_name.required' => 'Category Name is required'
        ]);

        $validatedData['user_id'] = auth()->id();

        LetterCategory::create($validatedData);

        return redirect('letter-categories')->with('toast_success', 'Letter Category added successfully!');
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'category_code' => 'required|max:10|unique:letter_categories,category_code,' . $id,
            'category_name' => 'required|max:100',
            'description' => 'nullable',
            'is_active' => 'required|boolean',
        ], [
            'category_code.required' => 'Category Code is required',
            'category_code.unique' => 'Category Code already exists',
            'category_name.required' => 'Category Name is required',
        ]);

        $category = LetterCategory::findOrFail($id);
        $category->category_code = $request->category_code;
        $category->category_name = $request->category_name;
        $category->description = $request->description;
        $category->is_active = $request->is_active;
        $category->save();

        return redirect('letter-categories')->with('toast_success', 'Letter Category updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $category = LetterCategory::findOrFail($id);

            // Check if category has letter numbers
            if ($category->letterNumbers()->count() > 0) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete category that has letter numbers!'
                    ], 400);
                }
                return redirect('letter-categories')->with('toast_error', 'Cannot delete category that has letter numbers!');
            }

            $category->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Letter Category deleted successfully!'
                ]);
            }

            return redirect('letter-categories')->with('toast_success', 'Letter Category deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the category'
                ], 500);
            }
            return redirect('letter-categories')->with('toast_error', 'Failed to delete category');
        }
    }
}
