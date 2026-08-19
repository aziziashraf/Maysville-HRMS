<x-base-layout :scrollspy="false">
  @php
    if(isset($claim_type)){
      $title = 'Edit Claim Type';
    }else{
      $title = 'Add Claim Type';
    }
  @endphp
  <x-slot:pageTitle>
    {{$title}} | {{ env('APP_NAME') }}
  </x-slot>

  <!-- BEGIN GLOBAL MANDATORY STYLES -->
  <x-slot:headerFiles>
    <!--  BEGIN CUSTOM STYLE FILE  -->
    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss'])        
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

  <!-- BREADCRUMB -->
  <div class="page-meta">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('claim_type.index') }}">Claim Type</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('claim_type.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
      <div id="basic" class="col-12  collayout-spacing">
        <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
      </div>
      <div id="basic" class="col-12 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>Details</h4>
              </div>
            </div>
            @if($errors->any())
              @foreach ($errors->all() as $error)
                  <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
              @endforeach
            @endif
          </div>
          <div class="widget-content widget-content-area">

            <div class="row">
              <input type="text" id="claim_type_id" name="claim_type_id" value="{{$claim_type->id ??''}}" hidden>
              <div class="form-group col-12 mb-4">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$claim_type->name ??''}}" required>
              </div>
              <div class="form-group col-12 mb-4">
                <label for="description">Description</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="Description.." value="{{$claim_type->description ??''}}">
              </div>
              <div class="form-group col-12 mb-4">
                <label>Unit</label>
                <input type="text" class="form-control" id="unit" name="unit" placeholder="Unit.." value="{{$claim_type->unit ??''}}" required>
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label>Unit Price Setting</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="unit_price" id="unit_price_enable" value="1" required {{ isset($claim_type) && $claim_type->unit_price == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="unit_price_enable">
                    Enable
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="unit_price" id="unit_price_disable" value="0" required {{ isset($claim_type) && $claim_type->unit_price == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="unit_price_disable">
                    Disable
                  </label>
                </div>
              </div>
              <div class="form-group col-12 col-md-6 mb-4 value" hidden>
                <label for="unit_price_value" id="unit_price_value_label">Unit Price</label>
                <input type="number" class="form-control" id="unit_price_value" name="unit_price_value" placeholder="Unit Price.." value="{{$claim_type->unit_price_value ??''}}" step="0.05" required>
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
      // Get references to the radio buttons and fields
      const unitPriceEnable = document.getElementById('unit_price_enable');
      const unitPriceValue = document.getElementById('unit_price_value');
      var valueDiv = $('.value');

      $(document).ready(function() {
        if (unitPriceEnable.checked) {
          unitPriceValue.removeAttribute('disabled');
          valueDiv.removeAttr('hidden');
        } else {
          unitPriceValue.setAttribute('disabled', 'disabled');
          valueDiv.attr('hidden', 'hidden');
        }
      });

      // Add an event listener to the radio button
      unitPriceEnable.addEventListener('change', function() {
        if (this.checked) {
          unitPriceValue.removeAttribute('disabled');
          valueDiv.removeAttr('hidden');
        }
      });

      const unitPriceDisable = document.getElementById('unit_price_disable');

      unitPriceDisable.addEventListener('change', function() {
        if (this.checked) {
          unitPriceValue.setAttribute('disabled', 'disabled');
          valueDiv.attr('hidden', 'hidden');
        }
      });
    </script>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>