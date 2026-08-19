<x-base-layout :scrollspy="false">

  <x-slot:pageTitle>
    Edit Purchase Requisition | {{ env('APP_NAME') }}
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
        <li class="breadcrumb-item"><a href="{{ route('purchase_requisition.requestIndex') }}">Purchase Requisition Approval</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Purchase Requisition</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form id="purchase_requisition_form" action="{{ route('purchase_requisition.requestUpdate', $purchaseRequisition->id) }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12 col-md-8 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Purchase Requisition Details</h4>
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
                <input type="text" id="purchase_requisition_id" name="purchase_requisition_id" value="{{$purchaseRequisition->id ?? ''}}" hidden>
                <input type="text" id="status" name="status" value="{{$purchaseRequisition->status ??''}}" hidden>
              </div>
              <div class="col-12 form-group mb-4">
                <label>Requested By</label>
                <input
                  class="form-control"
                  name="user"
                  placeholder="{{$purchaseRequisition->user->name}}"
                  disabled
                >
              </div>
              <div class="col-12 form-group mb-4">
                <label for="department_id" >Department</label>
                <input
                  class="form-control"
                  name="department_id"
                  placeholder="{{ $purchaseRequisition->department ? $purchaseRequisition->department->department_name : '-'}}"
                  disabled
                >
              </div>
              <div class="col-12 form-group mb-4">
                <label for="purpose">Purpose/Use</label>
                <input type="text" class="form-control" id="purpose" name="purpose" placeholder="{{$purchaseRequisition->purpose ??''}}"disabled>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label for="date_needed">Date Needed</label>
                <input id="date_needed" name="date_needed" class="form-control flatpickr flatpickr-input active" type="text" placeholder="{{$purchaseRequisition->date_needed ??''}}" disabled>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label for="date_ordered">Date Ordered (Optional)</label>
                <input id="date_ordered" name="date_ordered" class="form-control flatpickr flatpickr-input active" type="text" placeholder="{{$purchaseRequisition->date_ordered ??''}}" disabled>
              </div>
              <div class="col-12 form-group mb-4">
                <label for="purchased_from">Purchased From</label>
                <input type="text" class="form-control" id="purchased_from" name="purchased_from" placeholder="{{$purchaseRequisition->purchased_from ??''}}" disabled>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label>Auto Renew</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="auto_renew" id="auto_renew_enable" value="1" required {{ isset($purchaseRequisition) && $purchaseRequisition->auto_renew == '1' ? 'checked' : '' }} disabled>
                  <label class="form-check-label" for="auto_renew_enable">
                    Yes
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="auto_renew" id="auto_renew_disable" value="0" required {{ isset($purchaseRequisition) && $purchaseRequisition->auto_renew == '0' ? 'checked' : '' }} disabled>
                  <label class="form-check-label" for="auto_renew_disable">
                    No
                  </label>
                </div>
              </div>
              <div class="col-12 col-md-6 form-group mb-4">
                <label>Source of Fund</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="source_of_fund" id="source_of_fund_personal" value="personal" required {{ isset($purchaseRequisition) && $purchaseRequisition->source_of_fund == 'personal' ? 'checked' : '' }} disabled>
                  <label class="form-check-label" for="source_of_fund_personal">
                    Personal
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="source_of_fund" id="source_of_fund_company" value="company" required {{ isset($purchaseRequisition) && $purchaseRequisition->source_of_fund == 'company' ? 'checked' : '' }} disabled>
                  <label class="form-check-label" for="source_of_fund_company">
                    Company
                  </label>
                </div>
              </div>
              <div class="col-12 mb-4 invoice-detail-items">
                <div class="table-responsive">
                  <table class="table table-bordered table-striped item-table">
                    <thead>
                      <tr>
                        <th class="text-center">No</th>
                        <th class="w-50">Description</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Unit Price (Optional)</th>
                        <th class="text-center">Total Price (Optional)</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($purchaseRequisition->purchaseRequisitionItems as $item)
                      <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{$item->description}}</td>
                        <td class="text-end">{{$item->quantity ?? '-'}}</td>
                        <td class="text-end">{{$item->unit_price ?? '-'}}</td>
                        <td class="text-end">{{$item->total_price ?? '-'}}</td>
                      </tr>
                      @endforeach
                      <tr>
                        <td colspan="4" class="text-end"><b>Total Amount</b></td>
                        <td class="text-end">{{ $purchaseRequisition->totalAmount() }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-12 form-group mb-4">
                <label for="remarks">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" placeholder="{{$purchaseRequisition->remarks ??''}}" rows="5" disabled></textarea>
              </div>
            </div>
          </div>
        </div>
        @isset($purchaseRequisition->attachments)
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Attachments</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">
            <div class="row">
              <div class="col-12 mb-4">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th scope="col">Filename</th>
                        <th class="text-center" scope="col">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @forelse ($purchaseRequisition->attachments as $row)
                      <tr>
                        <td>{{$row->filename}}</td>
                        <td class="text-center">
                          <div class="action-btns">
                            <a href="{{ route('attachment.show',$row) }}"  target="_blank" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="View">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </a>
                            @if($purchaseRequisition->status == 'draft')
                            <a onclick="if(confirm('Are you sure you want to delete this purchase requisition?')){ window.location.href='{{ route('attachment.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                            </a>
                            @endif
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
              </div>
            </div>

          </div>
        </div>
        @endisset
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
              @if($purchaseRequisition->status == 'draft')
              <span class="badge badge-info">Draft</span>
              @elseif($purchaseRequisition->status == 'submitted')
              <span class="badge badge-primary">Submitted</span>
              @elseif($purchaseRequisition->status == 'approved')
              <span class="badge badge-success">Approved</span>
              @elseif($purchaseRequisition->status == 'cancelled')
              <span class="badge badge-danger">Cancelled</span>
              @elseif($purchaseRequisition->status == 'rejected')
              <span class="badge badge-danger">Rejected</span>
              @endif
            </div>
            @if(Auth::user()->position && Auth::user()->position->leave_approver && in_array(Auth::user()->position->id, $purchaseRequisition->user->leave_approvers) && ($purchaseRequisition->status != 'cancelled' && $purchaseRequisition->status != 'rejected' && $purchaseRequisition->status != 'approved') )
            <div class="form-group mb-4">
              <label>Approval</label>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_approve" value="1" <?php echo isset($purchaseRequisition->approval_status) && $purchaseRequisition->approval_status == '1' ? 'checked' : '' ?> required>
                <label class="form-check-label" for="approval_status_approve">
                  Approve
                </label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="approval_status" id="approval_status_reject" value="0" <?php echo isset($purchaseRequisition->approval_status) && $purchaseRequisition->approval_status == '0' ? 'checked' : '' ?> required>
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
              <textarea class="form-control" id="approval_remark" name="approval_remark" placeholder="Approval Remark" rows="3">{{$purchaseRequisition->approval_remark ??''}}</textarea>
            </div>
            <div class="form-group mb-4 d-grid">
              <a class="btn btn-primary" style="float:right" onclick="submit()">Submit</a>
            </div>
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