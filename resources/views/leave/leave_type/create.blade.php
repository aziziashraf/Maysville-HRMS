<x-base-layout :scrollspy="false">
  @php
    if(isset($leave_type)){
      $title = 'Edit Leave Type';
    } else {
      $title = 'Add Leave Type';
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
    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss']) 
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

  <!-- BREADCRUMB -->
  <div class="page-meta">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('leave_type.index') }}">Leave Type</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('leave_type.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12  collayout-spacing">
        <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
      </div>
      <div id="basic" class="col-12 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>Details</h4>
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
              <input type="text" id="leave_type_id" name="leave_type_id" value="{{$leave_type->id ??''}}" hidden>
              <div class="form-group col-12 mb-4">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$leave_type->name ??''}}" required>
              </div>
              <div class="form-group col-12 mb-4">
                <label for="description">Description</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="Description.." value="{{$leave_type->description ??''}}">
              </div>
              <div class="form-group col-12 mb-4 d-flex align-items-center">
                <label for="label_color" class="my-0">Select a color: </label>
                <input type="color" class="mx-3" name="label_color" id="label_color" value="{{$leave_type->label_color ??''}}" required>
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label>For Confirmed Employees Only</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="confirmed_employees_only" id="confirmed_employees_only_yes" value="1" required {{ isset($leave_type) && $leave_type->confirmed_employees_only == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="confirmed_employees_only_yes">
                    Yes
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="confirmed_employees_only" id="confirmed_employees_only_no" value="0" required {{ isset($leave_type) && $leave_type->confirmed_employees_only == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="confirmed_employees_only_no">
                    No
                  </label>
                </div>
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label>Attachment Required</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="attachment_required" id="attachment_required_yes" value="1" required {{ isset($leave_type) && $leave_type->attachment_required == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="attachment_required_yes">
                    Yes
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="attachment_required" id="attachment_required_no" value="0" required {{ isset($leave_type) && $leave_type->attachment_required == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="attachment_required_no">
                    No
                  </label>
                </div>
              </div>
              <div class="form-group col-6 mb-4">
                <label>Balance Unit</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="balance_unit" id="balance_unit_hour" value="hour" {{ isset($leave_type) && $leave_type->balance_unit == 'hour' ? 'checked' : '' }}>
                  <label class="form-check-label" for="balance_unit_hour">
                    Hour
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="balance_unit" id="balance_unit_day" value="day" {{ isset($leave_type) && $leave_type->balance_unit == 'day' ? 'checked' : '' }}>
                  <label class="form-check-label" for="balance_unit_day">
                    Day
                  </label>
                </div>
              </div>
              <div class="form-group col-6 mb-4">
                <label>Renew Frequency</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="renew_freq" value="none" id="renew_freq_none" {{ isset($leave_type) && $leave_type->renew_freq == 'none' ? 'checked' : '' }}>
                  <label class="form-check-label" for="renew_freq_none">
                    Non-renewing
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="renew_freq" value="year" id="renew_freq_year" {{ isset($leave_type) && $leave_type->renew_freq == 'year' ? 'checked' : '' }}>
                  <label class="form-check-label" for="renew_freq_year">
                    Yearly
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="renew_freq" value="month" id="renew_freq_month" {{ isset($leave_type) && $leave_type->renew_freq == 'month' ? 'checked' : '' }}>
                  <label class="form-check-label" for="renew_freq_month">
                    Monthly
                  </label>
                </div>
              </div>
              <div class="form-group col-12 mb-4">
                <label for="default_amount">Default Amount</label>
                <input type="number" step="1" class="form-control" id="default_amount" name="default_amount" placeholder="Name.." value="{{$leave_type->default_amount ??''}}" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
              </div>
              <div class="form-group col-12 mb-4">
                <label>Limit per Leave</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="limit_per_leave" id="limit_per_leave_enable" value="1" required {{ isset($leave_type) && $leave_type->limit_per_leave == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="limit_per_leave_enable">
                    Enable
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="limit_per_leave" id="limit_per_leave_disable" value="0" required {{ isset($leave_type) && $leave_type->limit_per_leave == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="limit_per_leave_disable">
                    Disable
                  </label>
                </div>
              </div>
              <div class="form-group col-12 mb-4 limit" hidden>
                <label for="limit_per_leave_amount" id="limit_per_leave_amount_label">Limit Amount</label>
                <input type="number" step="1" class="form-control" id="limit_per_leave_amount" name="limit_per_leave_amount" placeholder="Percentage to Carry Forward (%)" value="{{ $leave_type->limit_per_leave_amount ?? '' }}" oninput="this.value = this.value.replace(/[^\d]/g, ''); if (this.value < 1 || this.value > 100) this.value = '';" disabled>
              </div>
              <div class="form-group col-12 mb-4">
                <label>Back Dated Application</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="back_dated" id="back_dated_enable" value="1" required {{ isset($leave_type) && $leave_type->back_dated == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="back_dated_enable">
                    Enable
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="back_dated" id="back_dated_disable" value="0" required {{ isset($leave_type) && $leave_type->back_dated == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="back_dated_disable">
                    Disable
                  </label>
                </div>
              </div>
              <div class="form-group col-12 mb-4 backDated" hidden>
                <label for="back_dated_days_limit">Back Dated Limit (Days)</label>
                <input type="number" step="1" class="form-control" id="back_dated_days_limit" name="back_dated_days_limit" placeholder="Percentage to Carry Forward (%)" value="{{ $leave_type->back_dated_days_limit ?? '' }}" oninput="this.value = this.value.replace(/[^\d]/g, ''); if (this.value < 1 || this.value > 100) this.value = '';" disabled>
              </div>
            </div>

          </div>
        </div>
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>Carry Forward</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">

            <div class="row">
              <div class="form-group col-12 mb-4">
                <label>Enable</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="carry_forward" id="carry_forward_yes" value="1" required {{ isset($leave_type) && $leave_type->carry_forward == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="carry_forward_yes">
                    Yes
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="carry_forward" id="carry_forward_no" value="0" required {{ isset($leave_type) && $leave_type->carry_forward == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="carry_forward_no">
                    No
                  </label>
                </div>
              </div>
              <div class="form-group col-12 mb-4 carry-forward" hidden>
                <label for="carry_forward_limit">Percentage to Carry Forward (%)</label>
                <input type="number" step="1" class="form-control" id="carry_forward_limit" name="carry_forward_limit" placeholder="Percentage to Carry Forward (%)" value="{{ $leave_type->carry_forward_limit ?? '' }}" oninput="this.value = this.value.replace(/[^\d]/g, ''); if (this.value < 1 || this.value > 100) this.value = '';"  required disabled>
              </div>
              <div class="form-group col-12 col-md-6 mb-4 carry-forward" hidden>
                <label for="carry_forward_timeframe_type">Timeframe</label>
                <select class="form-select" id="carry_forward_timeframe_type" name="carry_forward_timeframe_type" required disabled>
                  <option selected disabled>-- Select Timeframe --</option>
                  <option value="month" {{ isset($leave_type) && $leave_type->carry_forward_timeframe_type == 'month' ? 'selected' : '' }}>Month</option>
                  <option value="week" {{ isset($leave_type) && $leave_type->carry_forward_timeframe_type == 'week' ? 'selected' : '' }}>Week</option>
                  <option value="day" {{ isset($leave_type) && $leave_type->carry_forward_timeframe_type == 'day' ? 'selected' : '' }}>Day</option>
                </select>
              </div>
              <div class="form-group col-12 col-md-6 mb-4 carry-forward" hidden>
                <label for="carry_forward_timeframe_value">Timeframe Amount</label>
                <input type="number" step="1" class="form-control" id="carry_forward_timeframe_value" name="carry_forward_timeframe_value" value="{{ $leave_type->carry_forward_timeframe_value ?? '' }}" oninput="this.value = this.value.replace(/[^\d]/g, '');" required disabled>
              </div>
            </div>

          </div>
        </div>
        @if(isset($leave_type))
        <div class="statbox widget box box-shadow">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>{{ $leave_type->name }} Balance Tier</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">
            <a href="{{ route('leave_balance_tier.create',$leave_type->id) }}" class="btn btn-primary mb-2 me-4">Add Balance Tier</a>
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th class="text-center" scope="col">Years of Service</th>
                    <th class="text-center" scope="col">Amount</th>
                    <th class="text-center" scope="col">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($leave_type->leaveBalanceTiers as $row)
                  <tr>
                    <td class="text-center">{{$row->years_of_service}}</td>
                    <td class="text-center">{{$row->amount}}</td>
                    <td class="text-center">
                      <div class="action-btns">
                        <a href="{{ route('leave_balance_tier.edit',$row) }}" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Edit">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        </a>
                        <a onclick="if(confirm('Are you sure you want to delete this leave balance tier?')){ window.location.href='{{ route('leave_balance_tier.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
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
    </div>
  </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    @vite(['resources/assets/js/custom.js'])
    <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
    <script>
      // Get references to the radio buttons and fields
      const carryForwardYes = document.getElementById('carry_forward_yes');
      const carryForwardLimit = document.getElementById('carry_forward_limit');
      const carryForwardTimeframeType = document.getElementById('carry_forward_timeframe_type');
      const carryForwardTimeframeValue = document.getElementById('carry_forward_timeframe_value');
      var carryForwardDiv = $('.carry-forward');

      $(document).ready(function() {
        if (carryForwardYes.checked) {
          carryForwardLimit.removeAttribute('disabled');
          carryForwardTimeframeType.removeAttribute('disabled');
          carryForwardTimeframeValue.removeAttribute('disabled');
          carryForwardDiv.removeAttr('hidden');
        } else {
          carryForwardLimit.setAttribute('disabled', 'disabled');
          carryForwardTimeframeType.setAttribute('disabled', 'disabled');
          carryForwardTimeframeValue.setAttribute('disabled', 'disabled');
          carryForwardDiv.attr('hidden', 'hidden');
        }
      });

      // Add an event listener to the radio button
      carryForwardYes.addEventListener('change', function() {
        if (this.checked) {
          carryForwardLimit.removeAttribute('disabled');
          carryForwardTimeframeType.removeAttribute('disabled');
          carryForwardTimeframeValue.removeAttribute('disabled');
          carryForwardDiv.removeAttr('hidden');
        }
      });

      const carryForwardNo = document.getElementById('carry_forward_no');

      carryForwardNo.addEventListener('change', function() {
        if (this.checked) {
          carryForwardLimit.setAttribute('disabled', 'disabled');
          carryForwardTimeframeType.setAttribute('disabled', 'disabled');
          carryForwardTimeframeValue.removeAttribute('disabled');
          carryForwardDiv.attr('hidden', 'hidden');
        }
      });
    </script>
    <script>
      // Get references to the radio buttons and fields
      const limitPerLeaveEnable = document.getElementById('limit_per_leave_enable');
      const limitPerLeaveAmount = document.getElementById('limit_per_leave_amount');
      var limitDiv = $('.limit');

      $(document).ready(function() {
        if (limitPerLeaveEnable.checked) {
          limitPerLeaveAmount.removeAttribute('disabled');
          limitDiv.removeAttr('hidden');
        } else {
          limitPerLeaveAmount.setAttribute('disabled', 'disabled');
          limitDiv.attr('hidden', 'hidden');
        }
      });

      // Add an event listener to the radio button
      limitPerLeaveEnable.addEventListener('change', function() {
        if (this.checked) {
          limitPerLeaveAmount.removeAttribute('disabled');
          limitDiv.removeAttr('hidden');
        }
      });

      const limitPerLeaveDisable = document.getElementById('limit_per_leave_disable');

      limitPerLeaveDisable.addEventListener('change', function() {
        if (this.checked) {
          limitPerLeaveAmount.setAttribute('disabled', 'disabled');
          limitDiv.attr('hidden', 'hidden');
        }
      });
    </script>
    <script>
      // Get references to the radio buttons and fields
      const backDatedEnable = document.getElementById('back_dated_enable');
      const backDatedDaysLimit = document.getElementById('back_dated_days_limit');
      var backDatedDiv = $('.backDated');

      $(document).ready(function() {
        if (backDatedEnable.checked) {
          backDatedDaysLimit.removeAttribute('disabled');
          backDatedDiv.removeAttr('hidden');
        } else {
          backDatedDaysLimit.setAttribute('disabled', 'disabled');
          backDatedDiv.attr('hidden', 'hidden');
        }
      });

      // Add an event listener to the radio button
      backDatedEnable.addEventListener('change', function() {
        if (this.checked) {
          backDatedDaysLimit.removeAttribute('disabled');
          backDatedDiv.removeAttr('hidden');
        }
      });

      const backDatedDisable = document.getElementById('back_dated_disable');

      backDatedDisable.addEventListener('change', function() {
        if (this.checked) {
          backDatedDaysLimit.setAttribute('disabled', 'disabled');
          backDatedDiv.attr('hidden', 'hidden');
        }
      });
    </script>
    <script>
      const balanceUnitRadios = document.querySelectorAll('input[name="balance_unit"]');
      const limitPerLeaveAmountLabel = document.querySelector('#limit_per_leave_amount_label');

      $(document).ready(function() {
        balanceUnitRadios.forEach(function(radio) {
          if (radio.checked) {
            if (radio.value === 'hour') {
              limitPerLeaveAmountLabel.textContent = 'Limit Amount (Hour)';
            } else if (radio.value === 'day') {
              limitPerLeaveAmountLabel.textContent = 'Limit Amount (Day)';
            } else {
              limitPerLeaveAmountLabel.textContent = 'Limit Amount';
            }
          }
        });
      });

      balanceUnitRadios.forEach(function(radio) {
          radio.addEventListener('click', function() {
              if (radio.value === 'hour') {
                  limitPerLeaveAmountLabel.textContent = 'Limit Amount (Hour)';
              } else if (radio.value === 'day') {
                  limitPerLeaveAmountLabel.textContent = 'Limit Amount (Day)';
              } else {
                  limitPerLeaveAmountLabel.textContent = 'Limit Amount';
              }
          });
      });
  </script>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>