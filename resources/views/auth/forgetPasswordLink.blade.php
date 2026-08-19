<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Password Reset | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        @vite(['resources/scss/light/assets/authentication/auth-boxed.scss'])
        @vite(['resources/scss/dark/assets/authentication/auth-boxed.scss'])
        
        @vite(['resources/scss/light/assets/elements/alert.scss'])        
        @vite(['resources/scss/dark/assets/elements/alert.scss'])

        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->
    
    <div class="auth-container d-flex h-100">

        <div class="container mx-auto align-self-center">
    
            <div class="row">
    
                <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-8 col-12 d-flex flex-column align-self-center mx-auto">
                    <div class="card mt-3 mb-3">
                        <div class="card-body">
                            <div class="row">
                              <form action="{{ route('reset_password_post') }}" method="POST">
                              @csrf
                                <div class="col-12 text-center">
                                  <img src="{{Vite::asset('resources/images/logo-no-background.png')}}" alt="logo" class="img-fluid" style="width: 100%; padding: 15px;">
                                </div>
                                <div class="col-md-12 mb-3">
                                  <h2>Reset Password</h2>
                                  <p>Key in your email and next</p>
                                  @if($errors->any())
                                    @foreach ($errors->all() as $error)
                                      <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-4" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
                                    @endforeach
                                  @endif
                                </div>
                                <input type="text" name="token" id="token" value="{{$token??''}}" hidden>
                                <div class="col-md-12">
                                  <div class="mb-2">
                                    <label class="email">Email</label>
                                    <input type="email" id="email"  name="email" class="form-control">
                                  </div>
                                </div>
                                <div class="col-md-12">
                                  <div class="mb-2">
                                    <label class="password">Password</label>
                                    <input type="password" id="password"  name="password" class="form-control">
                                  </div>
                                </div>
                                <div class="col-md-12">
                                  <div class="mb-2">
                                    <label class="password_confirmation">Confirm Password</label>
                                    <input type="password" id="password_confirmation"  name="password_confirmation" class="form-control">
                                  </div>
                                </div>
                                <div class="col-12">
                                  <div class="mb-2">
                                    <button class="btn btn-secondary w-100" type="submit">RESET PASSWORD</button>
                                  </div>
                                </div>
                              </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>

    </div>
    
    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        <script src="{{asset('plugins/bootstrap/bootstrap.bundle.min.js')}}"></script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>