<x-base-layout :scrollspy="false">
  @php
    $title = isset($type) ? 'Edit Information Type' : 'Add Information Type';
    $existingFields = isset($type) ? $type->customFieldList() : [];
  @endphp

  <x-slot:pageTitle>
    {{$title}} | {{ env('APP_NAME') }}
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
        <li class="breadcrumb-item"><a href="{{ route('employee_document_type.index') }}">Employee Information Type</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->

  <form action="{{ route('employee_document_type.store') }}" method="post">
    @csrf
    <input type="hidden" name="employee_document_type_id" value="{{ $type->id ?? '' }}">

    <div class="row layout-top-spacing">
      <div class="col-12 collayout-spacing">
        <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right">Save</button>
      </div>

      <div class="col-12 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12">
                <h4>Details</h4>
              </div>
            </div>
            @if($errors->any())
              @foreach ($errors->all() as $error)
                <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-0" role="alert">
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  <strong>Error!</strong> {{ $error }}
                </div>
              @endforeach
            @endif
          </div>

          <div class="widget-content widget-content-area">
            <div class="row">
              <div class="form-group col-12 col-md-8 mb-4">
                <label for="name">Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name"
                       placeholder="e.g. Safety Passport, Forklift Licence, Medical Checkup"
                       value="{{ old('name', $type->name ?? '') }}" required>
                <small class="text-muted">This is the label employees and admins will see.</small>
              </div>

              <div class="form-group col-12 col-md-4 mb-4">
                <label for="sort_order">Display Order</label>
                <input type="number" class="form-control" id="sort_order" name="sort_order" min="0"
                       value="{{ old('sort_order', $type->sort_order ?? 0) }}">
              </div>

              <div class="form-group col-12 mb-4">
                <label for="description">Description</label>
                <input type="text" class="form-control" id="description" name="description"
                       placeholder="Optional note about what this covers"
                       value="{{ old('description', $type->description ?? '') }}">
              </div>
            </div>

            <hr class="mb-4">

            <div class="row">
              <div class="col-12 mb-3"><h6>Behaviour</h6></div>

              <div class="form-group col-12 col-md-4 mb-4">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch" id="has_expiry" name="has_expiry" value="1"
                         {{ old('has_expiry', $type->has_expiry ?? true) ? 'checked' : '' }}>
                  <label class="form-check-label" for="has_expiry">Tracks an expiry date</label>
                </div>
                <small class="text-muted">Turn on for certifications and licences that lapse.</small>
              </div>

              <div class="form-group col-12 col-md-4 mb-4">
                <label for="expiry_warning_days">Warn this many days before expiry</label>
                <input type="number" class="form-control" id="expiry_warning_days" name="expiry_warning_days" min="1" max="3650"
                       value="{{ old('expiry_warning_days', $type->expiry_warning_days ?? 30) }}">
                <small class="text-muted">Drives the dashboard alert for this type.</small>
              </div>

              <div class="form-group col-12 col-md-4 mb-4">
                <div class="form-check form-switch mb-3">
                  <input class="form-check-input" type="checkbox" role="switch" id="requires_attachment" name="requires_attachment" value="1"
                         {{ old('requires_attachment', $type->requires_attachment ?? false) ? 'checked' : '' }}>
                  <label class="form-check-label" for="requires_attachment">Require a file upload</label>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1"
                         {{ old('is_active', $type->is_active ?? true) ? 'checked' : '' }}>
                  <label class="form-check-label" for="is_active">Active</label>
                </div>
              </div>
            </div>

            <hr class="mb-4">

            <div class="row">
              <div class="col-12 mb-2">
                <h6>Extra Fields</h6>
                <p class="text-muted" style="font-size: 13px; max-width: 720px;">
                  Employee name, reference number, issuer, issue date, expiry date, remarks and file
                  attachments are always available. Add rows here only for information specific to this
                  type &mdash; a grade, a class, an issuing country. They appear on the entry form automatically.
                </p>
              </div>

              <div class="col-12">
                <div id="custom-fields-wrapper"></div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-custom-field">
                  + Add Field
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/global/vendors.min.js')}}"></script>
    <script>
      // Existing rows, plus anything the user had typed before a validation bounce.
      var existingFields = @json(old('custom_fields', $existingFields));
      var fieldIndex = 0;

      function fieldRow(field) {
        field = field || {};
        var i = fieldIndex++;
        var types = ['text', 'number', 'date', 'textarea'];
        var labels = { text: 'Text', number: 'Number', date: 'Date', textarea: 'Long text' };

        var options = types.map(function (t) {
          var selected = (field.type === t) ? ' selected' : '';
          return '<option value="' + t + '"' + selected + '>' + labels[t] + '</option>';
        }).join('');

        var row = document.createElement('div');
        row.className = 'row align-items-end custom-field-row mb-3';
        row.innerHTML =
          '<input type="hidden" name="custom_fields[' + i + '][key]" value="' + (field.key ? escapeAttr(field.key) : '') + '">' +
          '<div class="form-group col-12 col-md-5 mb-2">' +
            '<label>Field Label</label>' +
            '<input type="text" class="form-control" name="custom_fields[' + i + '][label]" placeholder="e.g. Grade" value="' + (field.label ? escapeAttr(field.label) : '') + '">' +
          '</div>' +
          '<div class="form-group col-12 col-md-3 mb-2">' +
            '<label>Input Type</label>' +
            '<select class="form-select" name="custom_fields[' + i + '][type]">' + options + '</select>' +
          '</div>' +
          '<div class="form-group col-8 col-md-2 mb-2">' +
            '<div class="form-check form-switch">' +
              '<input class="form-check-input" type="checkbox" role="switch" name="custom_fields[' + i + '][required]" value="1"' + (field.required ? ' checked' : '') + '>' +
              '<label class="form-check-label">Required</label>' +
            '</div>' +
          '</div>' +
          '<div class="form-group col-4 col-md-2 mb-2 text-end">' +
            '<button type="button" class="btn btn-outline-danger btn-sm remove-custom-field">Remove</button>' +
          '</div>';

        row.querySelector('.remove-custom-field').addEventListener('click', function () {
          row.remove();
        });

        return row;
      }

      function escapeAttr(value) {
        return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }

      var wrapper = document.getElementById('custom-fields-wrapper');

      if (Array.isArray(existingFields)) {
        existingFields.forEach(function (field) {
          wrapper.appendChild(fieldRow(field));
        });
      }

      document.getElementById('add-custom-field').addEventListener('click', function () {
        wrapper.appendChild(fieldRow());
      });

      // Keep the warning-days input relevant to the expiry switch.
      var hasExpiry = document.getElementById('has_expiry');
      var warnDays = document.getElementById('expiry_warning_days');

      function syncExpiry() {
        warnDays.disabled = !hasExpiry.checked;
      }

      hasExpiry.addEventListener('change', syncExpiry);
      syncExpiry();
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>
