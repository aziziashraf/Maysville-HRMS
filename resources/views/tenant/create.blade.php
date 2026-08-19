<x-base-layout :scrollspy="false">

  <x-slot:pageTitle>
    Add Tenant | {{ env('APP_NAME') }}
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
            <li class="breadcrumb-item"><a href="#">Form</a></li>
            <li class="breadcrumb-item active" aria-current="page">Basic</li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form action="{{ route('tenant.store') }}" method="post" enctype="multipart/form-data">
			@csrf
      <div class="row layout-top-spacing">
        <div id="basic" class="col-12  collayout-spacing">
          <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button> 
        </div>
        <div id="basic" class="col-12 col-md-8 collayout-spacing">
          <div class="statbox widget box box-shadow">
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
                  <input type="text" class="form-control" id="tenant_id" name="tenant_id" value="{{$tenant->id ??''}}" hidden>
                  <div class="form-group mb-4">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$tenant->name ??''}}" required>
                  </div>
                  <div class="form-group mb-4">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email.." value="{{$tenant->email ??''}}" required>
                  </div>
                  <div class="form-group mb-4">
                    <label for="contact_no">Phone No.</label>
                    <input type="text" class="form-control" id="contact_no" name="contact_no" placeholder="Phone Number.." value="{{$tenant->contact_no ??''}}">
                  </div>
                  <div class="form-group mb-4">
                    <label for="card_id">Card ID</label>
                    <input type="text" class="form-control" id="card_id" name="card_id" placeholder="Card ID.." value="{{$tenant->card_id ??''}}">
                  </div>
                  <div class="form-group mb-4">
                    <label for="company_id">Company</label>
                    <select id="company_id" name="company_id" class="form-select" required>
                      <option disabled selected> -- Select --</option>
                        @foreach($company as $c)
                        <option value='{{$c->id}}' <?php echo isset($tenant->company_id) && $tenant->company_id == $c->id ?'selected':'' ?>>{{$c->company_name}}</option>
                        @endforeach
                    </select>
                  </div>
                  <div class="form-group mb-4">
                    <label for="remarks">Remark</label>
                    <input type="text" class="form-control" id="remarks" name="remarks" placeholder="Remark.." value="{{$tenant->remarks ??''}}">
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
                  <input class="form-check-input" type="radio" name="status" id="status_active" value="1" <?php echo isset($tenant->is_active) && $tenant->is_active == 1 ? 'checked' : '' ?>>
                  <label class="form-check-label" for="status_active">
                    Active
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="radio" name="status" id="status_block" value="0" <?php echo isset($tenant->is_active) && $tenant->is_active == 0 ? 'checked' : '' ?>>
                  <label class="form-check-label" for="status_block">
                    Block
                  </label>
                </div>
              </div>
              <div class="form-group mb-4">
                <label>Lift Access</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="ckbCheckAllLift">
                  <label class="form-check-label" for="status">
                    All
                  </label>
                </div>
                @foreach($lift_access as $b)
                <div class="form-check">
                  <input class="form-check-input liftClass" type="checkbox" name="lift_access[]" value="{{$b->id}}" <?php echo isset($tenant->lift_access_floor) && in_array($b->id,$tenant->lift_access_floor) ?'checked':'' ?>>
                  <label class="form-check-label" for="access">
                    {{$b->access_level}}
                  </label>
                </div>
                @endforeach
              </div>
            </div>
          </div>
          <div class="statbox widget box box-shadow">
            <div class="widget-header">
              <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                  <h4>Access</h4>
                </div>
              </div>
            </div>
            <div class="widget-content widget-content-area">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ckbCheckAllDoor">
                <label class="form-check-label" for="access">
                  All
                </label>
              </div>
              @foreach($access as $a)
              <div class="form-check">
                <input class="form-check-input DoorClass" type="checkbox" name="access[]" value="{{$a->access_name}}" <?php echo isset($tenant->all_user_access) && in_array($a->access_name,$tenant->all_user_access) ?'checked':'' ?>>
                <label class="form-check-label" for="access">
                  {{$a->access_name}}
                </label>
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
      </form>



      <!--  BEGIN CUSTOM SCRIPTS FILE  -->
      <x-slot:footerFiles>
      <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
      <script>
      $(document).ready(function () {
          $("#ckbCheckAllLift").click(function () {
              $(".liftClass").prop('checked', $(this).prop('checked'));
          });

          $("#ckbCheckAllDoor").click(function () {
              $(".DoorClass").prop('checked', $(this).prop('checked'));
          });
      });
      </script>
        </x-slot>
        <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>