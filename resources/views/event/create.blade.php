<x-base-layout :scrollspy="false">
  @php
    if(isset($event)){
      $title = 'Edit Event';
    } else {
      $title = 'Add Event';
    }
  @endphp
  <x-slot:pageTitle>
    {{$title}} | {{ env('APP_NAME') }}
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
            <li class="breadcrumb-item"><a href="{{ route('event.index') }}">Form</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form id="event_form" action="{{ route('event.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
          </div>
          <div id="basic" class="col-12 collayout-spacing">
            <div class="statbox widget box box-shadow">
              <div class="widget-header">
                <div class="row">
                  <div class="col-12">
                    <h4>Event Details</h4>
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
                    <input type="text" id="event_id" name="event_id" value="{{$event->id ?? ''}}" hidden>
                  </div>
                  <div class="form-group col-12 mb-4">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$event->name ??''}}" required>
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="event_type_id">Event Type</label>
                    <select id="event_type_id" name="event_type_id" class="form-select" required>
                      <option disabled selected> -- Select --</option>
                      @foreach($event_type as $et)
                      <option value='{{$et->id}}' <?php echo isset($event->event_type_id) && $event->event_type_id == $et->id ? 'selected' : '' ?>>{{$et->name}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" placeholder="Reason" rows="5" <?php echo isset($show) ? 'readonly' : '' ?>>{{$event->description ??''}}</textarea>
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="date_range">Date Range</label>
                    <input id="date_range" name="date_range" class="form-control flatpickr flatpickr-input active" placeholder="Date Range.." type="text" value="{{$event->date_range ??''}}">
                  </div>
                  <div id="start_time_div" class="col-12 col-md-6 form-group mb-4">
                    <label for="time_from">Start Time</label>
                    <input
                      id="time_from"
                      class="form-control"
                      type="time"
                      placeholder="Select Time.."
                      name="time_from"
                      onchange="restrictMinutesTo30(this)"
                      value="{{$event->time_from ??''}}"
                    >
                  </div>
                  <div id="end_time_div" class="col-12 col-md-6 form-group mb-3">
                    <label for="time_to">End Time</label>
                    <input
                      id="time_to"
                      class="form-control"
                      type="time"
                      placeholder="Select Time.."
                      name="time_to"
                      onchange="restrictMinutesTo30(this)"
                      value="{{$event->time_to ??''}}"
                    >
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
    <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
    <script>
      // var numDates = 0; // Initialize numDates variable outside of event handler

      var f3 = flatpickr(document.getElementById('date_range'), {
        mode: "range",
        onReady: updateDateRangeInputs,
        onChange: updateDateRangeInputs
      });

      function updateDateRangeInputs(selectedDates, dateStr, instance) {
        // Update numDates variable based on the length of selectedDates array
        var numDates = selectedDates.length;

        // Log numDates and selectedDates for debugging
        console.log(numDates);
        console.log(selectedDates);

        // Check if numDates is 2, indicating a date range is selected
        if (numDates === 2) {
          // Check if the selected dates are the same
          if (selectedDates[0].getTime() === selectedDates[1].getTime()) {
            // Show time inputs and enable them
            showTimeInputs(true);
          } else {
            // Hide time inputs and disable them
            showTimeInputs(false);
          }
        } else {
          // Show time inputs and enable them
          showTimeInputs(true);
        }
      }

      function showTimeInputs(show) {
        var startInput = document.getElementById('time_from');
        var endInput = document.getElementById('time_to');

        if (show) {
          // Show time inputs and enable them
          startInput.disabled = false;
          endInput.disabled = false;
          // Optionally, show time input divs if hidden
          // document.getElementById('start_time_div').style.display = 'block';
          // document.getElementById('end_time_div').style.display = 'block';
        } else {
          // Hide time inputs and disable them
          startInput.disabled = true;
          endInput.disabled = true;
          // Clear input values
          startInput.value = null;
          endInput.value = null;
          // Optionally, hide time input divs if shown
          // document.getElementById('start_time_div').style.display = 'none';
          // document.getElementById('end_time_div').style.display = 'none';
        }
      }
    </script>
    <script>
      function restrictMinutesTo30(input) {
        var time = input.value.split(":");
        var minutes = parseInt(time[1]);
        if (minutes < 15) {
          input.value = time[0] + ":00";
        } else if (minutes >= 15 && minutes < 45) {
          input.value = time[0] + ":30";
        } else {
          input.value = (parseInt(time[0]) + 1) + ":00";
        }
      }
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>