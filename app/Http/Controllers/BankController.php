<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index()
    {
        $title = 'Bank';
        $subtitle = 'List of Banks';
        return view('bank.index', compact('title', 'subtitle'));
    }

    public function getBanks(Request $request)
    {
        $banks = Bank::orderBy('bank_name', 'asc');

        return datatables()->of($banks)
            ->addIndexColumn()
            ->addColumn('bank_name', function ($banks) {
                return $banks->bank_name;
            })
            ->addColumn('bank_status', function ($banks) {
                if ($banks->bank_status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($banks->bank_status == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('bank_name', 'LIKE', "%$search%")
                            ->orWhere('bank_status', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'bank.action')
            ->rawColumns(['bank_status', 'action'])
            ->toJson();
    }

    public function addBanks()
    {
        return view('bank.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'bank_name' => 'required',
            'bank_status' => 'required',
        ], [
            'bank_name.required' => 'Bank Name is required'
        ]);

        Bank::create($validatedData);

        return redirect('banks')->with('toast_success', 'Bank added successfully!');
    }

    public function edit($slug)
    {
        $banks = Bank::where('slug', $slug)->first();
        return view('bank.edit', compact('banks'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'bank_name' => 'required'
        ], [
            'bank_name.required' => 'Bank Name is required',
        ]);

        $banks = Bank::find($id);
        $banks->bank_name = $request->bank_name;
        $banks->bank_status = $request->bank_status;
        $banks->save();

        return redirect('banks')->with('toast_success', 'Bank edited successfully');
    }

    public function destroy($id)
    {
        $banks = Bank::where('id', $id)->first();
        $banks->delete();
        return redirect('banks')->with('toast_success', 'Bank delete successfully');
    }
}
