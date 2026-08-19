<x-base-layout :scrollspy="false">
  @php
    if(isset($position)){
      $title = 'Edit Position';
    }else{
      $title = 'Add Position';
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
        <li class="breadcrumb-item"><a href="{{ route('position.index') }}">Position</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('position.store') }}" method="post" enctype="multipart/form-data">
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
              <input type="text" id="position_id" name="position_id" value="{{$position->id ??''}}" hidden>
              <div class="form-group col-12 mb-4">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$position->name ??''}}" required>
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" class="form-select" required>
                  <option disabled selected> -- Select --</option>
                  @foreach($department as $d)
                  <option value='{{$d->id}}' <?php echo isset($position->department_id) && $position->department_id == $d->id ? 'selected' : '' ?>>{{$d->department_name}}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="role_id">System Role </label>
                <select id="role_id" name="role_id" class="form-select" required>
                  <option disabled selected> -- Select --</option>
                  @foreach($role as $r)
                  <option value='{{$r->id}}' <?php echo isset($position->role_id) && $position->role_id == $r->id ? 'selected' : '' ?>>{{$r->title}}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-12 mb-4">
                <label for="description">Description</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="Description.." value="{{$position->description ??''}}">
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label>Reviewer of Leave Application</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="leave_reviewer" id="leave_reviewer_yes" value="1" {{ isset($position) && $position->leave_reviewer == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="leave_reviewer_yes">
                    Yes
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="leave_reviewer" id="leave_reviewer_ no"value="0" {{ isset($position) && $position->leave_reviewer == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="leave_reviewer_no">
                    No
                  </label>
                </div>
              </div>
              <div class="form-group col-12 col-md-6 mb-4">
                <label>Approver of Leave Application</label>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="leave_approver" value="1" id="leave_approver_yes" {{ isset($position) && $position->leave_approver == '1' ? 'checked' : '' }}>
                  <label class="form-check-label" for="leave_approver_yes">
                    Yes
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="leave_approver" value="0" id="leave_approver_no" {{ isset($position) && $position->leave_approver == '0' ? 'checked' : '' }}>
                  <label class="form-check-label" for="leave_approver_no">
                    No
                  </label>
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