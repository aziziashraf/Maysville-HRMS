<x-base-layout :scrollspy="false">
	@php
		if($notification){
			$title = "Edit Announcement";
	 	}else{
			$title = "Add Announcement";
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
                <li class="breadcrumb-item"><a href="{{ route('notification.index') }}">Announcement</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
            </ol>
        </nav>
    </div>
    <!-- /BREADCRUMB -->
    <form id="notification_form" action="{{ route('notification.store') }}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="row layout-top-spacing">
			@if(!isset($show))
			<div id="basic" class="col-12  collayout-spacing">
				<a class="btn btn-primary mb-2 me-0" style="float:right" onclick="saveDraft()">Save as Draft</a> 
				<a class="btn btn-primary mb-2 me-2" style="float:right" onclick="saveSend()">Save & Send</a>  
			</div>
			@endif
			<div id="basic" class="col-12 col-md-8  collayout-spacing">
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
								<input type="text" class="form-control" id="notification_id" name="notification_id" value="{{$notification->id ??''}}" hidden>
                      			<input type="text" class="form-control" id="status" name="status" value="{{$notification->status ??''}}" hidden>
								<div class="form-group mb-4">
									<label for="title">Title</label>
									<input id="t-text" type="text" class="form-control" id="title" name="title" placeholder="Title.." value="{{$notification->title ??''}}" <?php echo isset($show)?'disabled':'required'?>>
								</div>
								<div class="form-group mb-4">
									<label for="short_descriptions">Short Description</label>
									<input type="text" class="form-control" id="short_descriptions" name="short_descriptions" placeholder="Short Descriptions.." value="{{$notification->short_descriptions ??''}}" <?php echo isset($show)?'readonly':''?>>
								</div>
								<div class="form-group mb-4">
									<label for="descriptions">Descriptions</label>
									<textarea class="form-control" id="descriptions" name="descriptions" placeholder="Descriptions" rows="5" <?php echo isset($show)?'readonly':''?>>{{$notification->descriptions ??''}}</textarea>
								</div>
							</div>                                        
						</div>

					</div>
				</div>
			</div>
			<div id="basic" class="col-12 col-md-4  collayout-spacing">
				<div class="statbox widget box box-shadow">
					<div class="widget-header">
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12 col-12">
								<h4>Settings</h4>
							</div>                 
						</div>
					</div>
					<div class="widget-content widget-content-area">

						<div class="row">
							<div class="col-12">
								<div class="form-check">
									<input class="form-check-input" type="radio" name="notification_type" id="notification_type_all_employee" value="all_employee" onclick="hideall()" <?php echo isset($notification->notification_type)&&$notification->notification_type == "all_employee"?'checked':'' ?> <?php echo isset($show)?'disabled':''?>>
									<label class="form-check-label" for="notification_type_all_employee">
										All Employee
									</label>
								</div>
								
								<div class="form-check">
									<input class="form-check-input" type="radio" name="notification_type" id="notification_type_specific_division" value="specific_division" onclick="checkDepartment()" <?php echo isset($notification->notification_type)&&$notification->notification_type == "specific_division"?'checked':'' ?> <?php echo isset($show)?'disabled':''?>>
									<label class="form-check-label" for="notification_type_specific_division">
										Specific Department
									</label>
								</div>

								<div class="form-group" id="department_ids" <?php echo isset($notification->notification_type)&&$notification->notification_type == "specific_division"?'':'style="display:none"' ?>>
									<label for="department">Departments<span style="color:red"> (Multiple Select)</span></label><br>
									<select id="departmentSelect" name="department[]" multiple="multiple" <?php echo isset($show)?'disabled':''?>>
										@foreach($department as $d)
										<option value='{{$d->id}}' <?php echo isset($notification->department_idss)&&in_array($d->id,$notification->department_idss) ?'selected':'' ?>>{{$d->department_name ??''}}</option>
										@endforeach
									</select>
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
		<script>

			function checkDepartment(){
				var x = document.getElementById("department_ids");
				var y = document.getElementById("tenant_ids");
				if (window.getComputedStyle(x).display === "none") {
					x.style.display = "block";
					y.style.display = "none";
				}
			}

			function checkTenant(){
				var x = document.getElementById("tenant_ids");
				var y = document.getElementById("department_ids");
				if (window.getComputedStyle(x).display === "none") {
					x.style.display = "block";
					y.style.display = "none";
				}
			}

			function hideall(){
				var x = document.getElementById("tenant_ids");
				var y = document.getElementById("department_ids");
				x.style.display = "none";
				y.style.display = "none";
				
			}

			function saveDraft(){
				document.getElementById("status").value = "Draft";
				document.getElementById("notification_form").submit();
			}

			function saveSend(){
				document.getElementById("status").value = "Sent";
				document.getElementById("notification_form").submit();
			}
		</script>
		<script src="{{asset('plugins/tomSelect/tom-select.base.js')}}"></script>
		<script>
			new TomSelect("#departmentSelect",{
			});
			new TomSelect("#tenantSelect",{
			});
		</script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>