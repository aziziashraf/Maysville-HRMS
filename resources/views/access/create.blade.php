<x-base-layout :scrollspy="false">
  @php
    if($access){
      $title = 'Edit Access';
    } else {
      $title = 'Add Access';
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

    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss']) 
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

  <!-- BREADCRUMB -->
  <div class="page-meta">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('access.index') }}">Access</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->

  <form action="{{ route('access.store') }}" method="post" enctype="multipart/form-data">
  @csrf
  <div class="row layout-top-spacing">
    <div id="basic" class="col-12  collayout-spacing">
      <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button> 
    </div>
    <div id="basic" class="col-12 collayout-spacing">
      <div class="statbox widget box box-shadow">
        <div class="widget-header">
          <div class="row">
            <div class="col-xl-12 col-md-12 col-sm-12 col-12">
              <h4>Access Details</h4>
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
            <div class="col-12">
              <input type="text" id="acs_id" name="acs_id" value="{{$access->id ??''}}" hidden>
              <div class="form-group mb-4">
                <label for="access_name">Access Name</label>
                <input type="text" class="form-control" id="access_name" name="access_name" placeholder="Name.." value="{{$access->access_name ??''}}" required>
              </div>
              <div class="form-group mb-4">
                <label for="access_id">Access ID</label>
                <input type="text" class="form-control" id="access_id" name="access_id" placeholder="Name.." value="{{$access->access_id ??''}}" required>
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
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>