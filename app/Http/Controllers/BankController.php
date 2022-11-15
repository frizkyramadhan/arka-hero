<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function banks(Request $request)
    {
        $keyword = $request->keyword;



        $banks = Bank::where('bank_name', 'LIKE', '%'.$keyword.'%')
                    ->paginate(5);
        return view('bank.index', ['banks' => $banks]);
        
    }

    public function addBanks()
    {
        return view('bank.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|unique:banks|',
            
        ]);
        $banks = Bank::create($request->all());
        return redirect('admin/banks')->with('status', 'Name Bank Add Successfully');
    }

    public function editBanks($slug)
    {
        $banks = Bank::where('slug', $slug)->first();
        return view('bank.edit', compact('banks'));
    }

    public function updateBanks(Request $request, $slug)
    {
        $validated = $request->validate([
            'bank_name' => 'required',
            

        ]);

        $banks = Bank::where('slug', $slug)->first();
        $banks->slug = null;
        $banks->update($request->all());
        return redirect('admin/banks')->with('status', 'Bank Name Edit Successfully');
    }

    public function deleteBanks($slug)
    {

        $banks = Bank::where('slug', $slug)->first();
        $banks->delete();
        return redirect('admin/banks')->with('status', 'Bank Name Delete Successfully');
    }
}
