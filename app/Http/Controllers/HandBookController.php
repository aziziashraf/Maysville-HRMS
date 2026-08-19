<?php

namespace App\Http\Controllers;

use App\Models\HandBook;
use App\Models\HandBookCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class HandBookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $user = Auth()->user();

        // $handbook = HandBook::orderBy('handbookcategory_id', 'asc')
        //     ->orderBy('index_number', 'asc')
        //     ->get();

        // return view('handbook.index', [
        //     'handbook' => $handbook,
        // ]);

        return view('handbook.index', [
            'handbook_category' => HandBookCategory::orderBy('index_number', 'asc')->get(),
        ]);

    }

    public function indexUser()
    {
        $handbookCategories = HandbookCategory::where('status', 1)->get();
    
        $categories = [];
    
        foreach ($handbookCategories as $category) {
            $handbooks = $category->handbook()->where('status', 1)->orderBy('index_number', 'asc')->get();
    
            if ($handbooks->isNotEmpty()) {
                $categories[$category->name] = [
                    'index' => $category->index_number,
                    'items' => [],
                ];
    
                foreach ($handbooks as $handbook) {
                    $categories[$category->name]['items'][] = [
                        'item' => $handbook,
                        'index' => $handbook->index_number,
                    ];
                }
            }
        }
    
        uasort($categories, function ($a, $b) {
            return (int)$a['index'] - (int)$b['index'];
        });
    
        foreach ($categories as &$category) {
            usort($category['items'], function ($a, $b) {
                return (int)$a['index'] - (int)$b['index'];
            });
        }
    
        return view('handbook.indexUser', [
            'categories' => $categories,
        ]);
    }
    
    

    /**
     * Show the form for creating a new resource.
     */
    public function create(HandBookCategory $handbook_category, HandBook $handbook)
    {
        // return view('handbook.create')->with([
        //     'handbook_category' => HandBookCategory::all(),
        // ]);
        return view('handbook.create_item')->with([
            'handbook' => $handbook_category,
            // 'handbook_category' => HandBookCategory::all(),

            'handbookField' => $handbook,
            'handbook_category' => HandBookCategory::all(),
            'content' => $handbook->content,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'handbookcategory_id'        => 'required',
            'name'                       => 'required',
            'description'                => 'required',
            'status'                     => 'required',
            'content'                    => 'required',
            'index_number' => [
                'required',
                Rule::unique('hand_books')->where(function ($query) use ($request) {
                    return $query->where('index_number', $request->input('index_number'))
                        ->where('handbookcategory_id', $request->input('handbookcategory_id'))
                        ->where('deleted_at', NULL)
                        ->where('id', '!=', $request->input('id'));
                })->ignore($request->input('handbook_id'), 'id'),
                'regex:/^[a-zA-Z]$/',
            ],
        ], [
            'index_number.unique' => 'The index number is already taken for this handbook category.',
            'index_number.regex'  => 'The index number should contain only alphabetic characters (a-z).',
        ]);
        
        $handbook = HandBook::find($request->handbook_id);
        if ($handbook) {
            $handbook->update($request->all());

        } else {
            $handbook = HandBook::create($request->all());
        }
        return redirect()->route('handbook.edit', $handbook->handbookCategory->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(HandBook $handbook)
    {
        return view('handbook.create')->with([
            'handbook' => $handbook,
            'handbook_category' => HandBookCategory::all(),
            'content' => $handbook->content,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(HandBookCategory $handbook)
    {
        // return view('handbook.create')->with([
        //     'handbook' => $handbook,
        //     'handbook_category' => HandBookCategory::all(),
        //     'content' => $handbook->content,
        // ]);

        return view('handbook.create')->with([
            'handbook_category' => $handbook,
        ]);

        // $handbook_categories = HandBookCategory::where('id', '!=', $handbook_category->id)->get();
        // return view('handbook.create')->with(['handbook_category' => $handbook_category, 'handbook_categories' => $handbook_categories ]);
    }

    public function editHandbookItem(HandBook $handbook)
    {
        // return view('handbook.create_item')->with([
        //     'handbook'  => $handbook,
        //     'handbook_category' => HandBookCategory::all(),
        //     'content' => $handbook->content,
        // ]);

        return view('handbook.create_item')->with([
            'handbook_category' => $handbook,
            
            'handbookField' => $handbook,
            'handbook_category' => HandBookCategory::all(),
            'content' => $handbook->content,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HandBook $handbook_category)
    {
        
    }

    public function getHandbookContent($id)
    {
        $handbook = HandBook::find($id);
    
        if ($handbook) {
            return response()->json(['content' => $handbook->content]);
        }
    
        return response()->json(['error' => 'Handbook not found'], 404);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(HandBook $handbook)
    {
        $handbook->delete();
        return redirect()->route('handbook.index');
    }
}
