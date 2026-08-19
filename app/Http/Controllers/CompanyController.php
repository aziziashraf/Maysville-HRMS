<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->query('filter');
		if (!empty($filter)) {
            $company = Company::where('company_name', 'like', '%'.$filter.'%')->get();
        }else{
            $company = Company::all();
        }
        // $company->appends(['filter' => $filter]);

        return view('company.index')->with('company',$company)->with('filter',$filter);
    }

    public function create()
    {
        $company = [];
        return view('company.create')->with('company',$company);
    }

    public function store(Request $request)
    {
        if($request->company_id >0){
            $company=Company::find($request->company_id);
            $company->update($request->all());
        }else{
            Company::create($request->all());
        }
        return redirect()->route('company.index');
    }

    public function edit(Company $company)
    {
        return view('company.create')->with('company',$company);
    }

    public function destroy(Company $company)
    {
        $company->delete();
        return redirect()->back();
    }
}
