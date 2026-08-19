<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        HandBook | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        @vite(['resources/scss/light/assets/pages/faq.scss'])
        @vite(['resources/scss/dark/assets/pages/faq.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->
    
    <div class="faq">
    
        <div class="faq-layouting layout-spacing">

            <div class="fq-tab-section">
                <div class="row">
                    <div class="col-md-12">

                        <h2>The <span>HandBook</span></h2>

                        <div class="row">
                            
                            <div class="col-lg-12">

                            <div class="accordion" id="simple_faq">
                            @if($categories)
                                @foreach($categories as $categoryName => $categoryItems)
                                    <h5 style="margin-top: 30px;">{{ $categoryItems['index'] }}. {{ $categoryName }}</h5>
                                    @foreach($categoryItems['items'] as $item)
                                        <div class="card">
                                            <div class="card-header" id="fqheading{{ $item['item']->id }}">
                                                <div class="mb-0" data-bs-toggle="collapse" role="navigation" data-bs-target="#fqcollapse{{ $item['item']->id }}" aria-expanded="false" aria-controls="fqcollapse{{ $item['item']->id }}">
                                                    <span class="faq-q-title">{{ $item['item']->index_number }}. {{ $item['item']->name }}</span>
                                                    <div class="icons"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
                                                </div>
                                            </div>
                                            <div id="fqcollapse{{ $item['item']->id }}" class="collapse" aria-labelledby="fqheading{{ $item['item']->id }}" data-bs-parent="#simple_faq">
                                                <div class="card-body">
                                                    <p>{!! $item['item']->content !!}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endforeach
                            @else
                                <div class="card">
                                    <div class="card-header" id="fqheadingOne">
                                        <div class="mb-0" data-bs-toggle="collapse" role="navigation" data-bs-target="#fqcollapseOne" aria-expanded="false" aria-controls="fqcollapseOne">
                                            <span class="faq-q-title">No Data Available</span> <div class="icons"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
                                        </div>
                                    </div>
                                    <div id="fqcollapseOne" class="collapse" aria-labelledby="fqheadingOne" data-bs-parent="#simple_faq">
                                        <div class="card-body">
                                            <p>Nothing has been published yet.</p>
                                        </div>
                                    </div>
                                </div>                            
                            @endif
                            </div>


                            </div>

                            <!-- <div class="col-lg-6">
                                
                                <div class="accordion" id="simple_faq1">
                                    <h5 style="margin-top: 30px;">This part id hard-coded.</h5>
                                    <div class="card">
                                        <div class="card-header" id="fqheadingOne1">
                                            <div class="mb-0" data-bs-toggle="collapse" role="navigation" data-bs-target="#fqcollapseOne1" aria-expanded="false" aria-controls="fqcollapseOne1">
                                                <span class="faq-q-title">Are images are provided in the download version?</span> <div class="icons"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg></div>
                                            </div>
                                        </div>
                                        <div id="fqcollapseOne1" class="collapse" aria-labelledby="fqheadingOne1" data-bs-parent="#simple_faq1">
                                            <div class="card-body">
                                                <p>Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et. Nihil anim keffiyeh helvetica, craft beer labore wes anderson cred nesciunt sapiente ea proident. Ad vegan excepteur butcher vice lomo. Leggings occaecat craft beer farm-to-table, raw denim aesthetic synth nesciunt you probably haven't heard of them accusamus labore sustainable VHS.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div> -->


                        </div>

                    </div>
                </div>                            
            </div>

        </div>

    </div>
    
    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>

    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>