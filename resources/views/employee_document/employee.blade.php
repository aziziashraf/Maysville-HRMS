<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        {{ $employee->name }} — Information | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        @vite(['resources/scss/light/assets/elements/alert.scss'])
        @vite(['resources/scss/dark/assets/elements/alert.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BREADCRUMB -->
    <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee_document.index') }}">Employee Information</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $employee->name }}</li>
            </ol>
        </nav>
    </div>
    <!-- /BREADCRUMB -->

    @php
        $expired = $documents->where('expiry_status', 'expired');
        $expiring = $documents->where('expiry_status', 'expiring');
    @endphp

    <div class="row layout-top-spacing">
        <div class="col-12 layout-spacing">

            @if(session('success'))
                <div class="alert alert-light-success alert-dismissible fade show border-0 mb-4" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    {{ session('success') }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <h5 class="card-title mb-1">{{ $employee->name }}</h5>
                            <div class="text-muted">
                                {{ $employee->staff_id ? 'Staff ID '.$employee->staff_id.' · ' : '' }}
                                {{ $employee->department->name ?? 'No department' }}
                                {{ $employee->position->name ?? '' }}
                            </div>
                        </div>
                        @can('employee_document-create')
                        <a href="{{ route('employee_document.create', ['user_id' => $employee->id]) }}" class="btn btn-primary">
                            Add Information
                        </a>
                        @endcan
                    </div>

                    @if($expired->count() || $expiring->count())
                        <div class="alert alert-light-warning border-0 mt-3 mb-0" role="alert">
                            <strong>{{ $expired->count() }}</strong> expired,
                            <strong>{{ $expiring->count() }}</strong> expiring soon.
                        </div>
                    @endif
                </div>
            </div>

            @forelse($documents as $row)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                            <div>
                                <h6 class="mb-1">{{ $row->display_title }}</h6>
                                <div class="text-muted" style="font-size:13px;">{{ $row->documentType->name ?? '' }}</div>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-light-{{ $row->expiry_badge_class }}">{{ $row->expiry_label }}</span>
                                <div class="action-btns mt-2">
                                    @can('employee_document-edit')
                                    <a href="{{ route('employee_document.edit',$row) }}" class="action-btn btn-edit bs-tooltip me-2" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                    </a>
                                    @endcan
                                    @can('employee_document-destroy')
                                    <a onclick="if(confirm('Delete this record?')){ window.location.href='{{ route('employee_document.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </a>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row" style="font-size:13px;">
                            <div class="col-6 col-md-3 mb-2">
                                <div class="text-muted">Reference</div>
                                <div>{{ $row->reference_no ?? '-' }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="text-muted">Issued By</div>
                                <div>{{ $row->issued_by ?? '-' }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="text-muted">Issue Date</div>
                                <div>{{ $row->issue_date ? $row->issue_date->format('d M Y') : '-' }}</div>
                            </div>
                            <div class="col-6 col-md-3 mb-2">
                                <div class="text-muted">Expiry Date</div>
                                <div>{{ $row->expiry_date ? $row->expiry_date->format('d M Y') : '-' }}</div>
                            </div>

                            @foreach($row->documentType->customFieldList() ?? [] as $field)
                                <div class="col-6 col-md-3 mb-2">
                                    <div class="text-muted">{{ $field['label'] }}</div>
                                    <div>{{ $row->custom_values[$field['key']] ?? '-' }}</div>
                                </div>
                            @endforeach
                        </div>

                        @if($row->remarks)
                            <div class="mt-2" style="font-size:13px;">
                                <div class="text-muted">Remarks</div>
                                <div>{{ $row->remarks }}</div>
                            </div>
                        @endif

                        @if($row->attachments->count())
                            <div class="mt-3">
                                <div class="text-muted mb-2" style="font-size:13px;">Attachments</div>
                                <ul class="list-group">
                                    @foreach($row->attachments as $attachment)
                                        <li class="list-group-item py-2">
                                            <a href="{{ route('attachment.show', $attachment) }}" target="_blank">{{ $attachment->filename }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <p class="text-muted mb-3">No information recorded for this employee yet.</p>
                        @can('employee_document-create')
                        <a href="{{ route('employee_document.create', ['user_id' => $employee->id]) }}" class="btn btn-primary">Add Information</a>
                        @endcan
                    </div>
                </div>
            @endforelse

        </div>
    </div>

    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>
