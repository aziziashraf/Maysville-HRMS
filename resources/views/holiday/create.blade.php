<x-base-layout :scrollspy="false">

  <x-slot:pageTitle>
    Add Holiday | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
      <!--  BEGIN CUSTOM STYLE FILE  -->
      @vite(['resources/scss/light/assets/components/timeline.scss'])
      <link rel="stylesheet" type="text/css" href="{{asset('plugins/tomSelect/tom-select.default.min.css')}}">
      @vite(['resources/scss/light/plugins/tomSelect/custom-tomSelect.scss'])
      @vite(['resources/scss/dark/plugins/tomSelect/custom-tomSelect.scss'])
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
            <li class="breadcrumb-item"><a href="#">Form</a></li>
            <li class="breadcrumb-item active" aria-current="page">Basic</li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form action="{{ route('holiday.store') }}" method="post" enctype="multipart/form-data">
	    @csrf
      <div class="row layout-top-spacing">
        <div id="basic" class="col-12  collayout-spacing">
          <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button> 
        </div>
        <div id="basic" class="col-12 collayout-spacing">
          <div class="statbox widget box box-shadow">
            <div class="widget-header">
              <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                  <h4>Holiday Details</h4>
                </div>
              </div>
              @if($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
                @endforeach
              @endif
            </div>
            <div class="widget-content widget-content-area">

              <div class="row">
                <div class="col-12">
					<input type="text" id="holiday_id" name="holiday_id" value="{{$holiday->id ??''}}" hidden>
                  <div class="form-group mb-4">
                    <label for="holiday_name">Holiday Name</label>
                    <input type="text" class="form-control" id="holiday_name" name="holiday_name" placeholder="Name.." value="{{$holiday->holiday_name ??''}}" required>
                  </div>
				  <div class="form-group mb-4">
				  	<label for="date_range">Date Range</label>
				  	<input id="date_range" name="date_range" class="form-control flatpickr flatpickr-input active" placeholder="Date Range.." type="text" value="{{$holiday->date_range ??''}}">
				  </div>
                </div>
              </div>

            </div>
          </div>
        </div>
      </div>
      </form>



      <!--  BEGIN CUSTOM SCRIPTS FILE  -->
      <x-slot:footerFiles>
	  <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
        <script>
            var f3 = flatpickr(document.getElementById('date_range'), {
                mode: "range",
            });
        </script>
        </x-slot>
        <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>