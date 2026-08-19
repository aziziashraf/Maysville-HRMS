<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Purchase Requisition Approval | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        <link rel="stylesheet" href="{{asset('plugins/table/datatable/datatables.css')}}">
        @vite(['resources/scss/light/plugins/table/datatable/dt-global_style.scss'])
        @vite(['resources/scss/dark/plugins/table/datatable/dt-global_style.scss'])

        <link rel="stylesheet" href="{{asset('plugins/flatpickr/flatpickr.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/noUiSlider/nouislider.min.css')}}">
        @vite(['resources/scss/light/plugins/flatpickr/custom-flatpickr.scss'])
        @vite(['resources/scss/dark/plugins/flatpickr/custom-flatpickr.scss'])
        <style>
            .dt-buttons .dt-button {
                color: #fff !important;
                background-color: #4361ee !important;
                border-color: $primary;
                margin-bottom: 5px;
                margin-right: 5px;
            }
        </style>
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 mx-auto layout-spacing">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Purchase Requisition Approval</h5>
                    <form method="GET" class="row mb-3">
						<div class="col-md-4 mb-3">
							<label for="date_range">Date Approved</label>
							<input id="date_range" name="date_range" placeholder="Date Range" class="form-control flatpickr flatpickr-input active" type="text" value="{{$filter['date_range'] ??''}}">
						</div>
						<div class="col-md-4 mb-3">
							<label for="department_id" >Department</label>
							<select id="department_id" name="department_id" class="form-select" aria-label="Default select example">
								<option value='0'>--All Departments--</option>
								@foreach($department as $d)
								<option value='{{$d->id}}' <?php echo $filter['department_id']==$d->id?'selected':''?> >{{$d->department_name}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-4 mb-3">
							<label for="status" >Status</label>
							<select id="status" name="status" class="form-select" aria-label="Default select example">
								<option value=''>--All Status--</option>
								<option value='submitted' <?php echo $filter['status']=='submitted'?'selected':''?>>Submitted</option>
								<option value='reviewed' <?php echo $filter['status']=='reviewed'?'selected':''?>>Reviewed</option>
								<option value='approved' <?php echo $filter['status']=='approved'?'selected':''?>>Approved</option>
								<option value='rejected' <?php echo $filter['status']=='rejected'?'selected':''?>>Rejected</option>
								<option value='cancelled' <?php echo $filter['status']=='cancelled'?'selected':''?>>Cancelled</option>
							</select>
						</div>
                        <div class="col-md-6 mb-3">
							<label for="search">Search Name, Staff ID...</label>
							<input type="text" class="form-control" aria-label="Default select example"id="search" name="search" placeholder="Search..." value="{{$filter['search'] ??''}}">
						</div>
						<div class="col-md-2 mb-3" style="display: flex;">
							<button class="btn btn-primary mb-1" type="submit" style="align-self: flex-end;" type="button">Search</button>
						</div>
                    </form>
                    <div class="widget-content widget-content-area br-8">
                        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Requested by</th>
                                    <th>Purpose</th>
                                    <th class="text-center">Source of Fund</th>
                                    <th class="text-center">Amount (RM)</th>
                                    <th class="text-center">When Needed</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Date Approved</th>
									<th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
								@foreach($purchaseRequisitions as $row)
								<tr>
                                    <td>{{$row->user->name}}</td>
                                    <td>{{$row->purpose}}</td>
                                    <td class="text-center">{{ucwords($row->source_of_fund)}}</td>
					  				<td class="text-center">{{$row->totalAmount()}}</td>
                                    <td class="text-center">{{$row->date_needed}}</td>
									<td class="text-center">
                                        @if($row->status == 'draft')
                                        <span class="badge badge-info">Draft</span>
                                        @elseif($row->status == 'submitted')
                                        <span class="badge badge-primary">Submitted</span>
                                        @elseif($row->status == 'approved')
                                        <span class="badge badge-success">Approved</span>
                                        @elseif($row->status == 'cancelled')
                                        <span class="badge badge-danger">Cancelled</span>
                                        @elseif($row->status == 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($row->status == 'approved')
                                            {{ date('Y-m-d', strtotime($row->approved_at)) }}
                                        @endif
                                    </td>
									<td class="text-center">
                                        <div class="action-btns">
                                            <a href="{{ route('purchase_requisition.requestEdit',$row) }}" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                                            </a>
                                            @if($row->status == 'approved')
                                            <!-- download  -->
                                            <a href="{{ route('purchase_requisition.download',$row) }}" class="action-btn btn-download bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Download" target=”_blank”>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                            </a>
                                            @endif
                                        </div>
									</td>
								</tr>
								@endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Purpose</th>
                                    <th class="text-center">Source of Fund</th>
                                    <th class="text-center">Amount (RM)</th>
                                    <th class="text-center">When Needed</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Date Approved</th>
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
        <script src="{{asset('plugins/table/datatable/button-ext/dataTables.buttons.min.js')}}"></script>
        <script src="{{asset('plugins/table/datatable/button-ext/jszip.min.js')}}"></script>
        <script src="{{asset('plugins/table/datatable/button-ext/buttons.html5.min.js')}}"></script>
        <script src="{{asset('plugins/table/datatable/button-ext/buttons.print.min.js')}}"></script>
        <script>
            $('#zero-config').DataTable( {
                "dom": "<'dt--top-section'<'row'<'col-sm-12 col-md-6 d-flex justify-content-md-start justify-content-center'B><'col-sm-12 col-md-6 d-flex justify-content-md-end justify-content-center mt-md-0 mt-3'f>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
                buttons: {
                    buttons: [
                        { extend: 'excel', className: 'btn', text: 'Export' },
                    ]
                },
                "oLanguage": {
                    "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
                    "sInfo": "Showing page _PAGE_ of _PAGES_",
                    "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
                    "sSearchPlaceholder": "Search...",
                "sLengthMenu": "Results :  _MENU_",
                },
                "stripeClasses": [],
                "lengthMenu": [10, 20, 50],
                "pageLength": 50 
            } );
        </script>
        <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
        <script>
            var f3 = flatpickr(document.getElementById('date_range'), {
                mode: "range",
            });
        </script>
    </x-slot>
                <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>