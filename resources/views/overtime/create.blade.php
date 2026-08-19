<x-base-layout :scrollspy="false">
  @php
    if(isset($overtime)){
      $title = 'Edit Overtime';
    }else{
      $title = 'Add Overtime';
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

    <!-- <link rel="stylesheet" href="{{asset('plugins/notification/snackbar/snackbar.min.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/sweetalerts2/sweetalerts2.css')}}"> -->
    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    <!-- @vite(['resources/scss/light/plugins/sweetalerts2/custom-sweetalert.scss'])
    @vite(['resources/scss/light/plugins/notification/snackbar/custom-snackbar.scss']) -->
    @vite(['resources/scss/dark/assets/elements/alert.scss'])        
    <!-- @vite(['resources/scss/dark/plugins/sweetalerts2/custom-sweetalert.scss'])
    @vite(['resources/scss/dark/plugins/notification/snackbar/custom-snackbar.scss']) -->
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

      <!-- BREADCRUMB -->
      <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('overtime.index') }}">Overtime Application</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form id="overtime_form" action="{{ route('overtime.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            @if(isset($overtime))
              @if(!in_array($overtime->status, ['cancelled', 'rejected']))
                <a class="btn btn-danger mb-2 me-2" style="float:right" onclick="cancel()">Cancel</a>
              @endif

              @if($overtime->status == 'pre_reviewed')
                <a class="btn btn-primary mb-2 me-2" style="float:right" onclick="saveSubmit()">Submit</a>
              @endif
            @endif
            @if(!isset($overtime) || $overtime->status == 'draft')
            <a class="btn btn-primary mb-2 me-2" style="float:right" onclick="saveRequest()">Request</a>
            <a class="btn btn-info mb-2 me-2" style="float:right" onclick="saveDraft()">Save as Draft</a>
            @endif
          </div>
          <div id="basic" class="col-12 collayout-spacing">
            <div class="statbox widget box box-shadow">
              <div class="widget-header">
                <div class="row">
                  <div class="col-12">
                    <h4>Overtime Details</h4>
                  </div>
                </div>
                @if(session()->has('error'))
                  <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-4" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ session()->get('error') }}. </div>
                @endif
                @if(session()->has('success'))
                  <div class="alert alert-light-success alert-dismissible fade show border-0 mb-4" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Success!</strong> {{ session()->get('success') }}. </div>
                @endif

                @if($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-light-danger alert-dismissible fade show border-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
                    @endforeach
                @endif
              </div>

              <div class="widget-content widget-content-area">

                <div class="row">
                  <div class="col-12">
                    <input type="text" id="overtime_id" name="overtime_id" value="{{$overtime->id ?? ''}}" hidden>
                    <input type="text" id="user_id" name="user_id" value="{{$overtime->user_id ?? auth()->user()->id}}" hidden>
                    <input type="text" id="status" name="status" value="{{$overtime->status ??''}}" hidden>
                  </div>
                  @isset($overtime)
                  <div class="col-12 form-group mb-4">
                    <label for="status">Status:
                      @if($overtime->status == 'draft')
                      <span class="badge badge-info">Draft</span>
                      @elseif($overtime->status == 'requested')
                      <span class="badge badge-primary">Requested</span>
                      @elseif($overtime->status == 'pre_reviewed')
                      <span class="badge badge-success">Pre Reviewed</span>
                      @elseif($overtime->status == 'submitted')
                      <span class="badge badge-primary">Submitted</span>
                      @elseif($overtime->status == 'reviewed')
                      <span class="badge badge-success">Reviewed</span>
                      @elseif($overtime->status == 'approved')
                      <span class="badge badge-success">Approved</span>
                      @elseif($overtime->status == 'cancelled')
                      <span class="badge badge-danger">Cancelled</span>
                      @elseif($overtime->status == 'rejected')
                      <span class="badge badge-danger">Rejected</span>
                      @endif
                    </label>
                  </div>
                  @endisset
                  <div class="col-12 form-group mb-4">
                    <label for="reasons">Reasons</label>
                    <textarea class="form-control" id="reasons" name="reasons" placeholder="Reasons" rows="5" <?php echo isset($overtime) && $overtime->status != 'draft' ? 'disabled' : ''; ?>>{{$overtime->reasons ??''}}</textarea>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="date">Date</label>
                    <input id="date" name="date" class="form-control flatpickr flatpickr-input active" type="text" value="{{$overtime->date ??''}}" <?php echo isset($overtime) && $overtime->status != 'draft' ? 'disabled' : ''; ?>>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-3">
                    <label for="estimated_time_taken">Estimated Time Taken (Hours)</label>
                    <input
                      id="estimated_time_taken"
                      class="form-control"
                      type="number"
                      name="estimated_time_taken"
                      value="{{$overtime->estimated_time_taken ??''}}"
                      <?php echo isset($overtime) && $overtime->status != 'draft' ? 'disabled' : ''; ?>
                    >
                  </div>
                  @isset($overtime)
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="actual_time_start">Actual Time Start</label>
                    <input
                      id="actual_time_start"
                      class="form-control flatpickr flatpickr-input active"
                      type="text"
                      name="actual_time_start"
                      value="{{$overtime->actual_time_start ??''}}"
                      <?php echo isset($overtime) && $overtime->status == 'pre_reviewed' ? '' : 'readonly'; ?>
                    >
                  </div>
                  <div class="col-12 col-md-6 form-group mb-3">
                    <label for="actual_time_end">Actual Time End</label>
                    <input
                      id="actual_time_end"
                      class="form-control flatpickr flatpickr-input active"
                      type="text"
                      name="actual_time_end"
                      value="{{$overtime->actual_time_end ??''}}"
                      <?php echo isset($overtime) && $overtime->status == 'pre_reviewed' ? '' : 'readonly'; ?>
                    >
                  </div>
                  <div class="col-12 col-md-6 form-group mb-3">
                    <label for="actual_time_taken">Actual Time Taken (Hours)</label>
                    <input
                      id="actual_time_taken"
                      class="form-control"
                      type="number"
                      name="actual_time_taken"
                      value="{{$overtime->actual_time_taken ??''}}"
                      readonly
                    >
                  </div>
                  <div class="col-12 col-md-6 form-group mb-3">
                    <label for="actual_time_approved">Actual Time Approved (Hours)</label>
                    <input
                      id="actual_time_approved"
                      class="form-control"
                      type="number"
                      name="actual_time_approved"
                      value="{{$overtime->actual_time_approved ??''}}"
                      readonly
                    >
                  </div>
                  @endisset
                  <div class="col-12 mb-4">
                    <label for="attachment">Attachment</label>
                    @if(!isset($overtime) || $overtime->status == 'draft' || $overtime->status == 'pre_reviewed')
                    <div class="input-group mb-3">
                      <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                      @if(isset($overtime) && ($overtime->status == 'draft' || $overtime->status == 'pre_reviewed'))
                      <button class="btn btn-primary" type="button" id="button-addon2" onclick="addAttachment()">Add Attachment</button>
                      @endif
                    </div>
                    @endif
                    
                    @if (isset($overtime))
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
                            @forelse ($overtime->attachments as $row)
                            <tr>
                              <td>{{$row->filename}}</td>
                              <td class="text-center">
                                <div class="action-btns">
                                  <a href="{{ route('attachment.show',$row) }}"  target="_blank" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                  </a>
                                  @if($overtime->status == 'draft')
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
        </div>
      </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
    <script>
      var overtime = @json($overtime ?? null);

      if (overtime !== null) {
        var f1 = flatpickr(document.getElementById('actual_time_start'), {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
        });

        var f2 = flatpickr(document.getElementById('actual_time_end'), {
            enableTime: true,
            dateFormat: "Y-m-d H:i:S",
        });
      }

      var f3 = flatpickr(document.getElementById('date'), {
        mode: "single",
      }); 
    </script>
    <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
    <script>
      function saveDraft() {
        document.getElementById("status").value = "draft";
        document.getElementById("overtime_form").submit();
      }

      function saveRequest() {
        document.getElementById("status").value = "requested";
        document.getElementById("overtime_form").submit();
      }

      function saveSubmit() {
        document.getElementById("status").value = "submitted";
        document.getElementById("overtime_form").submit();
      }

      function cancel() {
        document.getElementById("status").value = "cancelled";
        document.getElementById("overtime_form").submit();
      }

      function addAttachment(){
        document.getElementById('user_id').disabled = true;
        document.getElementById('status').disabled = true;
        document.getElementById('reasons').disabled = true;
        document.getElementById('date').disabled = true;
        document.getElementById('actual_time_start').disabled = true;
        document.getElementById('actual_time_end').disabled = true;
        document.getElementById("overtime_form").submit();
      }
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>