<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
		Add Notification | {{ env('APP_NAME') }} 
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        @vite(['resources/scss/light/assets/components/timeline.scss'])
		<link rel="stylesheet" type="text/css" href="{{asset('plugins/tomSelect/tom-select.default.min.css')}}">
        @vite(['resources/scss/light/plugins/tomSelect/custom-tomSelect.scss'])
        @vite(['resources/scss/dark/plugins/tomSelect/custom-tomSelect.scss'])
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
    <form action="{{ route('visitor.storeVisitorPassAccess') }}" method="post" enctype="multipart/form-data">
    <input type="text" class="form-control" id="visitor_pass_id" name="visitor_pass_id" value="{{$visitorPass->id ??''}}" hidden>
    @csrf
		<div class="row layout-top-spacing">
      <div id="basic" class="col-12  collayout-spacing">
      @if($visitorPass->status <> "Approved")
          <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button> 
      @endif
      </div>
			<div id="basic" class="col-12 col-md-6 collayout-spacing">
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
                <input class="form-check-input" type="checkbox" id="ckbCheckAllDoor"/>
                <label class="form-check-label" for="access">All</label> 
              </div>
              @foreach($access as $a)
              <div class="form-check">
                <input class="form-check-input DoorClass" type="checkbox" name="access[]" value="{{$a->access_name}}" <?php echo isset($visitorPass->all_user_access) && in_array($a->access_name,$visitorPass->all_user_access) ?'checked':'' ?>>
                <label class="form-check-label" for="access">
                  {{$a->access_name}}
                </label>
              </div>
              @endforeach
					</div>
				</div>
			</div>
      <div id="basic" class="col-12 col-md-6 collayout-spacing">
				<div class="statbox widget box box-shadow">
					<div class="widget-header">
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12 col-12">
								<h4>Lift Access</h4>
							</div>                 
						</div>
					</div>
					<div class="widget-content widget-content-area">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="ckbCheckAllLift">
              <label class="form-check-label" for="status">
                All
              </label>
            </div>
            @foreach($lift_access as $b)
            <div class="form-check">
              <input class="form-check-input liftClass" name="lift_access[]" value="{{$b->id}}" <?php echo isset($visitorPass->lift_access_floor) && in_array($b->id,$visitorPass->lift_access_floor) ?'checked':'' ?>>
              <label class="form-check-label" for="lift_access[]">
                {{$b->access_level}}
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