<?php

namespace App\Http\Controllers;

use App\Models\Transportation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransportationController extends Controller
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
        $title = 'Transportations';
        $subtitle = 'List of Transportations';

        return view('transportations.index', compact('title', 'subtitle'));
    }

    public function getTransportations(Request $request)
    {
        $transportations = Transportation::orderBy('transportation_name', 'asc');

        return datatables()->of($transportations)
            ->addIndexColumn()
            ->addColumn('transportation_name', function ($transportation) {
                return $transportation->transportation_name;
            })
            ->addColumn('transportation_status', function ($transportation) {
                return $transportation->transportation_status;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('transportation_name', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($model) {
                return view('transportations.action', compact('model'))->render();
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
        //
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
                'transportation_name' => 'required|unique:transportations,transportation_name',
                // Add more validation rules as needed
            ], [
                'transportation_name.required' => 'Name is required',
                'transportation_name.unique' => 'Name already exists',
                // Add more custom messages as needed
            ]);

            DB::beginTransaction();

            $transportation = Transportation::create($request->all());

            DB::commit();

            return redirect('transportations')->with('toast_success', 'Transportation added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to add transportation. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Transportation  $transportation
     * @return \Illuminate\Http\Response
     */
    public function show(Transportation $transportation)
    {
        $title = 'Transportations';
        $subtitle = 'Transportation Details';

        return view('transportations.show', compact('title', 'subtitle', 'transportation'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transportation  $transportation
     * @return \Illuminate\Http\Response
     */
    public function edit(Transportation $transportation)
    {
        $title = 'Transportations';
        $subtitle = 'Edit Transportation';

        return view('transportations.edit', compact('title', 'subtitle', 'transportation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transportation  $transportation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transportation $transportation)
    {
        try {
            $this->validate($request, [
                'transportation_name' => 'required|unique:transportations,transportation_name,' . $transportation->id,
                // Add more validation rules as needed
            ], [
                'transportation_name.required' => 'Name is required',
                'transportation_name.unique' => 'Name already exists',
                // Add more custom messages as needed
            ]);

            DB::beginTransaction();

            $transportation->update($request->all());

            DB::commit();

            return redirect('transportations')->with('toast_success', 'Transportation updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Transportation not found.')
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update transportation. Please try again.')
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transportation  $transportation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Transportation $transportation)
    {
        try {
            DB::beginTransaction();

            $transportation->delete();

            DB::commit();

            return redirect('transportations')->with('toast_success', 'Transportation deleted successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Transportation not found.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to delete transportation. Please try again.');
        }
    }
}
