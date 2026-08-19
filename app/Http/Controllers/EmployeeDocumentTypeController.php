<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocumentType;
use Illuminate\Http\Request;

class EmployeeDocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('employee_document.type.index', [
            'types' => EmployeeDocumentType::ordered()->withCount('documents')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employee_document.type.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'expiry_warning_days' => 'nullable|integer|min:1|max:3650',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'custom_fields.*.label' => 'nullable|string|max:255',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'has_expiry' => $request->boolean('has_expiry'),
            'expiry_warning_days' => $request->expiry_warning_days ?: 30,
            'requires_attachment' => $request->boolean('requires_attachment'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $request->sort_order ?: 0,
            'custom_fields' => EmployeeDocumentType::normaliseCustomFields($request->input('custom_fields', [])),
        ];

        $type = EmployeeDocumentType::find($request->employee_document_type_id);

        if ($type) {
            $type->update($data);
            $message = 'Information type updated.';
        } else {
            EmployeeDocumentType::create($data);
            $message = 'Information type created.';
        }

        return redirect()->route('employee_document_type.index')->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeDocumentType $employee_document_type)
    {
        return view('employee_document.type.create')->with([
            'type' => $employee_document_type,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeDocumentType $employee_document_type)
    {
        if ($employee_document_type->documents()->count() > 0) {
            return redirect()->route('employee_document_type.index')->with([
                'error' => 'Cannot delete "' . $employee_document_type->name . '". There are employee records using it. Set it to inactive instead.',
            ]);
        }

        $employee_document_type->delete();

        return redirect()->route('employee_document_type.index')->with('success', 'Information type deleted.');
    }
}
