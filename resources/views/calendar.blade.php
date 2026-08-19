<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        {{env('APP_NAME')}} | Calendar
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        <link rel="stylesheet" href="{{asset('plugins/fullcalendar/fullcalendar.min.css')}}">
        @vite(['resources/scss/light/plugins/fullcalendar/custom-fullcalendar.scss'])
        @vite(['resources/scss/light/assets/components/modal.scss'])
        @vite(['resources/scss/light/assets/components/list-group.scss'])

        @vite(['resources/scss/dark/plugins/fullcalendar/custom-fullcalendar.scss'])
        @vite(['resources/scss/dark/assets/components/modal.scss'])
        @vite(['resources/scss/dark/assets/components/list-group.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->
    
    <div class="row layout-top-spacing layout-spacing" id="cancel-row">
        <div class="col-12 col-md-8">
            <div class="layout-spacing">
                <div class="calendar-container">
                    <div class="calendar"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Upcoming Events</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">
                <ul class="list-group list-group-icons-meta">
                  @foreach($events as $event)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{$event->name}}
                    <span class="badge bg-primary">
                      {{$event->date_from}} 
                      @if ($event->date_to !== null)
                        to {{$event->date_to}}
                      @endif
                    </span>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Employees on Leave</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">
                <ul class="list-group">
                  @foreach($leaves as $leave)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    {{$leave->user->name}}
                    <span class="badge bg-dark">
                      {{$leave->start_date}} 
                      @if ($leave->end_date !== null)
                        to {{$leave->end_date}}
                      @endif
                    </span>
                  </li>
                  @endforeach
                </ul>
              </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <div class="row">
                        <div class="col-md-12">
                            <p id="event-title"></p>
                        </div>

                        <div class="col-md-12 d-none">
                            <div class="">
                                <label class="form-label">Enter Start Date</label>
                                <input id="event-start-date" type="text" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-12 d-none">
                            <div class="">
                                <label class="form-label">Enter End Date</label>
                                <input id="event-end-date" type="text" class="form-control">
                            </div>
                        </div>
                        
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn" data-bs-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-success btn-update-event" data-fc-event-public-id="">Update changes</button>
                    <button type="button" class="btn btn-primary btn-add-event">Add Event</button> -->
                </div>
            </div>
        </div>
    </div>

    
    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        
        <script src="{{asset('plugins/fullcalendar/fullcalendar.min.js')}}"></script>
        <script src="{{asset('plugins/uuid/uuid4.min.js')}}"></script>
        <script src="{{asset('plugins/fullcalendar/custom-fullcalendar.js')}}"></script>
    
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>