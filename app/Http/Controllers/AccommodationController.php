<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccommodationController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master-data.show')->only(['index', 'show']);
        $this->middleware('permission:master-data.create')->only('create');
        $this->middleware('permission:master-data.edit')->only('edit');
        $this->middleware('permission:master-data.delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Accommodations';
        $subtitle = 'List of Accommodations';

        return view('accommodations.index', compact('title', 'subtitle'));
    }

    public function getAccommodations(Request $request)
    {
        $accommodations = Accommodation::orderBy('accommodation_name', 'asc');

        return datatables()->of($accommodations)
            ->addIndexColumn()
            ->addColumn('accommodation_name', function ($accommodation) {
                return $accommodation->accommodation_name;
            })
            ->addColumn('accommodation_status', function ($accommodation) {
                return $accommodation->accommodation_status;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('accommodation_name', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($model) {
                return view('accommodations.action', compact('model'))->render();
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Accommodations';
        $subtitle = 'Add Accommodation';

        return view('accommodations.create', compact('title', 'subtitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'accommodation_name' => 'required|unique:accommodations,accommodation_name',
            ], [
                'accommodation_name.required' => 'Name is required',
                'accommodation_name.unique' => 'Name already exists',
            ]);

            DB::beginTransaction();

            $accommodation = Accommodation::create($request->all());

            DB::commit();

            return redirect('accommodations')->with('toast_success', 'Accommodation added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to add accommodation. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Accommodation  $accommodation
     * @return \Illuminate\Http\Response
     */
    public function show(Accommodation $accommodation)
    {
        $title = 'Accommodations';
        $subtitle = 'Accommodation Details';

        return view('accommodations.show', compact('title', 'subtitle', 'accommodation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Accommodation  $accommodation
     * @return \Illuminate\Http\Response
     */
    public function edit(Accommodation $accommodation)
    {
        $title = 'Accommodations';
        $subtitle = 'Edit Accommodation';

        return view('accommodations.edit', compact('title', 'subtitle', 'accommodation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Accommodation  $accommodation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Accommodation $accommodation)
    {
        try {
            $this->validate($request, [
                'accommodation_name' => 'required|unique:accommodations,accommodation_name,' . $accommodation->id,
            ], [
                'accommodation_name.required' => 'Name is required',
                'accommodation_name.unique' => 'Name already exists',
            ]);

            DB::beginTransaction();

            $accommodation->update($request->all());

            DB::commit();

            return redirect('accommodations')->with('toast_success', 'Accommodation updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Accommodation not found.')
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update accommodation. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Accommodation  $accommodation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Accommodation $accommodation)
    {
        try {
            DB::beginTransaction();

            $accommodation->delete();

            DB::commit();

            return redirect('accommodations')->with('toast_success', 'Accommodation deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Accommodation not found.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to delete accommodation. Please try again.');
        }
    }
}
