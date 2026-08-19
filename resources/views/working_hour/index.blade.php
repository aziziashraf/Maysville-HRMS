<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
		Working Hour Management | {{ env('APP_NAME') }}  
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
		<link rel="stylesheet" href="{{asset('plugins/flatpickr/flatpickr.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/noUiSlider/nouislider.min.css')}}">
        @vite(['resources/scss/light/plugins/flatpickr/custom-flatpickr.scss'])
        @vite(['resources/scss/dark/plugins/flatpickr/custom-flatpickr.scss'])

		@vite(['resources/scss/light/assets/elements/alert.scss'])        
    	@vite(['resources/scss/dark/assets/elements/alert.scss']) 
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <x-slot:scrollspyConfig>
        data-bs-spy="scroll" data-bs-target="#navSection" data-bs-offset="100"
    </x-slot>
    
    <!-- BREADCRUMB -->
    <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Components</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cards</li>
            </ol>
        </nav>
    </div>
    <!-- /BREADCRUMB -->

    <div class="row">
    
        <div id="card_1" class="col-12 layout-spacing layout-top-spacing">
			<form action="{{ route('working_hour.store') }}" method="post" enctype="multipart/form-data">
			@csrf
				<div class="statbox widget box box-shadow">
					<div class="widget-header">
						<div class="row">
							<div class="col-xl-12 col-md-12 col-sm-12 col-12">
								<h4>Working Hour Management</h4>
							</div>
							<div class="col-md-12 mt-1">
								<div class="form-group text-end">
									<button class="btn btn-primary mx-3" type="submit">Save</button>
								</div>
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
							<div class="col-12 mx-auto">
								@foreach ($working_hour as $row)
								<div class="card  @if (!$loop->first) layout-top-spacing @endif">
									<div class="card-body">
										<h5 class="card-title">Working Hour Shift</h5>
										<div class="row">
											<input type="text" name="idss[]" value="{{$row->id}}" hidden>
											<div class="col-12 mb-3">
												<label for="shift_label_{{$row->id}}">Shift Name</label>
												<input type="text" class="form-control" name="shift_label_{{$row->id}}" value="{{$row->shift_label}}" required>
											</div>
											<div class="col-12 form-group mb-3">
												<label>Working Day(s)</label>
												@foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
												<div class="form-check mx-3">
													<input class="form-check-input" type="checkbox" name="working_days_{{ $row->id }}[]" value="{{ $day }}" {{ in_array($day, $row->working_hour_days) ? 'checked' : '' }}>
													<label class="form-check-label" for="working_days">{{ $day }}</label>
												</div>
												@endforeach
											</div>
										</div>
										<h5 class="card-title">Working Time</h5>
										<div class="row">
											<div class="col-6 form-group mb-3">
												<label for="start_time_{{$row->id}}">Start Time</label>
												<input
													class="form-control"
													type="time"
													placeholder="Select Time.."
													name="start_time_{{$row->id}}" 
													value="{{$row->start_time}}"
												>
											</div>
											<div class="col-6 form-group mb-3">
												<label for="end_time_{{$row->id}}">End Time</label>
												<input
													class="form-control"
													type="time"
													placeholder="Select Time.."
													name="end_time_{{$row->id}}"
													value="{{$row->end_time}}"
												>
											</div>
										</div>
										<h5 class="card-title">Break Time</h5>
										<div class="row">
											<div class="col-6 form-group mb-3">
												<label for="break_start_time_{{$row->id}}">Start Time</label>
												<input
													class="form-control"
													type="time"
													placeholder="Select Time.."
													name="break_start_time_{{$row->id}}"
													value="{{$row->break_start_time}}"
												>
											</div>
											<div class="col-6 form-group mb-3">
												<label for="break_end_time_{{$row->id}}">End Time</label>
												<input
													class="form-control"
													type="time"
													placeholder="Select Time.."
													name="break_end_time_{{$row->id}}"
													value="{{$row->break_end_time}}"
												>
											</div>
										</div>
										<div class="text-end">
						 	 				<a type="button" class="btn btn-dark mx-2" onclick="if(confirm('Are you sure you want to delete?')){ window.location.href='{{ route('working_hour.destroy',$row) }}' }"><i class="fa fa-trash" aria-hidden="true"></i>Delete</a> 
										</div>
									</div>
								</div>
								@endforeach
								<div class="layout-top-spacing">
									<a class="btn btn-warning" onclick="if(confirm('Are you sure you want to add new?')){ addNewShift();}">+ Add More Working Hour Shift</a>
								</div>

							</div>
						</div>
					</div>
				</div>
			</form>
        </div>

    </div>
    
    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
	@vite(['resources/assets/js/custom.js'])
	<script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
	<script>
		function addNewShift(){
			$.ajax({
				url: "{{ url('/working_hour/addNewDiv') }}",
				method: 'GET',
				success: function(data) {
					location.reload();
				},
			})
		}
	</script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>