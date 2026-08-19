<x-base-layout :scrollspy="false">

  <x-slot:pageTitle>
    Edit Overtime | {{ env('APP_NAME') }}
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
        <li class="breadcrumb-item"><a href="{{ route('overtime.requestIndex') }}">Overtime Approval</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Overtime</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form id="overtime_form" action="{{ route('overtime.requestUpdate', $overtime->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12 col-md-8 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Overtime Details</h4>
              </div>
            </div>
          </div>

          <div class="widget-content widget-content-area">

            <div class="row">
              <div class="col-12">
                <input type="text" id="overtime_id" name="overtime_id" value="{{$overtime->id ?? ''}}" hidden>
                <input type="text" id="user_id" name="user_id" value="{{$overtime->user_id}}" hidden>
                <input type="text" id="status" name="status" value="{{$overtime->status ??''}}" hidden>
              </div>
              <div class="col-12 form-group mb-4">
                <label>Employee</label>
                <input
                  class="form-control"
                  name="user"
                  placeholder="{{$overtime->user->name}}"
                  disabled
                >
              </div>
              <div class="col-12 form-group mb-4">
                <label for="reasons">Reasons</label>
                <textarea class="form-control" id="reasons" name="reasons" placeholder="{{$overtime->reasons ??''}}" rows="5" <?php echo isset($overtime) && $overtime->status != 'draft' ? 'disabled' : ''; ?>></textarea>
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
                  placeholder="{{$overtime->estimated_time_taken ??''}}"
                  disabled
                >
              </div>
              @if(isset($overtime) && $overtime->status != 'requested' && $overtime->status != 'pre_reviewed')
              <div class="col-12 col-md-6 form-group mb-4">
                <label for="actual_time_start">Actual Time Start</label>
                <input
                  id="actual_time_start"
                  class="form-control flatpickr flatpickr-input active"
                  type="text"
                  name="actual_time_start"
                  value="{{$overtime->actual_time_start ??''}}"
                  disabled
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
                  disabled
                >
              </div>
              <div class="col-12 col-md-6 form-group mb-3">
                <label for="actual_time_taken">Actual Time Taken (Hours)</label>
                <input
                  id="actual_time_taken"
                  class="form-control"
                  type="number"
                  name="actual_time_taken"
                  placeholder="{{$overtime->actual_time_taken ??''}}"
                  disabled
                >
              </div>
              @endisset
              <div class="col-12 mb-4">
                <label for="attachment">Attachment</label>
                @if(!isset($overtime) || $overtime->status == 'draft')
                <div class="input-group mb-3">
                  <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                  @if(isset($overtime) && $overtime->status == 'draft')
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
                              <a onclick="if(confirm('Are you sure you want to delete?')){ window.location.href='{{ route('attachment.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
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
            @if($errors->any())
              @foreach ($errors->all() as $error)
                <div class="alert alert-light-danger alert-dismissible fade show border-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
              @endforeach
            @endif
          </div>
          <div class="widget-content widget-content-area">
            <div class="form-group mb-4">
              <label>Status:</label>
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
            </div>
            @if(Auth::user()->position)
            @if((Auth::user()->position->leave_reviewer && in_array(Auth::user()->position->id, $overtime->user->leave_reviewers)) || (Auth::user()->position->leave_approver && in_array(Auth::user()->position->id, $overtime->user->leave_approvers)))
            @if($overtime->status != 'requested' && $overtime->status != 'pre_reviewed')
            <div class="col-12 form-group mb-3">
              <label for="actual_time_approved">Actual Time Approved (Hours)</label>
              <input
                id="actual_time_approved"
                class="form-control"
                type="number"
                name="actual_time_approved"
                <?php echo ((($overtime->status == 'submitted') && Auth::user()->position->leave_reviewer) || (($overtime->status == 'submitted' || $overtime->status == 'reviewed') && Auth::user()->position->leave_approver)) ? 'value='.$overtime->actual_time_approved ?? '' : 'placeholder='.$overtime->actual_time_approved.' disabled'; ?>
              >
            </div>
            <div class="col-12 form-group mb-3">
              <label for="claim_as">Claim As</label>
              <select class="form-select" id="claim_as" name="claim_as" <?php echo ((($overtime->status == 'submitted') && Auth::user()->position->leave_reviewer) || (($overtime->status == 'submitted' || $overtime->status == 'reviewed') && Auth::user()->position->leave_approver)) ? '' : 'disabled'; ?>>
                <option disabled selected> -- Select -- </option>
                <option value="cash" <?php echo isset($overtime->claim_as) && $overtime->claim_as == 'cash' ? 'selected' : ''; ?>>Cash</option>
                <option value="replacement_leave" <?php echo isset($overtime->claim_as) && $overtime->claim_as == 'replacement_leave' ? 'selected' : ''; ?>>Replacement Leave</option>
              </select>
            </div>
            @endif
            @if($overtime->status == 'requested')
            <div class="form-group mb-4">
              <label>Pre Review</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pre_review_status" id="pre_review_status_review" value="1" <?php echo isset($overtime->pre_review_status) && $overtime->pre_review_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="pre_review_status_review">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="pre_review_status" id="pre_review_status_reject" value="0" <?php echo isset($overtime->pre_review_status) && $overtime->pre_review_status == '0' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="pre_review_status_reject">
                  Reject
                </label>
              </div>
              @error('pre_review_status')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 form-group mb-4">
              <label for="remarks">Pre Review Remark (Optional)</label>
              <textarea class="form-control" id="pre_review_remark" name="pre_review_remark" placeholder="Review Remark" rows="3">{{$overtime->pre_review_remark ??''}}</textarea>
            </div>
            <div class="form-group mb-4 d-grid">
              <a class="btn btn-primary" style="float:right" onclick="submit()">Submit</a>
            </div>
            @endif
            @endif
            @if(Auth::user()->position->leave_reviewer && in_array(Auth::user()->position->id, $overtime->user->leave_reviewers) && $overtime->status == 'submitted')
            <div class="form-group mb-4">
              <label>Review</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="review_status" id="review_status_review" value="1" <?php echo isset($overtime->review_status) && $overtime->review_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="review_status_review">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="review_status" id="review_status_reject" value="0" <?php echo isset($overtime->review_status) && $overtime->review_status == '0' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="review_status_reject">
                  Reject
                </label>
              </div>
              @error('review_status')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 form-group mb-4">
              <label for="remarks">Review Remark (Optional)</label>
              <textarea class="form-control" id="review_remark" name="review_remark" placeholder="Review Remark" rows="3">{{$overtime->review_remark ??''}}</textarea>
            </div>
            <div class="form-group mb-4 d-grid">
              <a class="btn btn-primary" style="float:right" onclick="submit()">Submit</a>
            </div>
            @endif
            @if(Auth::user()->position->leave_approver && in_array(Auth::user()->position->id, $overtime->user->leave_approvers) && ($overtime->status == 'submitted' || $overtime->status == 'reviewed') )
            <div class="form-group mb-4">
              <label>Approval</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_approve" value="1" <?php echo isset($overtime->approval_status) && $overtime->approval_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="approval_status_approve">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_reject" value="0" <?php echo isset($overtime->approval_status) && $overtime->approval_status == '0' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="approval_status_reject">
                  Reject
                </label>
              </div>
              @error('approval_status')
                <span class="text-danger">{{ $message }}</span>
              @enderror
            </div>
            <div class="col-12 form-group mb-4">
              <label for="remarks">Approval Remark (Optional)</label>
              <textarea class="form-control" id="approval_remark" name="approval_remark" placeholder="Approval Remark" rows="3">{{$overtime->approval_remark ??''}}</textarea>
            </div>
            <div class="form-group mb-4 d-grid">
              <a class="btn btn-primary" style="float:right" onclick="submit()">Submit</a>
            </div>
            @endif
            @endif
          </div>
        </div>
      </div>
    </div>
  </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
    <script>
      // var overtime = @json($overtime ?? null);

      // if (overtime !== null) {
      //   var f1 = flatpickr(document.getElementById('actual_time_start'), {
      //       enableTime: true,
      //       dateFormat: "Y-m-d H:i:S",
      //   });

      //   var f2 = flatpickr(document.getElementById('actual_time_end'), {
      //       enableTime: true,
      //       dateFormat: "Y-m-d H:i:S",
      //   });
      // }
    
      var f3 = flatpickr(document.getElementById('date'));
    </script>
    <script>
			function submit(){
				document.getElementById("overtime_form").submit();
			}
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>