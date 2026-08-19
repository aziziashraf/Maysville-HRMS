<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Leave Application | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        <link rel="stylesheet" href="{{asset('plugins/table/datatable/datatables.css')}}">
        @vite(['resources/scss/light/plugins/table/datatable/dt-global_style.scss'])
        @vite(['resources/scss/dark/plugins/table/datatable/dt-global_style.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 mx-auto layout-spacing">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Leave Application</h5>
                    <div>
	                    <a href="{{ route('leave.create') }}" class="btn btn-primary mb-2 me-4">Apply Leave</a>
                    </div>
                    <div class="widget-content widget-content-area br-8">
                        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Leave Type</th>
                                    <th>Date(s)</th>
									<th class="text-center">Time</th>
                                    <th class="text-center">Status</th>
									<th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
								@foreach($leave as $row)
								<tr>
									<td>{{$row->leaveType->name}}</td>
					  				<td>
										{{$row->start_date ??''}}
										@if ($row->end_date)
											to {{$row->end_date}}
										@endif
									</td>
                                    <td class="text-center">
										{{$row->start_time ??''}}
										@if ($row->end_time)
											to {{$row->end_time}}
										@endif
									</td>
									<td class="text-center">
                                        @if($row->status == 'draft')
                                        <span class="badge badge-info">Draft</span>
                                        @elseif($row->status == 'submitted')
                                        <span class="badge badge-primary">Submitted</span>
                                        @elseif($row->status == 'reviewed')
                                        <span class="badge badge-success">Reviewed</span>
                                        @elseif($row->status == 'approved')
                                        <span class="badge badge-success">Approved</span>
                                        @elseif($row->status == 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                        @elseif($row->status == 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
									<td class="text-center">
                                        <div class="action-btns">
                                            <a href="{{ route('leave.edit',$row) }}" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                            </a>
                                        </div> 
									</td>
								</tr>
								@endforeach
                            </tbody>
                            <tfoot>
                                <tr>
									<th>Leave Type</th>
                                    <th>Date(s)</th>
									<th class="text-center">Time</th>
                                    <th class="text-center">Status</th>
									<th class="text-center">Action</th>
                                </tr>
                            </tfoot>
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
                "pageLength": 10
            });
        </script>
    </x-slot>
                <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>