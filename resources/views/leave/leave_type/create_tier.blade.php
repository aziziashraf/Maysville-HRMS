<x-base-layout :scrollspy="false">
  @php
    if(isset($leave_balance_tier)){
      $title = 'Edit Leave Balance Tier';
    } else {
      $title = 'Add Leave Balance Tier';
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
        <li class="breadcrumb-item"><a href="{{ route('leave_type.index') }}">Leave Type</a></li>
        <li class="breadcrumb-item"><a href="{{ route('leave_type.edit',$leave_balance_tier->leaveType->id ?? $leave_type->id) }}">{{$leave_balance_tier->leaveType->name ?? $leave_type->name}}</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('leave_balance_tier.store') }}" method="post" enctype="multipart/form-data">
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
              <input type="text" id="leave_balance_tier_id" name="leave_balance_tier_id" value="{{$leave_balance_tier->id ??''}}" hidden>
              <input type="text" id="leave_type_id" name="leave_type_id" value="{{$leave_balance_tier->leaveType->id ?? $leave_type->id}}" hidden>
              <div class="form-group col-12 mb-4">
                <label for="name">Years of Service</label>
                <input type="number" step="1" class="form-control" id="years_of_service" name="years_of_service" value="{{$leave_balance_tier->years_of_service ??''}}" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
              </div>
              <div class="form-group col-12 mb-4">
                <label for="description">Amount</label>
                <input type="number" step="1" class="form-control" id="amount" name="amount" value="{{$leave_balance_tier->amount ??''}}" required>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>