<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Employee Information | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        <link rel="stylesheet" href="{{asset('plugins/table/datatable/datatables.css')}}">
        @vite(['resources/scss/light/plugins/table/datatable/dt-global_style.scss'])
        @vite(['resources/scss/dark/plugins/table/datatable/dt-global_style.scss'])
        @vite(['resources/scss/light/assets/elements/alert.scss'])
        @vite(['resources/scss/dark/assets/elements/alert.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BREADCRUMB -->
    <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">Employee Management</a></li>
                <li class="breadcrumb-item active" aria-current="page">Employee Information</li>
            </ol>
        </nav>
    </div>
    <!-- /BREADCRUMB -->

    @php
        $expired = $documents->where('expiry_status', 'expired')->count();
        $expiring = $documents->where('expiry_status', 'expiring')->count();
    @endphp

    <div class="row layout-top-spacing">
        <div class="col-12 layout-spacing">

            @if(session('success'))
                <div class="alert alert-light-success alert-dismissible fade show border-0 mb-4" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-4" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    {{ session('error') }}
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Employee Information</h5>

                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @can('employee_document-create')
                        <a href="{{ route('employee_document.create') }}" class="btn btn-primary">Add Information</a>
                        @endcan
                        @can('employee_document_type-index')
                        <a href="{{ route('employee_document_type.index') }}" class="btn btn-outline-primary">Manage Types</a>
                        @endcan
                    </div>

                    @if($expired || $expiring)
                    <div class="alert alert-light-warning border-0 mb-4" role="alert">
                        <strong>{{ $expired }}</strong> expired and <strong>{{ $expiring }}</strong> expiring soon in the current view.
                    </div>
                    @endif

                    <!-- FILTERS -->
                    <form method="get" action="{{ route('employee_document.index') }}" class="row mb-4">
                        <div class="col-12 col-md-3 mb-2">
                            <label style="font-size:13px;">Employee</label>
                            <select class="form-select" name="user_id">
                                <option value="">All employees</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (string)$filter['user_id'] === (string)$employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label style="font-size:13px;">Information Type</label>
                            <select class="form-select" name="employee_document_type_id">
                                <option value="">All types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}" {{ (string)$filter['employee_document_type_id'] === (string)$type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2">
                            <label style="font-size:13px;">Expiry Status</label>
                            <select class="form-select" name="status">
                                <option value="">Any status</option>
                                <option value="expired" {{ $filter['status'] === 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="expiring" {{ $filter['status'] === 'expiring' ? 'selected' : '' }}>Expiring soon</option>
                                <option value="ok" {{ $filter['status'] === 'ok' ? 'selected' : '' }}>Valid</option>
                                <option value="none" {{ $filter['status'] === 'none' ? 'selected' : '' }}>No expiry</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 mb-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('employee_document.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                    <!-- /FILTERS -->

                    <div class="widget-content widget-content-area br-8">
                        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Information</th>
                                    <th>Reference</th>
                                    <th>Issued</th>
                                    <th>Expiry</th>
                                    <th>Status</th>
                                    <th>Files</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($documents as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('employee_document.employee', $row->user_id) }}">{{ $row->user->name ?? '-' }}</a>
                                        <div class="text-muted" style="font-size:12px;">{{ $row->user->department->name ?? '' }}</div>
                                    </td>
                                    <td>
                                        <strong>{{ $row->display_title }}</strong>
                                        <div class="text-muted" style="font-size:12px;">{{ $row->documentType->name ?? '' }}</div>
                                        @foreach($row->documentType->customFieldList() ?? [] as $field)
                                            @if(!empty($row->custom_values[$field['key']]))
                                                <div style="font-size:12px;">
                                                    <span class="text-muted">{{ $field['label'] }}:</span>
                                                    {{ $row->custom_values[$field['key']] }}
                                                </div>
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>{{ $row->reference_no ?? '-' }}</td>
                                    <td>{{ $row->issue_date ? $row->issue_date->format('d M Y') : '-' }}</td>
                                    <td data-order="{{ $row->expiry_date ? $row->expiry_date->format('Y-m-d') : '9999-12-31' }}">
                                        {{ $row->expiry_date ? $row->expiry_date->format('d M Y') : '-' }}
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ $row->expiry_badge_class }}">{{ $row->expiry_label }}</span>
                                    </td>
                                    <td>
                                        @forelse($row->attachments as $attachment)
                                            <a href="{{ route('attachment.show', $attachment) }}" target="_blank" style="font-size:12px; display:block;">
                                                {{ \Illuminate\Support\Str::limit($attachment->filename, 22) }}
                                            </a>
                                        @empty
                                            <span class="text-muted">-</span>
                                        @endforelse
                                    </td>
                                    <td class="text-center">
                                        <div class="action-btns">
                                            @can('employee_document-edit')
                                            <a href="{{ route('employee_document.edit',$row) }}" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                            </a>
                                            @endcan
                                            @can('employee_document-destroy')
                                            <a onclick="if(confirm('Delete this record?')){ window.location.href='{{ route('employee_document.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
        @vite(['resources/assets/js/custom.js'])
        <script src="{{asset('plugins/table/datatable/datatables.js')}}"></script>
        <script>
            $('#zero-config').DataTable({
                "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
                    "<'table-responsive'tr>" +
                    "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                "oLanguage": {
                    "oPaginate": {
                        "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>',
                        "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>'
                    },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Search...",
                    "sLengthMenu": "Results :  _MENU_",
                },
                "stripeClasses": [],
                "lengthMenu": [7, 10, 20, 50],
                "pageLength": 10,
                "order": []
            });
        </script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>
