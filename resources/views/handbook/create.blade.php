<x-base-layout :scrollspy="false">
  @php
    if(isset($handbook_category)){
      $title = 'Edit HandBook Item';
    }else{
      $title = 'Add HandBook Item';
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
        <li class="breadcrumb-item"><a href="{{ route('handbook.index') }}">HandBook Item</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->
  <form action="{{ route('handbook_category.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="row layout-top-spacing">
          <!-- <div id="basic" class="col-12  collayout-spacing">
            <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right" id="saveSend">Save</button>
          </div> -->
          <div id="basic" class="col-12 col-md-8 collayout-spacing">
            <div class="statbox widget box box-shadow layout-spacing">
              <div class="widget-header">
                <div class="row">
                  <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>HandBook Category</h4>
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
                      <input type="text" class="form-control" id="name" name="name" placeholder="Name.." value="{{$handbook_category->name ??''}}" readonly required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="description">Description</label>
                      <input type="text" class="form-control" id="description" name="description" placeholder="Description.." value="{{$handbook_category->description ??''}}" readonly required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="description">Index Number (positioning purpose)</label>
                      <input type="text" class="form-control" id="index_number" name="index_number" placeholder="e.g. 1,2,3" value="{{$handbook_category->index_number ??''}}" readonly required>
                    </div>
                    <div class="form-group mb-4">
                      <label for="description">To change HandBook's Category details <a href="{{ route('handbook_category.edit',$handbook_category->id) }}" class="btn btn-primary mb-2 me-4">Click here</a></label>
                      <!-- <input type="text" class="form-control" id="index_number" name="index_number" placeholder="e.g. 1,2,3" value="{{$handbook_category->index_number ??''}}" readonly required> -->
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
                    <input class="form-check-input" type="radio" name="status" id="status_publish" value="1" disabled <?php echo isset($handbook_category) && $handbook_category->status == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_active">
                      Publish
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_unpublish" value="0" disabled <?php echo isset($handbook_category) && $handbook_category->status == 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_block">
                      Unpublish
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        @if(isset($handbook_category))
        <div class="statbox widget box box-shadow">
          <div class="widget-header">
            <div class="row">
              <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                <h4>({{ $handbook_category->name }}) HandBook Item</h4>
              </div>
            </div>
          </div>
          <div class="widget-content widget-content-area">
            <a href="{{ route('handbook.createHandbookItem',$handbook_category->id) }}" class="btn btn-primary mb-2 me-4">Add HandBook Item</a>
            <div class="table-responsive">
              <table class="table table-striped table-bordered">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($handbook_category->handbook as $row)
                  <tr>
                    <td>{{$row->name}}</td>
                    <td>
                      @if(strlen($row->description ?? '') > 30)
                        {{ substr($row->description ?? '', 0, 30) . '...' }}
                      @else
                        {{ $row->description ?? '' }}
                      @endif
                    </td>
                    <td>
                      @if($row->status == 1)
                        Publish
                      @else
                        Unpublish
                      @endif
                    </td>
                    <td class="text-center">
                      <div class="action-btns">
                        <a href="{{ route('handbook.editHandbookItem',$row) }}" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="Edit">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-edit-2"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                        </a>
                        <a onclick="if(confirm('Are you sure you want to delete this handbook item?')){ window.location.href='{{ route('handbook.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </a>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="4" class="text-center">No data available</td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
          </div>
        </div>
        @endif
</form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>