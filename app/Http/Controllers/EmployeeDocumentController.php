<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Department;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource, filterable by employee, type and
     * expiry status.
     */
    public function index(Request $request)
    {
        $filter = [
            'user_id' => $request->query('user_id'),
            'employee_document_type_id' => $request->query('employee_document_type_id'),
            'status' => $request->query('status'),
            'department_id' => $request->query('department_id'),
        ];

        $query = EmployeeDocument::with(['user.department', 'documentType', 'attachments']);

        if ($filter['user_id']) {
            $query->where('user_id', $filter['user_id']);
        }

        if ($filter['employee_document_type_id']) {
            $query->where('employee_document_type_id', $filter['employee_document_type_id']);
        }

        // Managers limited to their own department only see their own people.
        $user = Auth::user();
        if ($user->can('show-own-department-only')) {
            $query->whereHas('user', fn ($q) => $q->where('department_id', $user->department_id));
        } elseif ($filter['department_id']) {
            $query->whereHas('user', fn ($q) => $q->where('department_id', $filter['department_id']));
        }

        $documents = $query->orderByRaw('expiry_date is null')
            ->orderBy('expiry_date')
            ->get();

        // Expiry status is derived per-type, so filter it after hydration.
        if (in_array($filter['status'], ['expired', 'expiring', 'ok', 'none'], true)) {
            $documents = $documents->filter(fn ($doc) => $doc->expiry_status === $filter['status'])->values();
        }

        return view('employee_document.index', [
            'documents' => $documents,
            'types' => EmployeeDocumentType::ordered()->get(),
            'employees' => $this->selectableEmployees(),
            'departments' => Department::all(),
            'filter' => $filter,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $types = EmployeeDocumentType::active()->ordered()->get();

        if ($types->isEmpty()) {
            return redirect()->route('employee_document_type.index')
                ->with('error', 'Define at least one information type before adding employee records.');
        }

        return view('employee_document.create', [
            'types' => $types,
            'employees' => $this->selectableEmployees(),
            'preselectedUserId' => $request->query('user_id'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $document = EmployeeDocument::find($request->employee_document_id);

        $type = EmployeeDocumentType::findOrFail($request->employee_document_type_id);

        $rules = [
            'user_id' => 'required|exists:users,id',
            'employee_document_type_id' => 'required|exists:employee_document_types,id',
            'title' => 'nullable|string|max:255',
            'reference_no' => 'nullable|string|max:255',
            'issued_by' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:2000',
            'attachment.*' => 'file|max:10240',
        ];

        // Expiry is only mandatory when the type says it tracks expiry.
        $rules['expiry_date'] = $type->has_expiry
            ? 'required|date|after_or_equal:issue_date'
            : 'nullable|date|after_or_equal:issue_date';

        // An attachment is required the first time round if the type demands one.
        if ($type->requires_attachment && (!$document || $document->attachments()->count() === 0)) {
            $rules['attachment'] = 'required';
        }

        foreach ($type->customFieldList() as $field) {
            if (!empty($field['required'])) {
                $rules['custom_values.' . $field['key']] = 'required';
            }
        }

        $request->validate($rules, [], $this->customFieldAttributeNames($type));

        $data = [
            'user_id' => $request->user_id,
            'employee_document_type_id' => $type->id,
            'title' => $request->title,
            'reference_no' => $request->reference_no,
            'issued_by' => $request->issued_by,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'remarks' => $request->remarks,
            'custom_values' => $this->cleanCustomValues($type, $request->input('custom_values', [])),
        ];

        if ($document) {
            $data['updated_by'] = Auth::id();
            $document->update($data);
            $message = 'Employee information updated.';
        } else {
            $data['created_by'] = Auth::id();
            $document = EmployeeDocument::create($data);
            $message = 'Employee information saved.';
        }

        $this->storeAttachments($request, $document);

        return redirect()->route('employee_document.index')->with('success', $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmployeeDocument $employee_document)
    {
        return view('employee_document.create', [
            'document' => $employee_document->load('attachments'),
            'types' => EmployeeDocumentType::active()->ordered()->get(),
            'employees' => $this->selectableEmployees(),
            'preselectedUserId' => $employee_document->user_id,
        ]);
    }

    /**
     * All records held against one employee.
     */
    public function employee(User $user)
    {
        return view('employee_document.employee', [
            'employee' => $user,
            'documents' => $user->employeeDocuments()
                ->with(['documentType', 'attachments'])
                ->orderByRaw('expiry_date is null')
                ->orderBy('expiry_date')
                ->get(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmployeeDocument $employee_document)
    {
        foreach ($employee_document->attachments as $attachment) {
            $attachment->delete();
        }

        $employee_document->delete();

        return redirect()->back()->with('success', 'Employee information deleted.');
    }

    /**
     * Employees the current user is allowed to file information against.
     */
    private function selectableEmployees()
    {
        $user = Auth::user();

        $query = User::where('is_active', 1);

        if ($user->can('show-own-department-only')) {
            $query->where('department_id', $user->department_id);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Drop values whose field no longer exists on the type, so renaming or
     * removing a field does not leave junk behind.
     */
    private function cleanCustomValues(EmployeeDocumentType $type, array $values): array
    {
        $allowed = array_column($type->customFieldList(), 'key');
        $clean = [];

        foreach ($values as $key => $value) {
            if (in_array($key, $allowed, true) && $value !== null && $value !== '') {
                $clean[$key] = is_string($value) ? trim($value) : $value;
            }
        }

        return $clean;
    }

    /**
     * Make validation messages read "Grade is required" rather than
     * "custom_values.grade is required".
     */
    private function customFieldAttributeNames(EmployeeDocumentType $type): array
    {
        $names = [];

        foreach ($type->customFieldList() as $field) {
            $names['custom_values.' . $field['key']] = $field['label'];
        }

        return $names;
    }

    private function storeAttachments(Request $request, EmployeeDocument $document): void
    {
        if (!$request->hasFile('attachment')) {
            return;
        }

        $files = $request->file('attachment');
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            $path = 'employee_document/' . $document->id . '/' . $file->getClientOriginalName();

            $attachment = new Attachment([
                'filename' => $file->getClientOriginalName(),
                'path' => $path,
            ]);

            $file->storeAs('attachments', $path);
            $document->attachments()->save($attachment);
        }
    }
}
