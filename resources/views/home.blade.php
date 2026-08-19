<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        {{env('APP_NAME')}} 
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        @vite(['resources/scss/light/assets/components/list-group.scss'])
        @vite(['resources/scss/dark/assets/components/list-group.scss'])

        @vite(['resources/scss/light/assets/widgets/modules-widgets.scss'])
        @vite(['resources/scss/dark/assets/widgets/modules-widgets.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <div class="row layout-top-spacing layout-spacing" id="cancel-row">
      <div class="col-12 col-md-8">
        <div class="widget">
          <div class="row">
            <!-- <div class="col-12 layout-spacing">
              <form method="GET" class="row">
              <div class="col-12">
                <label for="month" >Month</label>
                <select id="month" name="month" class="form-select" aria-label="Default select example">
                  <option value='Jun'>June</option>
                  <option value='Jul'>July</option>
                  <option value='Aug'>August</option>
                </select>
              </div>
              </form>
            </div> -->
            <div class="col-12 col-md-6 layout-spacing">
              <div class="widget widget-chart-three">
                <div class="widget-heading">
                  <div class="">
                    <h5 class="">{{$currentMonthName}}'s Attendance</h5>
                  </div>
                </div>

                <div class="widget-content">
                  <div id="attendance-statistics" class="" style="display: flex; justify-content: center;"></div>
                </div>
              </div>
            </div>
            <div class="col-12 col-md-6 layout-spacing">
              <div class="widget" style="margin-bottom: 25px;">
                <div class="widget-heading">
                    <h5 class="">Leave Applications</h5>
                </div>
                <div class="widget-content">
                  <ul class="list-group list-group-icons-meta">
                  @foreach($groupedLeaves as $status => $statusCount)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      {{ucwords($status)}}
                      <span class="badge bg-primary">{{$statusCount}} leave(s)</span>
                    </li>
                  @endforeach
                  </ul>
                </div>
              </div>
            </div>
            <div class="col-12 layout-spacing">
              <div class="widget widget-chart-three">
                <div class="widget-heading">
                  <div class="">
                    <h5 class="">Personal Monthly Attendance</h5>
                  </div>
                </div>
                <div class="widget-content">
                  <div id="monthly-attendance"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-4">
        <div class="widget" style="margin-bottom: 25px;">
          <div class="widget-heading">
            <h5>Announcement</h4>
          </div>
          <div class="widget-content">
            <ul class="list-group list-group-icons-meta">
              @foreach($notifications as $notification)
              <li class="list-group-item ">
                <div class="media">
                  <div class="d-flex me-3">
                    <div class="avatar-container">
                      <div class="avatar avatar-sm">
                          @if(isset($notification->created_by) && !empty($notification->created_by) && !empty($notification->createdBy->profile_image))
                            <img src="{{asset('/storage/images/'.$notification->createdBy->profile_image)}}" alt="avatar" class="rounded-circle  bs-tooltip" data-bs-original-title="{{$notification->createdBy->name}}">
                          @else
                            <img src="{{Vite::asset('resources/images/maysville-avatar.png')}}" alt="avatar" class="rounded-circle">
                          @endif
                      </div>
                    </div>
                  </div>
                  <div class="media-body">
                    <h6 class="tx-inverse">{{$notification->title}}</h6>
                    <p class="mg-b-0">{{$notification->short_descriptions}}</p>
                  </div>
                </div>
              </li>
              @endforeach
            </ul>
          </div>
        </div>
        <div class="widget">
          <div class="widget-heading">
              <h5 class="">Leave Balance</h5>
          </div>
          <div class="widget-content">
            <ul class="list-group">
                @foreach(Auth::user()->leaveBalances as $leaveBalance)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{$leaveBalance->leaveType->name}}
                    <span class="badge bg-primary">{{$leaveBalance->totalBalance()}} {{$leaveBalance->leaveType->balance_unit}}(s) remaining</span>
                </li>
                @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
    
    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
      <script src="{{asset('plugins/uuid/uuid4.min.js')}}"></script>
      <script src="{{asset('plugins/apex/apexcharts.min.js')}}"></script>
      <script>
        var ASdata = <?php echo json_encode($ASdata); ?>;
        var MAdata = <?php echo json_encode($MAdata); ?>;
      </script>
      @vite(['resources/assets/js/widgets/attendanceStatistics.js'])
      @vite(['resources/assets/js/widgets/monthlyAttendance.js'])
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>