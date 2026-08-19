<x-base-layout :scrollspy="false">
  @php
    if($visitor){
      $title = "Edit Visitor";
    } else {
      $title = "Add Visitor";
    }
  @endphp
  <x-slot:pageTitle>
    {{$title}} | {{ env('APP_NAME') }}
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

        @vite(['resources/scss/light/assets/elements/alert.scss'])        
    	  @vite(['resources/scss/dark/assets/elements/alert.scss']) 
      <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

      <!-- BREADCRUMB -->
      <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('visitor.index') }}">Visitors</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form action="{{ route('visitor.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
          </div>
          <div id="basic" class="col-12 col-md-8 collayout-spacing">
            <div class="card">
              <div class="card-body">
              <h5 class="card-title">Basic Info</h5>
                @if($errors->any())
                  @foreach ($errors->all() as $error)
                      <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
                  @endforeach
                @endif
                <div class="row">
                  <div class="col-12">
                    <input type="text" class="form-control" id="visitor_id" name="visitor_id" value="{{$visitor->id ??''}}" hidden>
                    <div class="form-group mb-4">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$visitor->name ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="nric">IC / Passport</label>
                      <input type="text" class="form-control" id="nric" name="nric" placeholder="IC / Passport.." value="{{$visitor->nric ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="email">Email</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Email.." value="{{$visitor->email ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="contact_no">Phone No.</label>
                      <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Phone Number.." value="{{$visitor->contact_no ??''}}">
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div id="basic" class="col-12 col-md-4  collayout-spacing">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Status</h5>
                <div class="form-group mb-4">
                  <label>Status</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo isset($visitor->is_active) && $visitor->is_active == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_active">
                      Active
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_block" value="0" <?php echo isset($visitor->is_active) && ($visitor->is_active == 0 || $visitor->is_active == 2) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_block">
                      Block
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>
      @isset($visitor->id)
      <div class="row layout-top-spacing">
        <div class="col-xl-12 col-lg-12 col-sm-12 mx-auto layout-spacing">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Visitor Pass</h5>
              <form method="GET" class="row mb-3">
                <div class="col-md-6 mb-3">
                  <label for="date_range">Date Range</label>
                  <input id="date_range" name="date_range" placeholder="Date Range" class="form-control flatpickr flatpickr-input active" type="text">
                </div>
                <div class="col-md-6 mb-3">
                  <label for="search">Card ID</label>
                  <input type="text" class="form-control" aria-label="Default select example" id="search" name="search" placeholder="Search...">
                </div>
                <div class="col-md-10 mb-3">
                  <label for="search">Visit Purpose</label>
                  <input type="text" class="form-control" aria-label="Default select example" id="search" name="search" placeholder="Search...">
                </div>
                <div class="col-md-2 mb-3" style="display: flex;">
                  <button class="btn btn-primary mb-1" type="submit" style="align-self: flex-end;" type="button">Search</button>
                </div>
              </form>
              <div class="widget-content widget-content-area br-8">
                <table id="zero-config" class="table table-striped dt-table-hover" style="width:100%">
                  <thead>
                    <th>Card ID</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Time From</th>
                    <th>Time To</th>
                    <th>Visit Purpose</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                  </thead>
                  <tbody id="all_data">
                  @foreach($visitor->visitorPass as $row)
                    <tr>
                      <td>{{$row->visitor_card_id ??'-'}}</td>
                      <td>{{$row->from_date ??'-'}}</td>
                      <td>{{$row->to_date ??'-'}}</td>
                      <td>{{$row->from_time ??'-'}}</td>
                      <td>{{$row->to_time ??'-'}}</td>
                      <td>{{$row->visit_purpose ??'-'}}</td>
                      <td class="text-center">{{$row->status ??"-"}}</td>
                      <td class="text-center">
                        <div class="action-btns">
                          <a href="{{ route('visitor.setAccess',$row) }}" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Set Access">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-key"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                          </a>
                          @if($row->status <> "Approved")
                          <a onclick="if(confirm('Are you sure you want to approve this pass?')){ window.location.href='{{ route('visitor.approvePass',$row) }}' }" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Approve Pass">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check"><polyline points="20 6 9 17 4 12"></polyline></svg>
                          </a>
                          <a onclick="if(confirm('Are you sure you want to delete this pass?')){ window.location.href='{{ route('visitor.destroyPass',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                          </a>
                          @endif
                        </div>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <th>Card ID</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Time From</th>
                    <th>Time To</th>
                    <th>Visit Purpose</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Action</th>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
      @endisset

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
        @vite(['resources/assets/js/custom.js'])
        <script src="{{asset('plugins/table/datatable/datatables.js')}}"></script>
		<script>
			$('#zero-config').DataTable({
				"dom": "<'dt--top-section'<'row'<'col-12 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
					"<'table-responsive'tr>",
				"oLanguage": {
					"sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
					"sSearchPlaceholder": "Search...",
				},
				"stripeClasses": [],
				"pageLength": -1
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