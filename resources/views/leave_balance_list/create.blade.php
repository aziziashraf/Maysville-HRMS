<x-base-layout :scrollspy="false">
  @php 
    if(isset($leaveBalanceList)){
      $title = 'Edit Leave Balance List';
    }else{
      $title = 'Add Leave Balance List';
    }
    $leaveBalance = $leaveBalanceList->leaveBalance ?? $leaveBalance;
    $leaveType = $leaveBalance->leaveType;
  @endphp
  <x-slot:pageTitle>
    {{$title}} | {{ env('APP_NAME') }}
  </x-slot>

  <!-- BEGIN GLOBAL MANDATORY STYLES -->
  <x-slot:headerFiles>
    <!--  BEGIN CUSTOM STYLE FILE  -->
    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss'])

    <link rel="stylesheet" href="{{asset('plugins/flatpickr/flatpickr.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/noUiSlider/nouislider.min.css')}}">
    @vite(['resources/scss/light/plugins/flatpickr/custom-flatpickr.scss'])
    @vite(['resources/scss/dark/plugins/flatpickr/custom-flatpickr.scss'])
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

  <!-- BREADCRUMB -->
  <div class="page-meta">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('leave_balance_list.index', $leaveBalance) }}">Event Type</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('leave_balance_list.store') }}" method="post" enctype="multipart/form-data">
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
              <input type="text" id="leave_type_id" name="leave_type_id" value="{{$leaveType->id}}" hidden>
              @if(isset($leaveBalanceList))
              <input type="text" id="leave_balance_list_id" name="leave_balance_list_id" value="{{$leaveBalanceList->id}}" hidden>
              @else
              <input type="text" id="leave_balance_id" name="leave_balance_id" value="{{$leaveBalance->id}}" hidden>
              @endif
              <div class="form-group col-12 mb-4">
                <label for="name">Balance {{ucwords($leaveType->balance_unit)}}</label>
                <input type="number" step="0.5" class="form-control" id="balance" name="balance" placeholder="Balance" value="{{$leaveBalanceList->balance ??''}}">
              </div>
              @if($leaveType->renew_freq != 'none')
              @if($leaveType->renew_freq == 'month')
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="month">Month</label>
                <select class="form-select" id="month" name="month">>
                  <option value="" selected disabled>--Select Month--</option>
                  <option value="1" {{isset($leaveBalanceList) && $leaveBalanceList->month == 1 ? 'selected' : ''}}>January</option>
                  <option value="2" {{isset($leave_balance_id) && $leaveBalanceList->month == 2 ? 'selected' : ''}}>February</option>
                  <option value="3" {{isset($leaveBalanceList) && $leaveBalanceList->month == 3 ? 'selected' : ''}}>March</option>
                  <option value="4" {{isset($leaveBalanceList) && $leaveBalanceList->month == 4 ? 'selected' : ''}}>April</option>
                  <option value="5" {{isset($leaveBalanceList) && $leaveBalanceList->month == 5 ? 'selected' : ''}}>May</option>
                  <option value="6" {{isset($leaveBalanceList) && $leaveBalanceList->month == 6 ? 'selected' : ''}}>June</option>
                  <option value="7" {{isset($leaveBalanceList) && $leaveBalanceList->month == 7 ? 'selected' : ''}}>July</option>
                  <option value="8" {{isset($leaveBalanceList) && $leaveBalanceList->month == 8 ? 'selected' : ''}}>August</option>
                  <option value="9" {{isset($leaveBalanceList) && $leaveBalanceList->month == 9 ? 'selected' : ''}}>September</option>
                  <option value="10" {{isset($leaveBalanceList) && $leaveBalanceList->month == 10 ? 'selected' : ''}}>October</option>
                  <option value="11" {{isset($leaveBalanceList) && $leaveBalanceList->month == 11 ? 'selected' : ''}}>November</option>
                  <option value="12" {{isset($leaveBalanceList) && $leaveBalanceList->month == 12 ? 'selected' : ''}}>December</option>
                </select>
              </div>
              @endif
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="year">Year</label>
                <select class="form-select" id="year" name="year">
                  <option value="" selected disabled>--Select Year--</option>
                  @foreach($yearArray as $year)
                  <option value="{{$year}}" {{isset($leaveBalanceList) && $leaveBalanceList->year == $year ? 'selected' : ''}}>{{$year}}</option>
                  @endforeach
                </select>
              </div>
              @endif
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="expiry_date">Expiry Date</label>
                <input type="text" class="form-control flatpickr flatpickr-input  basicFlatpickr active" id="expiry_date" name="expiry_date" placeholder="Expiry Date.." value="{{$leaveBalanceList->expiry_date ??''}}">
              </div>
              @isset($leaveBalanceList)
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="status">Status</label>
                <select class="form-select" id="status" name="status">
                  <option value="" selected disabled>--Select Status--</option>
                  <option value="1" {{$leaveBalanceList->status == 1 ? 'selected' : ''}}>Active</option>
                  <option value="0" {{$leaveBalanceList->status == 0 ? 'selected' : ''}}>Expired</option>
                </select>
              </div>
              @endisset
              <div class="form-group col-12 mb-4">
                <label for="description">Description</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="Description" value="{{$leaveBalanceList->description ??''}}">
              </div>
            </div>
          </div>
        </div>
        @isset($leaveBalanceList)
        <div class="statbox widget box box-shadow">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>Balance Log</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th class="text-center" scope="col">Reason</th>
                    <th class="text-center" scope="col">Balance Before</th>
                    <th class="text-center" scope="col">Balance After</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($leaveBalanceList->leaveBalanceListLogs as $row)
                  <tr>
                    <td class="text-center">{{ $row->reason }}</td>
                    <td class="text-center">{{ $row->balance_before }}</td>
                    <td class="text-center">{{ $row->balance_after }}</td>
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
        @endisset
      </div>
    </div>
  </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
    <script>
      var f1 = flatpickr(document.querySelectorAll('.basicFlatpickr'));
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>