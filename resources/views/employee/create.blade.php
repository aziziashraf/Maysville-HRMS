<x-base-layout :scrollspy="false">

  @php
    if($employee){
      $title = "Edit Employee";
    }else{
      $title = "Add Employee";
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
            <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form action="{{ route('employee.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
          </div>
          <div id="basic" class="col-12 col-md-8 collayout-spacing">
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Basic Info</h4>
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
                    <input type="text" class="form-control" id="employee_id" name="employee_id" value="{{$employee->id ??''}}" hidden>
                    <div class="form-group mb-4">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$employee->name ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="email">Email</label>
                      <input type="email" class="form-control" id="email" name="email" placeholder="Email.." value="{{$employee->email ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="contact_no">Phone No.</label>
                      <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Phone Number.." value="{{$employee->contact_no ??''}}">
                    </div>
                    <div class="form-group mb-4">
                      <label for="staff_id">Staff ID</label>
                      <input type="text" class="form-control" id="staff_id" name="staff_id" placeholder="Staff ID.." value="{{$employee->staff_id ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="card_id">Card ID</label>
                      <input type="text" class="form-control" id="card_id" name="card_id" placeholder="Card ID.." value="{{$employee->card_id ??''}}">
                    </div>
                    <div class="form-group mb-4">
                      <label for="department_id">Department </label>
                      <select id="department_id" name="department_id" class="form-select" required>
                        <option disabled selected> -- Select --</option>
                        @foreach($department as $d)
                        <option value='{{$d->id}}' <?php echo isset($employee->department_id) && $employee->department_id == $d->id ? 'selected' : '' ?>>{{$d->department_name}}</option>
                        @endforeach
                      </select>
                    </div>

                    <div class="form-group mb-4">
                      <label for="position_id">Position</label>
                      <select id="position_id" name="position_id" class="form-select" required>
                        <option disabled selected> -- Select --</option>
                        @if(isset($employee->department_id))
                        @foreach($employee->department->position as $p)
                        <option value='{{$p->id}}' <?php echo isset($employee->position_id) && $employee->position_id == $p->id ? 'selected' : '' ?>>{{$p->name}}</option>
                        @endforeach
                        @endif
                      </select>
                    </div>
                  </div>
                </div>

              </div>
            </div>
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Employment Details</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">

                <div class="row">
                  <div class="col-12">
                    <div class="form-group mb-4">
                      <label for="start_date">Probation Start Date</label>
                      <input type="text" class="form-control flatpickr flatpickr-input basicFlatpickr active" id="start_date" name="start_date" placeholder="Probation Start Date.." value="{{$employee->start_date ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="confirmed_date">Confirmed Start Date</label>
                      <input type="text" class="form-control flatpickr flatpickr-input  basicFlatpickr active" id="confirmed_date" name="confirmed_date" placeholder="Confirmed Start Date.." value="{{$employee->confirmed_date ??''}}">
                    </div>
                    <div class="form-group mb-4">
                      <label for="resigned_date">Employment End Date</label>
                      <input type="text" class="form-control flatpickr flatpickr-input  basicFlatpickr active" id="resigned_date" name="resigned_date" placeholder="Employment End Date.." value="{{$employee->resigned_date ??''}}">
                    </div>
                  </div>
                </div>

              </div>
            </div>
            @if(Auth::user()->isAn('management', 'superadmin') && $employee)
            <div class="statbox widget box box-shadow">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Leave Balances</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th class="text-center" scope="col">Leave Type</th>
                        <th class="text-center" scope="col">Total Balance</th>
                        <th class="text-center" scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($employee->leaveBalances as $row)
                      <tr>
                        <td class="text-center">{{ ucwords($row->leaveType->name) }}</td>
                        <td class="text-center">{{ $row->totalBalance() }} {{$row->leaveType->balance_unit}}(s)</td>
                        <td class="text-center">
                          <div class="action-btns">
                            <a href="{{ route('leave_balance_list.index',$row) }}" class="action-btn btn-view bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="View">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </a>
                          </div>
                        </td>
                      </tr>
                      @empty
                      <tr>
                        <td colspan="3" class="text-center">No data available</td>
                      </tr>
                      @endforelse
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            @endif
          </div>
          <div id="basic" class="col-12 col-md-4  collayout-spacing">
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Settings</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">
                <div class="form-group mb-4">
                  <label>Status</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo isset($employee->is_active) && $employee->is_active == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_active">
                      Active
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_block" value="0" <?php echo isset($employee->is_active) && $employee->is_active == 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_block">
                      Block
                    </label>
                  </div>
                </div>
                <div class="form-group mb-4">
                  <label>Lift Access</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ckbCheckAllLift">
                    <label class="form-check-label" for="status">
                      All
                    </label>
                  </div>
                  @foreach($lift_access as $b)
                  <div class="form-check">
                    <input class="form-check-input liftClass" type="checkbox" name="lift_access[]" value="{{$b->id}}" <?php echo isset($employee->lift_access_floor) && in_array($b->id, $employee->lift_access_floor) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="access">
                      {{$b->access_level}}
                    </label>
                  </div>
                  @endforeach
                </div>
                <div class="form-group mb-4">
                  <label>Access</label>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="ckbCheckAllDoor">
                    <label class="form-check-label" for="access">
                      All
                    </label>
                  </div>
                  @foreach($access as $a)
                  <div class="form-check">
                    <input class="form-check-input DoorClass" type="checkbox" name="access[]" value="{{$a->access_name}}" <?php echo isset($employee->all_user_access) && in_array($a->access_name, $employee->all_user_access) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="access">
                      {{$a->access_name}}
                    </label>
                  </div>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="statbox widget box box-shadow">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Leave Notification Settings</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">
                <div class="form-group mb-4">
                  <label for="leave_reviewers">Leave Reviewer</label>
                  <select id="leave_reviewers" name="leave_reviewers[]" multiple class="form-select">
                    @foreach($leaveReviewers as $lr)
                    <option value='{{$lr->id}}' @if(!empty($employee) && is_object($employee) && !empty($employee->leave_reviewers) && in_array($lr->id, $employee->leave_reviewers)) selected @endif>{{$lr->name}}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group mb-4">
                  <label for="leave_approvers">Leave Approver</label>
                  <select id="leave_approvers" name="leave_approvers[]" multiple class="form-select">
                    @foreach($leaveApprovers as $la)
                    <option value='{{$la->id}}' @if(!empty($employee) && is_object($employee) && !empty($employee->leave_approvers) && in_array($la->id, $employee->leave_approvers)) selected @endif>{{$la->name}}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
    <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
    <script>
      var f1 = flatpickr(document.querySelectorAll('.basicFlatpickr'));
    </script>
    <script>
      $(document).ready(function() {
        $("#ckbCheckAllLift").click(function() {
          $(".liftClass").prop('checked', $(this).prop('checked'));
        });

        $("#ckbCheckAllDoor").click(function() {
          $(".DoorClass").prop('checked', $(this).prop('checked'));
        });
      });
    </script>
    <script>
      $(document).ready(function() {
        // Listen for changes on the department dropdown
        $('#department_id').on('change', function() {
          // Get the selected department ID
          var departmentId = $(this).val();
          var position_id = <?php echo isset($employee->position_id) ? $employee->position_id : 'null'; ?>;

          // Make an AJAX request to get the positions for the selected department
          $.ajax({
            url: '{{ route("position.get-position", ":department_id") }}'
              .replace(':department_id', departmentId),
            type: 'GET',
            success: function(response) {
              // Clear the position dropdown
              $('#position_id').html('<option disabled selected> -- Select --</option>');

              // Loop through the positions and add them to the dropdown
              $.each(response, function(index, position) {
                var selected = '';
                if (position_id !== null && position_id == position.id) {
                  selected = 'selected';
                }
                var option = '<option value="' + position.id + '" ' + selected + '>' + position.name + '</option>';
                $('#position_id').append(option);
              });
            }
          });
        });
      });
    </script>
    <script src="{{asset('plugins/tomSelect/tom-select.base.js')}}"></script>
		<script>
			new TomSelect("#leave_approvers",{
			});
      new TomSelect("#leave_reviewers",{
			});
		</script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>