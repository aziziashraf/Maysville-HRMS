<x-base-layout :scrollspy="false">
  @php 
    if(isset($claim)){
      $title = 'Edit Claim';
    }else{
      $title = 'Add Claim';
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

    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss'])        
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

      <!-- BREADCRUMB -->
      <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('claim.index') }}">Claim Application</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}} </li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form id="claim_form" action="{{ route('claim.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            @if(isset($claim) && ($claim->status != 'cancelled' && $claim->status != 'rejected'))
            <a class="btn btn-danger mb-2 me-2" style="float:right" onclick="cancel()">Cancel</a>
            @endif
            @if(!isset($claim) || $claim->status == 'draft')
            <a class="btn btn-primary mb-2 me-2" style="float:right" onclick="saveSubmit()">Submit</a>
            <a class="btn btn-info mb-2 me-2" style="float:right" onclick="saveDraft()">Save as Draft</a>
            @endif
          </div>
          <div id="basic" class="col-12 collayout-spacing">
            <div class="statbox widget box box-shadow">
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
                    <input type="text" id="user_id" name="user_id" value="{{$claim->user_id ?? auth()->user()->id}}" hidden>
                    <input type="text" id="status" name="status" value="{{$claim->status ??''}}" hidden>
                  </div>
                  @isset($claim)
                  <div class="col-12 form-group mb-4">
                    <label for="status">Status:
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
                    </label>
                  </div>
                  @endisset
                  <div class="col-12 form-group mb-4">
                    <label for="claim_type_id">Claim Type</label>
                    <select id="claim_type_id" name="claim_type_id" class="form-select" required <?php echo isset($claim) && $claim->status != 'draft' ? 'disabled' : ''; ?>>
                      <option disabled selected> -- Select --</option>
                      @foreach($claim_type as $ct)
                      <option value='{{$ct->id}}' <?php echo isset($claim->claim_type_id) && $claim->claim_type_id == $ct->id ? 'selected' : '' ?>>
                        {{$ct->name}}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="unit">Unit</label>
                    <input type="text" class="form-control" id="unit" name="unit" value="{{$claim->claimType->unit ?? ''}}" readonly>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="unit_price_value">Unit Price</label>
                    <input type="text" class="form-control" id="unit_price_value" name="unit_price_value"  value="{{$claim->claimType->unit_price_value ??''}}" readonly>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="unit_quantity">Unit Quantity</label>
                    <input type="number" class="form-control" id="unit_quantity" name="unit_quantity" placeholder="Unit Quantity" value="{{$claim->unit_quantity ??''}}" <?php echo isset($claim) && $claim->claimType->unit_price ? '' : 'disabled'?> <?php echo isset($claim) && $claim->status != 'draft' ? 'disabled' : ''; ?>>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="amount">Claim Amount (RM)</label>
                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount" value="{{$claim->amount ??''}}" required <?php echo isset($claim) && $claim->status != 'draft' ? 'disabled' : ''; ?>>
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks" rows="5" <?php echo isset($claim) && $claim->status != 'draft' ? 'disabled' : ''; ?>>{{$claim->remarks ??''}}</textarea>
                  </div>
                  <div class="col-12 mb-4">
                    <label for="attachment">Attachment</label>
                    @if(!isset($claim) || $claim->status == 'draft')
                    <div class="input-group mb-3">
                      <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                      @if(isset($claim) && $claim->status == 'draft')
                      <button class="btn btn-primary" type="button" id="button-addon2" onclick="addAttachment()">Add Attachment</button>
                      @endif
                    </div>
                    @endif
                    
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
                                  @if($claim->status == 'draft')
                                  <a onclick="if(confirm('Are you sure you want to delete this claim?')){ window.location.href='{{ route('attachment.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
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
    <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
    <script>
      function saveDraft() {
        document.getElementById("status").value = "draft";
        document.getElementById("claim_form").submit();
      }

      function saveSubmit() {
        document.getElementById("status").value = "submitted";
        document.getElementById("claim_form").submit();
      }

      function cancel() {
        document.getElementById("status").value = "cancelled";
        document.getElementById("claim_form").submit();
      }

      function addAttachment(){
        document.getElementById('status').disabled = true;
        document.getElementById('unit_quantity').disabled = true;
        document.getElementById('amount').disabled = true;
        document.getElementById('unit').disabled = true;
        document.getElementById('unit_price_value').disabled = true;
        document.getElementById('remarks').disabled = true;
        document.getElementById('claim_type_id').disabled = true;
        document.getElementById("claim_form").submit();
      }
    </script>
    <script>
      // jQuery code to update unit value on claim_type_id change
      $(document).ready(function() {
        // Attach onchange event to the claim_type_id select element
        $('#claim_type_id').on('change', function() {
          var selectedClaimTypeId = $(this).val(); // Get the selected claim_type_id value

          // Find the selected claim_type from the claim_type data
          var selectedClaimType = null;
          $.each(<?php echo json_encode($claim_type); ?>, function(index, claimType) {
            if (claimType.id == selectedClaimTypeId) {
              selectedClaimType = claimType;
              return false; // Exit the loop
            }
          });

          if (selectedClaimType) {
            var unit = selectedClaimType.unit; // Get the unit value from the selected claim_type
            var unitPrice = selectedClaimType.unit_price; // Get the unit_price value from the selected claim_type
            var unitPriceValue = selectedClaimType.unit_price_value; // Get the unit_price_value value from the selected claim_type
            // make it so that RM unitPrice / unit is displayed
            if (unitPrice){
              // var unitPriceDisplay = 'RM ' + unitPriceValue + ' / ' + unit;
              var unitPriceDisplay = unitPriceValue;
              document.getElementById('unit_quantity').disabled = false;
              document.getElementById('unit_quantity').required = true;
            } else {
              var unitPriceDisplay = 'N/A';
              document.getElementById('unit_quantity').disabled = true;
              document.getElementById('unit_quantity').required = false;
            }
            // Update the value of unit input
            $('#unit').val(unit);
            $('#unit_price_value').val(unitPriceDisplay);
          } else {
            console.error('Selected claim type not found in claim_type data.');
          }
        });

        $('#unit_quantity').on('change', function() {
          var unitQuantity = $(this).val();
          var unitPriceValue = parseFloat(document.getElementById('unit_price_value').value);
          
          if (!isNaN(unitPriceValue) && unitPriceValue !== 'N/A') {
            var amount = unitQuantity * unitPriceValue;
            amount = amount.toFixed(2); // Limit to 2 decimal places
            $('#amount').val(amount);
          }
        });
      });
    </script>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>