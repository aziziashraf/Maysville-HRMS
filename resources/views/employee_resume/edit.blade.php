<x-base-layout :scrollspy="false">
  @php
    // Field spec per repeating section: name, label, input type, column width.
    $fieldSpec = [
      'experience' => [
        ['company',     'Company',     'text',     4],
        ['position',    'Position',    'text',     4],
        ['start_date',  'From',        'text',     2],
        ['end_date',    'To',          'text',     2],
        ['description', 'Responsibilities', 'textarea', 12],
      ],
      'education' => [
        ['institution',    'Institution',    'text', 4],
        ['qualification',  'Qualification',  'text', 3],
        ['field_of_study', 'Field of Study', 'text', 3],
        ['start_year',     'From',           'text', 1],
        ['end_year',       'To',             'text', 1],
      ],
      'skills' => [
        ['name',  'Skill', 'text',   8],
        ['level', 'Level', 'select:Basic,Intermediate,Advanced,Expert', 4],
      ],
      'languages' => [
        ['name',        'Language',    'text',   8],
        ['proficiency', 'Proficiency', 'select:Basic,Conversational,Fluent,Native', 4],
      ],
      'references' => [
        ['name',     'Name',     'text', 3],
        ['position', 'Position', 'text', 3],
        ['company',  'Company',  'text', 3],
        ['contact',  'Contact',  'text', 3],
      ],
    ];

    $existing = [];
    foreach (array_keys($sections) as $key) {
        $existing[$key] = old($key, $resume->section($key));
    }
  @endphp

  <x-slot:pageTitle>
    Resume — {{ $employee->name }} | {{ env('APP_NAME') }}
  </x-slot>

  <!-- BEGIN GLOBAL MANDATORY STYLES -->
  <x-slot:headerFiles>
    <!--  BEGIN CUSTOM STYLE FILE  -->
    @vite(['resources/scss/light/assets/elements/alert.scss'])
    @vite(['resources/scss/dark/assets/elements/alert.scss'])
    <!--  END CUSTOM STYLE FILE  -->
    <style>
      .resume-row { border: 1px solid #DDE2ED; border-radius: 8px; padding: 14px; margin-bottom: 12px; background: #fff; }
      .resume-row .remove-row { float: right; }
    </style>
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

  <!-- BREADCRUMB -->
  <div class="page-meta">
    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">Employee Management</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employee_document.employee', $employee) }}">{{ $employee->name }}</a></li>
        <li class="breadcrumb-item active" aria-current="page">Resume</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->

  <div class="row layout-top-spacing">
    <div class="col-12 layout-spacing">

      @if(session('success'))
        <div class="alert alert-light-success alert-dismissible fade show border-0 mb-4" role="alert">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-4" role="alert">
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          {{ session('error') }}
        </div>
      @endif

      <form action="{{ route('employee_resume.update', $employee) }}" method="post">
        @csrf

        <div class="card mb-4">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap">
              <div>
                <h5 class="card-title mb-1">Resume</h5>
                <div class="text-muted">
                  {{ $employee->name }}
                  {{ $employee->staff_id ? ' · '.$employee->staff_id : '' }}
                  {{ $employee->department?->department_name ? ' · '.$employee->department->department_name : '' }}
                </div>
              </div>
              <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('employee_resume.preview', $employee) }}" target="_blank" class="btn btn-outline-primary">Preview</a>
                <a href="{{ route('employee_resume.download', $employee) }}" class="btn btn-outline-primary">Download PDF</a>
                <button type="submit" class="btn btn-primary">Save</button>
              </div>
            </div>

            @if($errors->any())
              <div class="mt-3">
                @foreach ($errors->all() as $error)
                  <div class="alert alert-light-danger border-0 mb-2" role="alert">{{ $error }}</div>
                @endforeach
              </div>
            @endif

            <hr>

            <div class="row">
              <div class="form-group col-12 col-md-5 mb-4">
                <label for="headline">Headline</label>
                <input type="text" class="form-control" id="headline" name="headline"
                       placeholder="e.g. Mechanical Technician — 8 years in plant turnaround"
                       value="{{ old('headline', $resume->headline) }}">
              </div>
              <div class="form-group col-12 col-md-7 mb-4">
                <label for="summary">Profile Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="3"
                          placeholder="A short paragraph introducing the employee">{{ old('summary', $resume->summary) }}</textarea>
              </div>
            </div>
          </div>
        </div>

        @foreach($sections as $key => $section)
          <div class="card mb-4">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0">{{ $section['label'] }}</h6>
                <button type="button" class="btn btn-outline-primary btn-sm" data-add="{{ $key }}">+ Add</button>
              </div>
              <div id="rows-{{ $key }}"></div>
            </div>
          </div>
        @endforeach

        <div class="d-flex justify-content-end mb-5">
          <button type="submit" class="btn btn-primary">Save Resume</button>
        </div>
      </form>

    </div>
  </div>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
    <script>
      var fieldSpec = @json($fieldSpec);
      var existing  = @json($existing);
      var counters  = {};

      function esc(v) {
        return String(v == null ? '' : v)
          .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
          .replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }

      function buildRow(section, values) {
        values = values || {};
        var i = (counters[section] = (counters[section] || 0) + 1) - 1;

        var html = '<button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button><div class="row">';

        fieldSpec[section].forEach(function (spec) {
          var name = spec[0], label = spec[1], type = spec[2], width = spec[3];
          var field = section + '[' + i + '][' + name + ']';
          var value = values[name] || '';
          var control;

          if (type === 'textarea') {
            control = '<textarea class="form-control" rows="2" name="' + field + '">' + esc(value) + '</textarea>';
          } else if (type.indexOf('select:') === 0) {
            var opts = type.slice(7).split(',').map(function (o) {
              return '<option value="' + esc(o) + '"' + (value === o ? ' selected' : '') + '>' + esc(o) + '</option>';
            }).join('');
            control = '<select class="form-select" name="' + field + '"><option value="">—</option>' + opts + '</select>';
          } else {
            control = '<input type="text" class="form-control" name="' + field + '" value="' + esc(value) + '">';
          }

          html += '<div class="form-group col-12 col-md-' + width + ' mb-2">' +
                    '<label style="font-size:12px;">' + esc(label) + '</label>' + control +
                  '</div>';
        });

        html += '</div>';

        var row = document.createElement('div');
        row.className = 'resume-row';
        row.innerHTML = html;
        row.querySelector('.remove-row').addEventListener('click', function () { row.remove(); });

        return row;
      }

      Object.keys(fieldSpec).forEach(function (section) {
        var wrap = document.getElementById('rows-' + section);
        var rows = existing[section] || [];

        rows.forEach(function (values) { wrap.appendChild(buildRow(section, values)); });

        // Always leave one blank row ready to type into.
        if (!rows.length) { wrap.appendChild(buildRow(section, {})); }
      });

      document.querySelectorAll('[data-add]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          var section = btn.getAttribute('data-add');
          document.getElementById('rows-' + section).appendChild(buildRow(section, {}));
        });
      });
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>
