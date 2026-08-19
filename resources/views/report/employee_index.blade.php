<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Employee Attendance | {{ env('APP_NAME') }}
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
					<h5 class="card-title">Employee Attendance</h5>
                    <form method="GET" class="row mb-3">
						<div class="col-md-4 mb-3">
							<label for="date_range">Date Range</label>
							<input id="date_range" name="date_range" placeholder="Date Range" class="form-control flatpickr flatpickr-input active" type="text" value="{{$filter['date_range'] ??''}}">
						</div>
                        @cannot('show-own-department-only')
						<div class="col-md-4 mb-3">
							<label for="department_id" >Department</label>
							<select id="department_id" name="department_id" class="form-select" aria-label="Default select example">
								<option value='0'>Department</option>
								@foreach($department as $d)
								<option value='{{$d->id}}' <?php echo $filter['department_id']==$d->id?'selected':''?> >{{$d->department_name}}</option>
								@endforeach
							</select>
						</div>
                        @endcannot
						<div class="col-md-4 mb-3">
							<label for="status" >Status</label>
							<select id="status" name="status" class="form-select" aria-label="Default select example">
								<option value=''>Status</option>
								<option value='Absent' <?php echo $filter['status']=='Absent'?'selected':''?>>Absent</option>
								<option value='Present' <?php echo $filter['status']=='Present'?'selected':''?>>Present</option>
								<option value='Late' <?php echo $filter['status']=='Late'?'selected':''?>>Late</option>
                                <option value='On Leave' <?php echo $filter['status']=='On Leave'?'selected':''?>>On Leave</option>
							</select>
						</div>
						<div class="col-md-6 mb-3">
							<label for="search">Search Name, Staff ID...</label>
							<input type="text" class="form-control" aria-label="Default select example"id="search" name="search" placeholder="Search..." value="{{$filter['search'] ??''}}">
						</div>
						<div class="col-md-2 mb-3 d-flex justify-content-center">
                            <button class="btn btn-primary mb-1" type="submit" style="align-self: flex-end;">Search</button>
                        </div>
                        <div class="col-md-4 mb-3 d-flex justify-content-end">
                            <div class="btn-group">
                                <button class="btn btn-dark dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false"  style="align-self: flex-end;">
                                    Show/Hide Column
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </button>
                                @php
                                    $columns = [
                                        'No',
                                        'Date',
                                        'Name',
                                        'Staff ID',
                                        'Department',
                                        'Check-In',
                                        'Check-Out',
                                        'Duration',
                                        'Remote Working Duration',
                                        'Location',
                                        'Status',
                                        'Secondary Status',
                                        'Remarks'
                                    ];
                                @endphp
                                <div class="dropdown-menu px-3  dropdown-menu-end" style="width: max-content;">
                                    @foreach($columns as $key => $column)
                                    <div class="form-check">
                                        <input class="form-check-input toggle-vis" type="checkbox" data-column="{{$key}}" id="column-{{$column}}" checked>
                                        <label class="form-check-label" for="column-{{$column}}">{{$column}}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
						</div>
                    </form>
                    <div class="widget-content widget-content-area br-8">
                        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    @foreach($columns as $column)
									<th>{{$column}}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="all_data">
                                @foreach($attendance as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
									<td>{{$row['check_in_date'] ??'-'}}</td>
									<td>{{$row['name'] ??'-'}}</td>
									<td>{{$row['staff_id'] ??'-'}}</td>
									<td>{{$row['department_name'] ??'-'}}</td>
									<td>{{$row['check_in_time'] ??'-'}}</td>
									<td>{{$row['check_out_time'] ??'-'}}</td>
									<td>{{$row['duration'] ??'-'}}</td>
                                    <td>{!! str_replace(',', ',<br>', $row['remote_working_duration'] ?? '-') !!}</td>
									<td>{{$row['location'] ??'-'}}</td>
									<?php if($row['status'] == "Present"){
										$color = "green";
									}else if($row['status'] == "Absent"){
										$color = "red";
									}else if($row['status'] == "Late"){
										$color = "orange";
									}else if($row['status'] == "On Leave"){
										$color = "blue";
									}else{
										$color = "black";
									}?>
									<td style="color:{{$color}}">{{$row['status'] ??'-'}}</td>
                                    <td>{!! str_replace(',', ',<br>', $row['secondary_status'] ?? '-') !!}</td>
									<td>{{$row['remarks'] ??'-'}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    @foreach($columns as $column)
									<th>{{$column}}</th>
                                    @endforeach
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
            var table = $('#zero-config').DataTable( {
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
                        "sSearchPlaceholder": "Filter Table...",
                    "sLengthMenu": "Results :  _MENU_",
                    },
                    "stripeClasses": [],
                    "lengthMenu": [10, 20, 50],
                    "pageLength": 50 
                } );

                $('a.toggle-vis').on('click', function (e) {
                    e.preventDefault();

                    // Get the column API object
                    var column = table.column($(this).attr('data-column'));

                    // Toggle the visibility
                    column.visible(!column.visible());

                    // Change button classes based on column visibility
                    if (column.visible()) {
                        $(this).removeClass('btn-light-dark').addClass('btn-dark');
                    } else {
                        $(this).removeClass('btn-dark').addClass('btn-light-dark');
                    }
                });
                
                // Function to update checkbox state based on column visibility
                function updateCheckboxes() {
                    $('input.toggle-vis').each(function () {
                        var column = table.column($(this).attr('data-column'));
                        $(this).prop('checked', column.visible());
                    });
                }

                // Initial update of checkboxes on page load
                updateCheckboxes();

                // Checkbox change event listener
                $('input.toggle-vis').on('change', function () {
                    // Get the column API object
                    var column = table.column($(this).attr('data-column'));

                    // Toggle the visibility
                    column.visible(!column.visible());

                    // Update checkbox state
                    updateCheckboxes();
                });
        </script>

        <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
        <script>
            var f3 = flatpickr(document.getElementById('date_range'), {
                mode: "range",
            });
        </script>
		<script>
			function GenerateButton(){
				$.ajax({
					url: "{{ url('/report/generateEmployeeAttendance') }}",
					method: 'GET',
					success: function(data) {
						location.reload();
					},
				})
			}
		</script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>