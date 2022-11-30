<?php

namespace App\Http\Controllers;

use App\Models\Religion;
use Illuminate\Http\Request;

class ReligionController extends Controller
{
    public function index()
    {
        $title = 'Religions';
        $subtitle = 'List of Religion';
        return view('religion.index', compact('title', 'subtitle'));
    }

    public function getReligions(Request $request)
    {
        $religions = Religion::orderBy('id', 'asc');

        return datatables()->of($religions)
            ->addIndexColumn()
            ->addColumn('religion_name', function ($religions) {
                return $religions->religion_name;
            })
            ->addColumn('religion_status', function ($religions) {
                if ($religions->religion_status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($religions->religion_status == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('religion_name', 'LIKE', "%$search%")
                            ->orWhere('religion_status', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'religion.action')
            ->rawColumns(['religion_status', 'action'])
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
        $validatedData = $request->validate([
            'religion_name' => 'required',
            'religion_status' => 'required',
        ], [
            'religion_name.required' => 'Religion Name is required'
        ]);

        Religion::create($validatedData);

        return redirect('religions')->with('toast_success', 'Religion added successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'religion_name' => 'required'
        ], [
            'religion_name.required' => 'Religion Name is required',
        ]);

        $religions = Religion::find($id);
        $religions->religion_name = $request->religion_name;
        $religions->religion_status = $request->religion_status;
        $religions->save();

        return redirect('religions')->with('toast_success', 'Religion edited successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $religion = Religion::where('id', $id)->first();
        $religion->delete();
        return redirect('religions')->with('toast_success', 'Religion delete successfully');
    }
}
