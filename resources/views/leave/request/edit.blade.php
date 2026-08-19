<x-base-layout :scrollspy="false">
  <x-slot:pageTitle>
    Edit Leave | {{ env('APP_NAME') }}
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
        <li class="breadcrumb-item active" aria-current="page">Edit Leave</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form id="leave_form" action="{{ route('leave.requestUpdate', $leave->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12 col-md-8 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Leave Details</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">
            <div class="row">
              <div class="col-12">
                <input type="text" id="leave_id" name="leave_id" value="{{$leave->id ?? ''}}" hidden>
              </div>
              <div class="col-12 form-group mb-4">
                <label>Name</label>
                <input disabled type="text" class="form-control" value="{{$leave->user->name}}">
              </div>
              <div class="col-12 form-group mb-4">
                <label for="leave_type_id">Leave Type</label>
                <select disabled id="leave_type_id" name="leave_type_id" class="form-select">
                  <option disabled selected> -- Select --</option>
                  @foreach($leave_type as $lt)
                  <option value='{{$lt->id}}' <?php echo isset($leave->leave_type_id) && $leave->leave_type_id == $lt->id ? 'selected' : '' ?>>{{$lt->name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-12 form-group mb-4">
                <label for="remarks">Reason</label>
                <textarea readonly class="form-control" id="remarks" name="remarks" placeholder="Reason" rows="5">{{$leave->remarks ??''}}</textarea>
              </div>
              <div class="col-12 form-group mb-4">
                <label for="date_range">Date Range</label>
                <input disabled id="date_range" name="date_range" class="form-control flatpickr flatpickr-input active" placeholder="Date Range.." type="text" value="{{$leave->date_range ??''}}">
              </div>
              <div class="col-12  col-md-6 form-group mb-4">
                <label for="start_time">Start Time</label>
                <input
                  disabled
                  class="form-control"
                  type="time"
                  placeholder="Select Time.."
                  name="start_time" 
                  value="{{$leave->start_time ??''}}"
                >
              </div>
              <div class="col-12 col-md-6 form-group mb-3">
                <label for="end_time">End Time</label>
                <input
                  disabled
                  class="form-control"
                  type="time"
                  placeholder="Select Time.."
                  name="end_time"
                  value="{{$leave->end_time ??''}}"
                >
              </div>
              <div class="col-12 mb-4">
                <label for="attachment">Attachment</label>                
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
              @if($leave->status == 'draft')
              <span class="badge badge-info">Draft</span>
              @elseif($leave->status == 'submitted')
              <span class="badge badge-primary">Submitted</span>
              @elseif($leave->status == 'reviewed')
              <span class="badge badge-success">Reviewed</span>
              @elseif($leave->status == 'approved')
              <span class="badge badge-success">Approved</span>
              @elseif($leave->status == 'cancelled')
              <span class="badge badge-danger">Cancelled</span>
              @elseif($leave->status == 'rejected')
              <span class="badge badge-danger">Rejected</span>
              @endif
            </div>
            @if(Auth::user()->position)
            @if(Auth::user()->position->leave_reviewer && in_array(Auth::user()->position->id, $leave->user->leave_reviewers) && ($leave->status != 'cancelled' && $leave->status != 'rejected' && $leave->status != 'reviewed' && $leave->status != 'approved'))
            <div class="form-group mb-4">
              <label>Review</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="review_status" id="review_status_review" value="1" <?php echo isset($leave->review_status) && $leave->review_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="review_status_review">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="review_status" id="review_status_reject" value="0" <?php echo isset($leave->review_status) && $leave->review_status == '0' ? 'checked' : '' ?> required>
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
              <textarea class="form-control" id="review_remark" name="review_remark" placeholder="Review Remark" rows="3">{{$leave->review_remark ??''}}</textarea>
            </div>
            <div class="form-group mb-4 d-grid">
              <a class="btn btn-primary" style="float:right" onclick="submit()">Submit</a>
            </div>
            @endif
            @if(Auth::user()->position->leave_approver && in_array(Auth::user()->position->id, $leave->user->leave_approvers) && ($leave->status != 'cancelled' && $leave->status != 'rejected' && $leave->status != 'approved') )
            <div class="form-group mb-4">
              <label>Approval</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_approve" value="1" <?php echo isset($leave->approval_status) && $leave->approval_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="approval_status_approve">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_reject" value="0" <?php echo isset($leave->approval_status) && $leave->approval_status == '0' ? 'checked' : '' ?> required>
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
              <textarea class="form-control" id="approval_remark" name="approval_remark" placeholder="Approval Remark" rows="3">{{$leave->approval_remark ??''}}</textarea>
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
      var f3 = flatpickr(document.getElementById('date_range'), {
        mode: "range",
        allowInput: false,
      });
    </script>
    <script>
			function submit(){
				document.getElementById("leave_form").submit();
			}
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>