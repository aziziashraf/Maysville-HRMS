<x-base-layout :scrollspy="false">
  @php 
    $title = "Create Claim";
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
            <li class="breadcrumb-item"><a href="{{ route('purchase_requisition.index') }}">Purchase Requisition</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}} </li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form id="claim_form" action="{{ route('claim.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            <a class="btn btn-primary mb-2 me-2" style="float:right" onclick="saveSubmit()">Submit</a>
            <a class="btn btn-info mb-2 me-2" style="float:right" onclick="saveDraft()">Save as Draft</a>
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
                    <input type="text" id="purchase_requisition_id" name="purchase_requisition_id" value="{{$purchaseRequisition->id}}" hidden>
                    <input type="text" id="user_id" name="user_id" value="{{$purchaseRequisition->user_id}}" hidden>
                    <input type="text" id="status" name="status" value="" hidden>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="claim_type_id">Claim Type</label>
                    <select id="claim_type_id" name="claim_type_id" class="form-select" required>
                      @foreach($claimTypes as $ct)
                      <option value='{{$ct->id}}'>
                        {{$ct->name}}
                      </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="amount">Claim Amount (RM)</label>
                    <input type="number" class="form-control" id="amount" name="amount" placeholder="Amount" value="{{$purchaseRequisition->totalAmount()}}" required >
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks" rows="5" >Purchase Requisition Claim, (ID:{{$purchaseRequisition->id}})</textarea>
                  </div>
                  <div class="col-12 mb-4">
                    <label for="attachment">Attachment</label>
                    <div class="input-group mb-3">
                      <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                    </div>
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
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>