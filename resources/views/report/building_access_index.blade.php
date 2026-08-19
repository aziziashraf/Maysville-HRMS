<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Daily Scan | {{ env('APP_NAME') }}
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

        @vite(['resources/scss/light/assets/components/modal.scss'])
		@vite(['resources/scss/dark/assets/components/modal.scss'])
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
					<h5 class="card-title">Daily Scan</h5>
                    <form method="GET" class="row mb-3">
						<div class="col-md-4 mb-3">
							<label for="date_range">Date Range</label>
							<input id="date_range" name="date_range" placeholder="Date Range" class="form-control flatpickr flatpickr-input active" type="text" value="{{$filter['date_range'] ??''}}">
						</div>
						<div class="col-md-4 mb-3">
							<label for="access_id">Access</label>
							<select id="access_id" name="access_id" class="form-select" >
								<option value='0'>Access</option>
								@foreach($access as $a)
								<option value='{{$a->id}}' <?php echo $filter['access_id']==$a->id?'selected':''?> >{{$a->access_name}} ({{$a->activity}})</option>
								@endforeach
							</select>
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
						<div class="col-md-6 mb-3">
							<label for="search">Search Name, Staff ID...</label>
							<input type="text" class="form-control" aria-label="Default select example"id="search" name="search" placeholder="Search..." value="{{$filter['search'] ??''}}">
						</div>
						<div class="col-md-2 mb-3" style="display: flex;">
							<button type="submit" class="btn btn-primary mb-1" type="button" style="align-self: flex-end;">Search</button>
						</div>
                    </form>
                    <div class="widget-content widget-content-area br-8">
                        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
									<th>No.</th>
									<th>Datetime</th>
									<th>Name</th>
									<th>Location</th>
									<th>Status</th>
									<th>Email</th>
									<th>Role</th>
									<th>Department</th>
									<th>Activity</th>
									<th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="all_data">
                                @foreach($attendance as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
									<td>{{$row->scan_datetime}}</td>
									<td>{{$row->user->name ??'-'}}</td>
									<td>
										@if(isset($row->access))
											{{$row->access->access_name ??'-'}}
										@elseif(isset($row->location))
											<button onclick="openMap('{{ $row->id }}')" class="btn btn-primary btn-sm">{{ $row->location ?? '-' }}</button>
										@endif
									</td>
									<td>{{$row->scan_status ??'-'}}</td>
									<td>{{$row->user->email ??'-'}}</td>
									<td>{{$row->user->role ??'-'}}</td>
									<td>{{$row->user->department->department_name ??'-'}}</td>
									<td>{{$row->access->activity ??'-'}}</td>
									<td>{{$row->remarks ??'-'}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
									<th>No.</th>
									<th>Datetime</th>
									<th>Name</th>
									<th>Location</th>
									<th>Status</th>
									<th>Email</th>
									<th>Role</th>
									<th>Department</th>
									<th>Activity</th>
									<th>Remarks</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<!-- Modal -->
	<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalTitle" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title add-title" id="mapModalTitleLabel1">Location Map</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
						<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
					</button>
				</div>

				<div class="modal-body" style="padding: 10px;">
					<div class="add-map-box">
						<div class="add-map-content">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
        <script src="{{asset('plugins/jquery-ui/jquery-ui.min.js')}}"></script>
        <script src="{{asset('plugins/table/datatable/datatables.js')}}"></script>
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
		<script>
			function openMap(id) {
				// Make an AJAX request to get the coordinates
				$.ajax({
					url: '/report/getCoordinates/' + id,
					method: 'GET',
					success: function(response) {
						// Handle the response and populate the map content
						var latitude = response.latitude;
						var longitude = response.longitude;
						var mapUrl = response.mapUrl;;
						
						var mapIframe = $('<iframe>', {
							width: '100%',
							height: '300px', // Set the desired height of the iframe
							allowfullscreen: true,
							src: mapUrl
						});

						// Update the map content in the modal and show modal after map load
						$('.add-map-content').html(mapIframe);
						mapIframe.on('load', function() {
							$('#mapModal').modal('show');
						});
						// console.log(mapUrl);
					},
					error: function(xhr, status, error) {
						// Handle error response
						console.log(xhr.responseText);
					}
				});
			}
		</script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>