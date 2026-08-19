<x-base-layout :scrollspy="false">
	@php
		if($role){
			$title = 'Edit Role';
		} else {
			$title = 'Add Role';
		}
	@endphp
	<x-slot:pageTitle>
		{{$title}} | {{ env('APP_NAME') }}
	</x-slot>

	<!-- BEGIN GLOBAL MANDATORY STYLES -->
	<x-slot:headerFiles>
		<!--  BEGIN CUSTOM STYLE FILE  -->
		<!--  END CUSTOM STYLE FILE  -->
	</x-slot>
	<!-- END GLOBAL MANDATORY STYLES -->

	<!-- BREADCRUMB -->
	<div class="page-meta">
		<nav class="breadcrumb-style-one" aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="{{ route('user.role_index') }}">User Role</a></li>
				<li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
			</ol>
		</nav>
	</div>
	<!-- /BREADCRUMB -->

	<form action="{{ route('user.role_store') }}" method="post" enctype="multipart/form-data">
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
								<h4>Role Details</h4>
							</div>
						</div>
					</div>
					<div class="widget-content widget-content-area">
						<div class="row">
							<input type="text" id="role_id" name="role_id" value="{{$role->id ??''}}" hidden>
							<div class="col-12 col-md-6 mb-4">
								<label for="name">Name</label>
								<input type="text" class="form-control" id="name" name="name" placeholder="Name...." value="{{$role->name ??''}}" required>
							</div>
							<div class="col-12 col-md-6 mb-4">
								<label for="title">Title</label>
								<input type="text" class="form-control" id="title" name="title" placeholder="Title...."  value="{{$role->title ??''}}" required>
							</div>
						</div>

						<div class="card">
							<div class="card-header">
								<div class="row">
									<div class="col-md-6">
										<h5>
											<b>Abilities</b>
										</h5>
										Please tick on the abilities to assign
									</div>
									<div class="col-md-6" style="text-align: right">
										<div class="form-check">
										<input type="checkbox" id="selectAll" >
										<label for=""><b style="color:black;">Select All</b></label>
										</div> 
									</div>
								</div>
							</div>
							<div class="card-body">
							<div class="form-check form-check-inline">
								<div class="row">
									@foreach ($allAbilities as $ability)
										<div class="col-4 px-3">
											<input type="checkbox"
											name="abilities[]"
											id="ability_{{ $ability->id }}"
											value="{{ $ability->id }}"
											@if(isset($role->abilities) && $role->abilities->where('id', $ability->id)->first())
											checked
											@endif
											>
											<label for="ability_{{ $ability->id }}">{{ $ability->title }}</label>
										</div>
									@endforeach
								</div>
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
		@vite(['resources/assets/js/custom.js'])
		<script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
		<script>
		$(document).ready(function () {
			$("#selectAll").click(function() {
				console.log("yes");
			$("input[type=checkbox]").prop("checked", $(this).prop("checked"));
			});

			$("input[type=checkbox]").click(function() {
			if (!$(this).prop("checked")) {
				$("#selectAll").prop("checked", false);
			}
			});
		});
		</script>
	</x-slot>
	<!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>