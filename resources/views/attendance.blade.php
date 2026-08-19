<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Attendance | {{ env('APP_NAME') }}
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
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 mx-auto layout-spacing">

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Attendance</h5>
                    <form method="GET">
                        <div class="input-group mb-3">
                            <input id="date_range" name="date_range" class="form-control flatpickr flatpickr-input active" type="text" value="{{$filter['date_range'] ??''}}">
                            <button class="btn btn-primary" type="submit" id="button-addon2">Search</button>
                        </div>
                    </form>
                    <div class="widget-content widget-content-area br-8">
                        <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th hidden>Attendance ID</th>
                                    <th>Datetime</th>
									<th>Timestamp</th>
									<th>Name</th>
									<th>Staff ID</th>
									<th>Department</th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<th>Duration</th>
									<th>Location</th>
									<th>Level</th>
									<th>Status</th>
									<th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td hidden><input type="text" id="attendance_id_{{$loop->iteration}}" value="{{$row->id}}"></td>
                                    <td>{{$row['check_in_date'] ??'-'}}</td>
									<td>{{$row['timestamp'] ??'-'}}</td>
									<td>{{$row['name'] ??'-'}}</td>
									<td>{{$row['staff_id'] ??'-'}}</td>
									<td>{{$row['department_name'] ??'-'}}</td>
									<td>{{$row['check_in_time'] ??'-'}}</td>
									<td>{{$row['check_out_time'] ??'-'}}</td>
									<td>{{$row['duration'] ??'-'}}</td>
									<td>{{$row['location'] ??'-'}}</td>
									<td>{{$row['level'] ??'-'}}</td>
									<?php if($row['status'] == "Present"){
										$color = "green";
									}else if($row['status'] == "Absent"){
										$color = "red";
									}else if($row['status'] == "Late"){
										$color = "orange";
									}else{
										$color = "black";
									}?>
									<td style="color:{{$color}}">{{$row['status'] ??'-'}}</td>
									<td>{{$row['remarks'] ??'-'}}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
									<th>No</th>
                                    <th hidden>Attendance ID</th>
                                    <th>Datetime</th>
									<th>Timestamp</th>
									<th>Name</th>
									<th>Staff ID</th>
									<th>Department</th>
									<th>Check-In</th>
									<th>Check-Out</th>
									<th>Duration</th>
									<th>Location</th>
									<th>Level</th>
									<th>Status</th>
									<th>Remarks</th>
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
        <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
        <script>
            var f3 = flatpickr(document.getElementById('date_range'), {
                mode: "range",
            });
        </script>
    </x-slot>
                <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>