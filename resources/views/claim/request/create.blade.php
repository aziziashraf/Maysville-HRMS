<x-base-layout :scrollspy="false">

  <x-slot:pageTitle>
    Edit Claim | {{ env('APP_NAME') }}
  </x-slot>

  <!-- BEGIN GLOBAL MANDATORY STYLES -->
  <x-slot:headerFiles>
    <!--  BEGIN CUSTOM STYLE FILE  -->
    @vite(['resources/scss/light/assets/components/timeline.scss'])
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/tomSelect/tom-select.default.min.css')}}">
    @vite(['resources/scss/light/plugins/tomSelect/custom-tomSelect.scss'])
    @vite(['resources/scss/dark/plugins/tomSelect/custom-tomSelect.scss'])
    <link rel="stylesheet" href="{{asset('plugins/noUiSlider/nouislider.min.css')}}">
    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss']) 
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

  <!-- BREADCRUMB -->
  <div class="page-meta">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('claim.requestIndex') }}">Claim Approval</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Claim</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form id="claim_form" action="{{ route('claim.requestUpdate', $claim->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12 col-md-8 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Claim Details</h4>
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
                <input type="text" id="claim_id" name="claim_id" value="{{$claim->id ?? ''}}" hidden>
                <input type="text" id="user_id" name="user_id" value="{{$claim->user_id}}" hidden>
                <input type="text" id="status" name="status" value="{{$claim->status ??''}}" hidden>
              </div>
              <div class="col-12 form-group mb-4">
                <label>Employee</label>
                <input
                  class="form-control"
                  name="user"
                  placeholder="{{$claim->user->name}}"
                  disabled
                >
              </div>
              <div class="col-12 form-group mb-4">
                <label for="claim_type_id">Claim Type</label>
                <select id="claim_type_id" name="claim_type_id" class="form-select" disabled>
                  <option disabled selected> -- Select --</option>
                  @foreach($claim_type as $ct)
                  <option value='{{$ct->id}}' <?php echo isset($claim->claim_type_id) && $claim->claim_type_id == $ct->id ? 'selected' : '' ?>>
                    {{$ct->name}}
                  </option>
                  @endforeach
                </select>
              </div>
              @if($claim->claimType->unit_price)
              <div class="col-12 col-md-6 form-group mb-4">
                    <label for="unit">Unit</label>
                    <input type="text" class="form-control" id="unit" name="unit" value="{{$claim->claimType->unit ?? ''}}" disabled>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label for="unit_price_value">Unit Price</label>
                <input type="text" class="form-control" id="unit_price_value" name="unit_price_value"  value="{{$claim->claimType->unit_price_value ??''}}" disabled>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label for="unit_quantity">Unit Quantity</label>
                <input type="number" class="form-control" id="unit_quantity" name="unit_quantity" placeholder="Unit Quantity" value="{{$claim->unit_quantity ??''}}" disabled>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label for="amount">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount" value="{{$claim->amount ??''}}" disabled>
              </div>
              @else
              <div class="col-12 form-group mb-4">
                <label for="amount">Claim Amount (RM)</label>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount" value="{{$claim->amount ??''}}" disabled>
              </div>
              @endif
              <div class="col-12 form-group mb-4">
                <label for="remarks">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="5" readonly>{{$claim->remarks ??''}}</textarea>
              </div>
              <div class="col-12 mb-4">
                <label for="attachment">Attachment</label>                
                @if (isset($claim))
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
                        @forelse ($claim->attachments as $row)
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
                          <td colspan="2" class="text-center">No data available</td>
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
              @if($claim->status == 'draft')
              <span class="badge badge-info">Draft</span>
              @elseif($claim->status == 'submitted')
              <span class="badge badge-primary">Submitted</span>
              @elseif($claim->status == 'reviewed')
              <span class="badge badge-success">Reviewed</span>
              @elseif($claim->status == 'approved')
              <span class="badge badge-success">Approved</span>
              @elseif($claim->status == 'cancelled')
              <span class="badge badge-danger">Cancelled</span>
              @elseif($claim->status == 'rejected')
              <span class="badge badge-danger">Rejected</span>
              @endif
            </div>
            @if(Auth::user()->position)
            @if(Auth::user()->position->leave_reviewer && in_array(Auth::user()->position->id, $claim->user->leave_reviewers) && ($claim->status != 'cancelled' && $claim->status != 'rejected' && $claim->status != 'reviewed' && $claim->status != 'approved'))
            <div class="form-group mb-4">
              <label>Review</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="review_status" id="review_status_approve" value="1" <?php echo isset($claim->review_status) && $claim->review_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="review_status_approve">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="review_status" id="review_status_reject" value="0" <?php echo isset($claim->review_status) && $claim->review_status == '0' ? 'checked' : '' ?> required>
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
              <textarea class="form-control" id="review_remark" name="review_remark" placeholder="Review Remark" rows="3">{{$claim->review_remark ??''}}</textarea>
            </div>
            <div class="form-group mb-4 d-grid">
              <a class="btn btn-primary" style="float:right" onclick="submit()">Submit</a>
            </div>
            @endif
            @if(Auth::user()->position->leave_approver && in_array(Auth::user()->position->id, $claim->user->leave_approvers) && ($claim->status != 'cancelled' && $claim->status != 'rejected' && $claim->status != 'approved') )
            <div class="form-group mb-4">
              <label>Approval</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_approve" value="1" <?php echo isset($claim->approval_status) && $claim->approval_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="approval_status_approve">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_reject" value="0" <?php echo isset($claim->approval_status) && $claim->approval_status == '0' ? 'checked' : '' ?> required>
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
              <textarea class="form-control" id="approval_remark" name="approval_remark" placeholder="Approval Remark" rows="3">{{$claim->approval_remark ??''}}</textarea>
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
    <script>
			function submit(){
				document.getElementById("claim_form").submit();
			}
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>