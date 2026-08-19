<?php

namespace App\Http\Controllers;

use App\Models\HandBookCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HandBookCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('handbook.handbook_category.index', [
            'handbook_category' => HandBookCategory::orderBy('index_number', 'asc')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('handbook.handbook_category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required',
            'description'   => 'required',
            'status'        => 'required',
            'index_number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('hand_book_categories')->where(function ($query) use ($request) {
                    return $query->where('index_number', $request->input('index_number'))
                        ->where('deleted_at', NULL)
                        ->where('id', '!=', $request->input('handbook_category_id'));
                }),
            ],
        ]);
        
        $handbook_category = HandBookCategory::find($request->handbook_category_id);
        if ($handbook_category) {
            $handbook_category->update($request->all());

        } else {
            $handbook_category = HandBookCategory::create($request->all());
        }
        return redirect()->route('handbook_category.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(HandBookCategory $handbook_category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HandBookCategory $handbook_category)
    {
        return view('handbook.handbook_category.create')->with([
            'handbook_category' => $handbook_category,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HandBookCategory $handbook_category)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HandBookCategory $handbook_category)
    {
        // check if there are any handbook with this handbook_category
        if ($handbook_category->handbook()->count() > 0) {
            return redirect()->route('handbook_category.index')->with([
                'error' => 'Cannot delete this handbook category. There are handbook with this handbook category.',
            ]);
        } else {
            $handbook_category->delete();
            return redirect()->route('handbook_category.index');
        }
    }
}
