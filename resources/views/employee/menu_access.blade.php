<x-base-layout :scrollspy="false">

    <x-slot:pageTitle>
        Menu Access — {{ $employee->name }} | {{ env('APP_NAME') }}
    </x-slot>

    <!-- BEGIN GLOBAL MANDATORY STYLES -->
    <x-slot:headerFiles>
        <!--  BEGIN CUSTOM STYLE FILE  -->
        @vite(['resources/scss/light/assets/elements/alert.scss'])
        @vite(['resources/scss/dark/assets/elements/alert.scss'])
        @vite(['resources/scss/light/assets/forms/switches.scss'])
        @vite(['resources/scss/dark/assets/forms/switches.scss'])
        <!--  END CUSTOM STYLE FILE  -->
    </x-slot>
    <!-- END GLOBAL MANDATORY STYLES -->

    <!-- BREADCRUMB -->
    <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">Employee Management</a></li>
                <li class="breadcrumb-item active" aria-current="page">Menu Access — {{ $employee->name }}</li>
            </ol>
        </nav>
    </div>
    <!-- /BREADCRUMB -->

    <div class="row layout-top-spacing">
        <div class="col-xl-9 col-lg-12 col-sm-12 mx-auto layout-spacing">

            @if(session('success'))
                <div class="alert alert-light-success alert-dismissible fade show border-0 mb-4" role="alert">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('employee_menu_access.update', $employee) }}" method="post">
                @csrf

                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start flex-wrap mb-1">
                            <div>
                                <h5 class="card-title mb-1">Menu Access</h5>
                                <div class="text-muted">
                                    {{ $employee->name }}
                                    {{ $employee->staff_id ? ' · '.$employee->staff_id : '' }}
                                    {{ $employee->department?->department_name ? ' · '.$employee->department->department_name : '' }}
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                        <p class="text-muted mt-3" style="max-width: 720px;">
                            Switch off any sidebar item this staff member should not have. Turning an item
                            off both removes it from their menu and blocks the page itself, so the link
                            cannot simply be typed in.
                        </p>

                        <div class="widget-content widget-content-area br-8 mt-4">
                            <table class="table table-striped" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width: 110px;">Assigned</th>
                                        <th>Menu Item</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $key => $item)
                                        @php $state = $assigned[$key]; @endphp
                                        <tr>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                           id="menu_{{ $key }}"
                                                           name="menu[{{ $key }}]" value="1"
                                                           {{ $state['visible'] ? 'checked' : '' }}
                                                           {{ $state['blocked_by_role'] ? 'disabled' : '' }}>
                                                </div>
                                            </td>
                                            <td>
                                                <label for="menu_{{ $key }}" class="mb-0" style="cursor:pointer;">
                                                    <strong>{{ $item['label'] }}</strong>
                                                </label>
                                                @if(!empty($item['description']))
                                                    <div class="text-muted" style="font-size:12px;">{{ $item['description'] }}</div>
                                                @endif
                                            </td>
                                            <td style="font-size:12px;">
                                                @if($state['blocked_by_role'])
                                                    <span class="badge badge-light-warning">Blocked by role</span>
                                                    <div class="text-muted mt-1">
                                                        Their role does not grant <code>{{ $item['ability'] }}</code>,
                                                        so this stays hidden regardless of the switch.
                                                    </div>
                                                @elseif($state['saved'])
                                                    <span class="badge badge-light-primary">Set for this staff</span>
                                                @else
                                                    <span class="badge badge-light-secondary">Using default</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-4 gap-2">
                            <a href="{{ route('employee_menu_access.reset', $employee) }}"
                               onclick="return confirm('Reset {{ $employee->name }} back to the default menu?')"
                               class="btn btn-outline-secondary">Reset to defaults</a>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>

                    </div>
                </div>
            </form>

        </div>
    </div>

    <!--  BEGIN CUSTOM SCRIPTS FILE  -->
    <x-slot:footerFiles>
        <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
    </x-slot>
    <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>
