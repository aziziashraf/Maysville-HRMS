<x-base-layout :scrollspy="false">
  @php
    if(isset($handbook_category)){
      $title = 'Edit HandBook Catgory';
    }else{
      $title = 'Add HandBook Category';
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
        <li class="breadcrumb-item"><a href="{{ route('handbook_category.index') }}">HandBook Category</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('handbook_category.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
          </div>
          <div id="basic" class="col-12 col-md-8 collayout-spacing">
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Basic Info</h4>
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
                    <input type="text" class="form-control" id="handbook_category_id" name="handbook_category_id" value="{{$handbook_category->id ??''}}" hidden>
                    <div class="form-group mb-4">
                      <label for="name">Name</label>
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$handbook_category->name ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="description">Description</label>
                      <input type="text" class="form-control" id="description" name="description" placeholder="Description.." value="{{$handbook_category->description ??''}}" required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="description">Index Number (positioning purpose)</label>
                      <input type="text" class="form-control" id="index_number" name="index_number" placeholder="e.g. 1,2,3" value="{{$handbook_category->index_number ??''}}" required>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div id="basic" class="col-12 col-md-4  collayout-spacing">
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Settings</h4>
                  </div>
                </div>
              </div>
              <div class="widget-content widget-content-area">
                <div class="form-group mb-4">
                  <label>Status</label>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_publish" value="1" <?php echo isset($handbook_category) && $handbook_category->status == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_active">
                      Publish
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_unpublish" value="0" <?php echo isset($handbook_category) && $handbook_category->status == 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_block">
                      Unpublish
                    </label>
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