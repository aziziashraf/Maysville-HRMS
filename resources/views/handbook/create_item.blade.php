<x-base-layout :scrollspy="false">

@php
  if(isset($handbook)){
    $title = 'Add HandBook Item';
  }else{
    $title = 'Edit HandBook Item';
  }
@endphp    
<x-slot:pageTitle>
        {{$title}} | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        <link rel="stylesheet" href="{{asset('plugins/filepond/filepond.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/filepond/FilePondPluginImagePreview.min.css')}}">
        <link rel="stylesheet" href="{{asset('plugins/tagify/tagify.css')}}">

        @vite(['resources/scss/light/assets/forms/switches.scss'])
        @vite(['resources/scss/light/plugins/editors/quill/quill.snow.scss'])
        @vite(['resources/scss/light/plugins/editors/quill/quill.snow.scss'])
        @vite(['resources/scss/light/plugins/tagify/custom-tagify.scss'])
        @vite(['resources/scss/light/assets/apps/blog-create.scss'])

        @vite(['resources/scss/dark/assets/forms/switches.scss'])
        @vite(['resources/scss/dark/plugins/editors/quill/quill.snow.scss'])
        @vite(['resources/scss/dark/plugins/editors/quill/quill.snow.scss'])
        @vite(['resources/scss/dark/plugins/tagify/custom-tagify.scss'])
        @vite(['resources/scss/dark/assets/apps/blog-create.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BREADCRUMB -->
    <div class="page-meta">
      <nav class="breadcrumb-style-one" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('handbook.index') }}">HandBook Item</a></li>
            <li class="breadcrumb-item"><a href="{{ route('handbook.edit',$handbookField->handbookcategory_id ?? $handbook->id) }}">{{$handbookField->handbookCategory->name ?? $handbook->name}}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
        </ol>
      </nav>
    </div>
    <!-- /BREADCRUMB -->
    <form action="{{ route('handbook.store') }}" method="post" enctype="multipart/form-data">
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
                    <h4>Details</h4>
                  </div>
                </div>
                @if($errors->any())
                  @foreach ($errors->all() as $error)
                      <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
                  @endforeach
                @endif
              </div>

            <div class="widget-content widget-content-area blog-create-section">

                <!-- <div class="row mb-4">
                    <div class="col-sm-12">
                        <input type="text" id="handbook_id" name="handbook_id" value="{{$handbook->id ?? ''}}" hidden>
                        <label for="handbookcategory_id">HandBook Category</label>
                            <select id="handbookcategory_id" name="handbookcategory_id" class="form-select">
                            <option disabled selected> -- Select --</option>
                              @foreach($handbook_category as $hb_cat)
                              <option value='{{$hb_cat->id}}' <?php echo isset($handbookField->handbookcategory_id) && $handbookField->handbookcategory_id == $hb_cat->id ? 'selected' : '' ?>>
                                {{$hb_cat->name}}
                              </option>
                              @endforeach
                            </select>
                    </div>
                </div> -->

                <input type="text" id="handbook_id" name="handbook_id" value="{{$handbookField->id ?? ''}}" hidden>
                <input type="text" id="handbookcategory_id" name="handbookcategory_id" value="{{$handbookField->handbookcategory_id ?? $handbook->id}}" hidden>

                <div class="form-group mb-4">
                  <label for="name">HandBook Item's Title</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Title.." value="{{$handbookField->name ??''}}" required>
                </div>

                <div class="form-group mb-4">
                  <label for="description">Description</label>
                  <input type="text" class="form-control" id="description" name="description" placeholder="Description.." value="{{$handbookField->description ??''}}" required>
                </div>

                <div class="form-group mb-4">
                  <label for="description">Index Number (positioning purpose)</label>
                  <input type="text" class="form-control" id="index_number" name="index_number" placeholder="e.g a,b,c" value="{{$handbookField->index_number ??''}}" required>
                </div>

                <div class="row mb-4">
                    <div class="col-sm-12">
                        <label>HandBook Item's Content</label>
                        <div id="handbook-editor">{!! isset($content) ? $content : '' !!}</div>
                        <input type="hidden" id="handbook-editor-input" id="content" name="content" value="{{$handbookField->content ?? ''}}" required>
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
                    <input class="form-check-input" type="radio" name="status" id="status_publish" value="1" <?php echo isset($handbookField) && $handbookField->status == 1 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_publish">
                      Publish
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="status" id="status_unpublish" value="0" <?php echo isset($handbookField) && $handbookField->status == 0 ? 'checked' : '' ?>>
                    <label class="form-check-label" for="status_unpublish">
                      Unpublish
                    </label>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </div>
    
    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        <script src="{{asset('plugins/editors/quill/quill.js')}}"></script>
        <script src="{{asset('plugins/filepond/filepond.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/FilePondPluginFileValidateType.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/FilePondPluginImageExifOrientation.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/FilePondPluginImagePreview.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/FilePondPluginImageCrop.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/FilePondPluginImageResize.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/FilePondPluginImageTransform.min.js')}}"></script>
        <script src="{{asset('plugins/filepond/filepondPluginFileValidateSize.min.js')}}"></script>
        <script src="{{asset('plugins/tagify/tagify.min.js')}}"></script>

        @if(isset($handbook))
        <script>
            $(document).ready(function () {
                $.ajax({
                    url: '/get-handbook-content/{{$handbook->id}}',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        quill.root.innerHTML = response.content;
                    },
                    error: function(error) {
                        console.error('Error fetching content: ' + error);
                    }
                });
            });
        </script>
        @endif

        @vite(['resources/assets/js/apps/rich-text-handbook.js'])
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>