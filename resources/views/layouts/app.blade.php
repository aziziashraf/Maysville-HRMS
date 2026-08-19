<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ config('app.name', 'Laravel') }}</title>
  <!-- plugins:css -->
  <link href="{{ asset('vendors/flag-icon-css/css/flag-icon.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/simple-line-icons/css/simple-line-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/feather/feather.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/css/vendor.bundle.base.css') }}" rel="stylesheet">
  <!-- endinject -->
  <!-- plugin css for this page -->
  <link href="{{ asset('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/jquery-bar-rating/fontawesome-stars.css') }}" rel="stylesheet">
  <link href="{{ asset('vendors/dropify/dropify.min.css') }}" rel="stylesheet">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link href="{{ asset('css/vertical-layout-light/style.css') }}" rel="stylesheet">
  <!-- endinject -->
  <link href="{{ asset('css/select2.min.css') }}" rel="stylesheet">
  <link href="{{ asset('images/logo-color.png') }}" rel="shortcut icon">
</head>
<style>
.overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  background: rgba(0,0,0,.7);
  z-index: 99999;
}

.overlay__wrapper {
  width: 100%;
  height: 100%;
  position: relative;
}

.overlay__spinner {
  position: absolute;
  left: 50%;
  top: 20%;
}
.required_class {
  color:red;
}
</style>
<body class="sidebar-fixed">
  <div class="overlay" id="spinner">
    <div class="overlay__wrapper">
      <div class="pixel-loader" id="spinner"></div>
    </div>
  </div>
  <div class="container-scroller">
    <!-- partial:partials/_navbar.html -->
    @include('layouts.top_navbar')
    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:partials/_sidebar.html -->
      @include('layouts.side_navbar')
      <!-- partial -->
        <div class="main-panel">
        @include('layouts.flash-message')
        @yield('content')

        <!-- content-wrapper ends -->
        <!-- partial:partials/_footer.html -->
        
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script>
          $(document).ready(function () {
              $("#ckbCheckAllLift").click(function () {
                  $(".liftClass").prop('checked', $(this).prop('checked'));
              });

              $("#ckbCheckAllDoor").click(function () {
                  $(".DorrClass").prop('checked', $(this).prop('checked'));
              });
          });
          </script>
          
        <footer class="footer">
            <div class="container-fluid clearfix">
              <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © 2022 <a href="#">PXS</a>. All rights reserved.</span>
            <!--  <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Hand-crafted & made with <i class="mdi mdi-heart text-danger"></i></span>-->
            </div>
          </footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->

  <!-- plugins:js -->
  <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
  <!-- endinject -->
  <!-- Plugin js for this page-->
  <script src="{{ asset('vendors/chart.js/Chart.min.js') }}"></script>
  <script src="{{ asset('vendors/progressbar.js/progressbar.min.js') }}"></script>
  <script src="{{ asset('vendors/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
  <script src="{{ asset('vendors/jquery-bar-rating/jquery.barrating.min.js') }}"></script>
  <script src="{{ asset('vendors/jquery-sparkline/jquery.sparkline.min.js') }}"></script>
  <script src="{{ asset('vendors/raphael/raphael.min.js') }}"></script>
  <script src="{{ asset('vendors/morris.js/morris.min.js') }}"></script>
  <script src="{{ asset('vendors/jquery-steps/jquery.steps.min.js') }}"></script>
  <script src="{{ asset('vendors/jquery-validation/jquery.validate.min.js') }}"></script>
  <script src="{{ asset('vendors/dropify/dropify.min.js') }}"></script>
  <script src="{{ asset('vendors/dropzone/dropzone.js') }}"></script>
  <!-- End plugin js for this page-->
  <!-- inject:js -->
  <script src="{{ asset('js/jq.tablesort.js') }}"></script>
  <script src="{{ asset('js/off-canvas.js') }}"></script>
  <script src="{{ asset('js/hoverable-collapse.js') }}"></script>
  <script src="{{ asset('js/template.js') }}"></script>
  <script src="{{ asset('js/settings.js') }}"></script>
  <script src="{{ asset('js/todolist.js') }}"></script>
  <script src="{{ asset('js/dropify.js') }}"></script>
  <script src="{{ asset('js/dropzone.js') }}"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="{{ asset('js/dashboard.js') }}"></script>
  <script src="{{ asset('js/wizard.js') }}"></script>
  <!-- End custom js for this page-->
  <script src="{{ asset('js/select2.min.js') }}"></script>
</body>

</html>

