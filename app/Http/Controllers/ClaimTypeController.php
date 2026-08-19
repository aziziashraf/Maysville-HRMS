<?php

namespace App\Http\Controllers;

use App\Models\ClaimType;
use Illuminate\Http\Request;

class ClaimTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('claim.claim_type.index', [
            'claim_type' => ClaimType::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('claim.claim_type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'unit' => 'required',
            'unit_price' => 'required',
            'unit_price_value' => 'required_if:unit_price,1',
        ]);
        
        $claim_type = ClaimType::find($request->claim_type_id);
        if ($claim_type) {
            $claim_type->update($request->all());

        } else {
            $claim_type = ClaimType::create($request->all());
        }
        return redirect()->route('claim_type.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClaimType $claim_type)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClaimType $claim_type)
    {
        return view('claim.claim_type.create')->with([
            'claim_type' => $claim_type,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClaimType $claim_type)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClaimType $claim_type)
    {
        // check if there are any claims with this claim type
        if ($claim_type->claims()->count() > 0) {
            return redirect()->route('claim_type.index')->with([
                'error' => 'Cannot delete this claim type. There are claims with this claim type.',
            ]);
        } else {
            $claim_type->delete();
            return redirect()->route('claim_type.index');
        }
    }
}
