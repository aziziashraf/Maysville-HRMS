<x-base-layout :scrollspy="false">

  <x-slot:pageTitle>
    Add Leave | {{ env('APP_NAME') }}
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
        <li class="breadcrumb-item"><a href="{{ route('leave.requestIndex') }}">Leave Approval</a></li>
        <li class="breadcrumb-item active" aria-current="page">Add Leave</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form id="leave_form" action="{{ route('leave.requestStore') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12 collayout-spacing">
        <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right">Save</button>
      </div>
      <div id="basic" class="col-12 col-md-8 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Leave Details</h4>
              </div>
            </div>
            @if($errors->any())
              @foreach ($errors->all() as $error)
                <div class="alert alert-light-danger alert-dismissible fade show border-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
              @endforeach
            @endif
          </div>
          <div class="widget-content widget-content-area">
            <div class="row">
              <div class="col-12">
                <input type="text" id="leave_id" name="leave_id" value="{{$leave->id ?? ''}}" hidden>
              </div>
              <div class="col-12 form-group mb-4">
                <label>Employee</label>
                <select id="user_id" name="user_id">
                  @foreach($employees as $employee)
                  <option value='{{$employee->id}}'>{{$employee->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 form-group mb-4">
                <label for="leave_type_id">Leave Type</label>
                <select id="leave_type_id" name="leave_type_id" class="form-select">
                  <option selected> -- Select --</option>
                  @foreach($leave_type as $lt)
                  <option value='{{$lt->id}}' <?php echo isset($leave->leave_type_id) && $leave->leave_type_id == $lt->id ? 'selected' : '' ?>>{{$lt->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 form-group mb-4">
                <label for="date_range">Date Range</label>
                <input id="date_range" name="date_range" class="form-control flatpickr flatpickr-input active" placeholder="Date Range.." type="text" value="{{$leave->date_range ??''}}">
              </div>
              <div id="start_time_div" class="col-12 col-md-6 form-group mb-4">
                <label for="start_time">Start Time</label>
                <input
                  id="start_time"
                  class="form-control"
                  type="time"
                  placeholder="Select Time.."
                  name="start_time"
                  onchange="restrictMinutesTo30(this)"
                  step="1800"
                  value="{{$leave->start_time ??''}}"
                >
              </div>
              <div id="end_time_div" class="col-12 col-md-6 form-group mb-3">
                <label for="end_time">End Time</label>
                <input
                  id="end_time"
                  class="form-control"
                  type="time"
                  placeholder="Select Time.."
                  name="end_time"
                  onchange="restrictMinutesTo30(this)"
                  step="1800"
                  value="{{$leave->end_time ??''}}"
                >
              </div>
              <div class="col-12 mb-4">
                <label for="attachment">Attachment</label>
                @if(!isset($leave) || $leave->status == 'draft')
                <div class="input-group mb-3">
                  <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                  @if(isset($leave) && $leave->status == 'draft')
                  <button class="btn btn-primary" type="button" id="button-addon2" onclick="addAttachment()">Add Attachment</button>
                  @endif
                </div>
                @endif
                
                @if (isset($leave))
                  <p><small class="text-muted">Current Attachments:</small></p>
                  <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th scope="col">Filename</th>
                          <th class="text-center" scope="col">Action</th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse ($leave->attachments as $row)
                        <tr>
                          <td>{{$row->filename}}</td>
                          <td class="text-center">
                            <div class="action-btns">
                              <a href="{{ route('attachment.show',$row) }}"  target="_blank" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="View">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                              </a>
                              @if($leave->status == 'draft')
                              <a onclick="if(confirm('Are you sure you want to delete this attachment?')){ window.location.href='{{ route('attachment.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                              </a>
                              @endif
                            </div>
                          </td>
                        </tr>
                        @empty
                        <tr>
                          <td colspan="2" class="text-center">No attachments</td>
                        </tr>
                        @endforelse
                      </tbody>
                    </table>
                  </div>
                @endif
              </div>
            </div>

          </div>
        </div>
      </div>
      <div id="basic" class="col-12 col-md-4 collayout-spacing">
        <div class="statbox widget box box-shadow">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>Status</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">
            <div class="form-group mb-4">
              <label>Status:</label>
              <select id="status" name="status" class="form-control">
                <option selected> -- Select --</option>
                @foreach($status as $s)
                <option value='{{$s}}' <?php echo isset($leave->status) && $leave->status == $s ? 'selected' : '' ?>>{{ucwords($s)}}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group mb-4">
              <label>Remarks (Optional)</label>
              <textarea class="form-control" id="status_remarks" name="status_remarks" placeholder="Remarks" rows="5"></textarea>
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
        var startInput = document.getElementById('start_time');
        var endInput = document.getElementById('end_time');

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
    <script src="{{asset('plugins/tomSelect/tom-select.base.js')}}"></script>
    <script>
      new TomSelect("#user_id",{
        preload: true,
        allowEmptyOption: true,
        items: [],
      });
    </script>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>