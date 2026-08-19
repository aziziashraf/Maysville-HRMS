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

        @vite(['resources/scss/light/assets/components/accordions.scss'])
        @vite(['resources/scss/dark/assets/components/accordions.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <div class="row layout-top-spacing layout-spacing" id="cancel-row">
      <div class="col-12 col-md-8 layout-spacing">
        <div class="widget">
          @can('show-own-department-only')
          <div class="widget-heading">
            <div class="">
              <h5 class="">{{Auth::user()->department->department_name}}'s Department Dashboard</h5>
            </div>
          </div>
          @endcan
          <div class="row">
            @cannot('show-own-department-only')
            <div class="col-12 layout-spacing">
              <form method="GET" class="row" id="departmentForm">
              <label for="department_id" >Department's Dashboard</label>
                <div class="input-group mb-3">
                  <select id="department_id" name="department_id" class="form-select" aria-label="Default select example">
                    <option value=''>All Department</option>
                    @foreach($departments as $d)
                    <option value='{{$d->id}}' <?php echo $filter['department_id']==$d->id?'selected':''?> >{{$d->department_name}}</option>
                    @endforeach
                  </select>
                  <!-- <button class="btn btn-primary" type="submit" id="button-addon2">Submit</button> -->
                </div>
              </form>
            </div>
            @endcannot
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
              <div class="widget widget-chart-three">
                <div class="widget-heading">
                  <div class="">
                    <h5 class="">{{$currentMonthName}}'s Leave Type Distribution</h5>
                  </div>
                </div>

                <div class="widget-content">
                  <div id="leave-type-distribution" class="" style="display: flex; justify-content: center;"></div>
                </div>
              </div>
            </div>
            <div class="col-12 layout-spacing">
              <div class="widget widget-chart-three">
                <div class="widget-heading">
                  <div class="">
                    <h5 class="">Monthly Attendance</h5>
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

        @php
          $docsExpired  = $expiringDocuments->where('expiry_status', 'expired');
          $docsExpiring = $expiringDocuments->where('expiry_status', 'expiring');
        @endphp

        <div class="widget" style="margin-bottom: 25px;">
          <div class="widget-heading d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Certification &amp; Document Expiry</h5>
            @can('employee_document-index')
              <a href="{{ route('employee_document.index') }}" style="font-size: 12px;">View all</a>
            @endcan
          </div>
          <div class="widget-content">
            @if($expiringDocuments->isEmpty())
              <p class="text-muted mb-0" style="font-size: 13px;">
                Nothing expiring soon. All tracked employee documents are valid.
              </p>
            @else
              <ul class="list-group mb-3">
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Expired
                  <a href="{{ route('employee_document.index', ['status' => 'expired']) }}" class="badge bg-danger" style="text-decoration:none;">{{ $docsExpired->count() }}</a>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  Expiring soon
                  <a href="{{ route('employee_document.index', ['status' => 'expiring']) }}" class="badge bg-warning" style="text-decoration:none;">{{ $docsExpiring->count() }}</a>
                </li>
              </ul>

              <div style="max-height: 320px; overflow-y: auto;">
                @foreach($expiringDocuments->take(15) as $doc)
                  <div class="d-flex justify-content-between align-items-start py-2" style="border-bottom: 1px solid var(--border-color, #e0e6ed);">
                    <div style="min-width: 0;">
                      <div style="font-size: 13px; font-weight: 600;">{{ $doc->user->name ?? '-' }}</div>
                      <div class="text-muted" style="font-size: 12px;">
                        {{ $doc->display_title }}
                        @if($doc->expiry_date)
                          &middot; {{ $doc->expiry_date->format('d M Y') }}
                        @endif
                      </div>
                    </div>
                    <span class="badge badge-light-{{ $doc->expiry_badge_class }}" style="flex-shrink: 0; margin-left: 8px;">
                      @if($doc->expiry_status === 'expired')
                        Expired
                      @else
                        {{ $doc->days_to_expiry }}d
                      @endif
                    </span>
                  </div>
                @endforeach

                @if($expiringDocuments->count() > 15)
                  <div class="pt-2" style="font-size: 12px;">
                    <a href="{{ route('employee_document.index') }}">+ {{ $expiringDocuments->count() - 15 }} more</a>
                  </div>
                @endif
              </div>
            @endif
          </div>
        </div>

        <div class="widget" style="margin-bottom: 25px;">
          <div class="widget-heading">
            <h5>Application Request Pending</h4>
          </div>
          <div class="widget-content">
            <ul class="list-group">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Leave
                <span class="badge bg-primary">{{$leaveRequests}}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Claim
                <span class="badge bg-primary">{{$claimRequests}}</span>
              </li>
              <li class="list-group-item d-flex justify-content-between align-items-center">
                Overtime
                <span class="badge bg-primary">{{$overtimeRequests}}</span>
              </li>
            </ul>
          </div>
        </div>
        <div class="widget" style="margin-bottom: 25px;">
          <div class="widget-heading">
            <h5 class="">Daily Scan Summary</h5>
          </div>
          <div class="widget-content layout-spacing">
            @foreach($departmentDailyScan as $departmentName => $dailyScan)
              @php
                $ariaLabel = str_replace(' ', '_', strtolower($departmentName));
              @endphp
              <div class="card my-2">
                <div class="card-body px-3 py-3">
                <h6 class="card-title pb-2">{{$departmentName}} Department</h6>
                <div id="toggle{{$ariaLabel}}Accordion" class="accordion no-outer-spacing">
                      @foreach($dailyScan as $status => $nameList)
                      @php
                        $ariaLabel2 = $ariaLabel . '_' . str_replace(' ', '_', strtolower(str_replace('\'', '', strtolower($status))));
                      @endphp
                      <div class="card">
                        <div class="card-header" id="{{$ariaLabel2}}Heading">
                          <section class="mb-0 mt-0">
                            <div role="menu" class="collapsed d-flex justify-content-between align-items-start" data-bs-toggle="collapse" data-bs-target="#{{$ariaLabel2}}Accordion" aria-expanded="false" aria-controls="{{$ariaLabel2}}Accordion">
                                {{$status}}
                                @if($status == 'Scanned')
                                <span class="badge bg-success rounded-pill">{{count($nameList)}}</span>
                                @elseif($status == 'On Leave')
                                <span class="badge bg-warning rounded-pill">{{count($nameList)}}</span>
                                @elseif($status == 'Haven\'t Scan')
                                <span class="badge bg-danger rounded-pill">{{count($nameList)}}</span>
                                @else
                                <span class="badge bg-secondary rounded-pill">{{count($nameList)}}</span>
                                @endif
                            </div>
                          </section>
                        </div>
                        <div id="{{$ariaLabel2}}Accordion" class="collapse" aria-labelledby="{{$ariaLabel2}}Heading" data-bs-parent="#toggle{{$ariaLabel}}Accordion">
                          <div class="card-body" style="padding: 0px 10px 10px 10px;">
                            <ul class="list-group list-group-flush  list-group-numbered">
                              @foreach($nameList as $name)
                              <li class="list-group-item py-1">{{$name}}</li>
                              @endforeach
                            </ul>
                          </div>
                        </div>
                      </div>
                      @endforeach
                    </div>
                </div>
              </div>
            @endforeach
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
        var LTDdata = <?php echo json_encode($LTDdata); ?>;
      </script>
      @vite(['resources/assets/js/widgets/attendanceStatistics.js'])
      @vite(['resources/assets/js/widgets/monthlyAttendance.js'])
      @vite(['resources/assets/js/widgets/leaveTypeDistribution.js'])
      <script>
        // Get a reference to the form and select element
        const departmentForm = document.getElementById('departmentForm');
        const departmentSelect = document.getElementById('department_id');

        // Add a change event listener to the select element
        departmentSelect.addEventListener('change', function () {
          // Submit the form when the selection changes
          departmentForm.submit();
        });
      </script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>