<x-base-layout :scrollspy="false">
  @php
    $title = isset($document) ? 'Edit Employee Information' : 'Add Employee Information';
    $selectedTypeId = old('employee_document_type_id', $document->employee_document_type_id ?? '');
    $selectedUserId = old('user_id', $preselectedUserId ?? '');
    $existingValues = old('custom_values', isset($document) ? ($document->custom_values ?? []) : []);

    // Field definitions for every selectable type, handed to the browser so
    // changing the dropdown re-renders the form without a round trip.
    $typeConfig = [];
    foreach ($types as $t) {
        $typeConfig[$t->id] = [
            'name' => $t->name,
            'has_expiry' => (bool) $t->has_expiry,
            'expiry_warning_days' => $t->expiry_warning_days,
            'requires_attachment' => (bool) $t->requires_attachment,
            'custom_fields' => $t->customFieldList(),
        ];
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
        <li class="breadcrumb-item"><a href="{{ route('employee.index') }}">Employee Management</a></li>
        <li class="breadcrumb-item"><a href="{{ route('employee_document.index') }}">Employee Information</a></li>
        <li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
      </ol>
    </nav>
  </div>
  <!-- /BREADCRUMB -->

  <form action="{{ route('employee_document.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="employee_document_id" value="{{ $document->id ?? '' }}">

    <div class="row layout-top-spacing">
      <div class="col-12 collayout-spacing">
        <button type="submit" class="btn btn-primary mb-2 me-0" style="float:right">Save</button>
      </div>

      <div class="col-12 collayout-spacing">
        <div class="statbox widget box box-shadow layout-spacing">
          <div class="widget-header">
            <div class="row">
              <div class="col-12"><h4>Details</h4></div>
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
              <div class="form-group col-12 col-md-6 mb-4">
                <label for="user_id">Employee <span class="text-danger">*</span></label>
                <select class="form-select" id="user_id" name="user_id" required>
                  <option value="">Select employee..</option>
                  @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ (string) $selectedUserId === (string) $employee->id ? 'selected' : '' }}>
                      {{ $employee->name }}{{ $employee->staff_id ? ' ('.$employee->staff_id.')' : '' }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="form-group col-12 col-md-6 mb-4">
                <label for="employee_document_type_id">Information Type <span class="text-danger">*</span></label>
                <select class="form-select" id="employee_document_type_id" name="employee_document_type_id" required>
                  <option value="">Select type..</option>
                  @foreach($types as $type)
                    <option value="{{ $type->id }}" {{ (string) $selectedTypeId === (string) $type->id ? 'selected' : '' }}>
                      {{ $type->name }}
                    </option>
                  @endforeach
                </select>
                <small class="text-muted" id="type-hint"></small>
              </div>

              <div class="form-group col-12 col-md-6 mb-4">
                <label for="title">Title Override</label>
                <input type="text" class="form-control" id="title" name="title"
                       placeholder="Leave blank to use the type name"
                       value="{{ old('title', $document->title ?? '') }}">
              </div>

              <div class="form-group col-12 col-md-6 mb-4">
                <label for="reference_no">Reference / Certificate No.</label>
                <input type="text" class="form-control" id="reference_no" name="reference_no"
                       value="{{ old('reference_no', $document->reference_no ?? '') }}">
              </div>

              <div class="form-group col-12 col-md-6 mb-4">
                <label for="issued_by">Issued By</label>
                <input type="text" class="form-control" id="issued_by" name="issued_by"
                       placeholder="Issuing body"
                       value="{{ old('issued_by', $document->issued_by ?? '') }}">
              </div>

              <div class="form-group col-12 col-md-3 mb-4">
                <label for="issue_date">Issue Date</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date"
                       value="{{ old('issue_date', isset($document) && $document->issue_date ? $document->issue_date->format('Y-m-d') : '') }}">
              </div>

              <div class="form-group col-12 col-md-3 mb-4" id="expiry-wrapper">
                <label for="expiry_date">Expiry Date <span class="text-danger" id="expiry-required">*</span></label>
                <input type="date" class="form-control" id="expiry_date" name="expiry_date"
                       value="{{ old('expiry_date', isset($document) && $document->expiry_date ? $document->expiry_date->format('Y-m-d') : '') }}">
              </div>
            </div>

            <!-- Fields defined on the chosen type -->
            <div id="custom-fields-block" style="display:none;">
              <hr class="mb-4">
              <div class="row">
                <div class="col-12 mb-3"><h6 id="custom-fields-heading">Additional Information</h6></div>
                <div id="custom-fields-target" class="col-12"><div class="row"></div></div>
              </div>
            </div>

            <hr class="mb-4">

            <div class="row">
              <div class="form-group col-12 mb-4">
                <label for="remarks">Remarks</label>
                <textarea class="form-control" id="remarks" name="remarks" rows="3">{{ old('remarks', $document->remarks ?? '') }}</textarea>
              </div>

              <div class="form-group col-12 mb-4">
                <label for="attachment">Attachment <span class="text-danger" id="attachment-required" style="display:none;">*</span></label>
                <input type="file" class="form-control" id="attachment" name="attachment[]" multiple>
                <small class="text-muted">Scanned certificate, licence or supporting document. Multiple files allowed.</small>

                @if(isset($document) && $document->attachments->count())
                  <div class="mt-3">
                    <label class="mb-2">Already attached</label>
                    <ul class="list-group">
                      @foreach($document->attachments as $attachment)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <a href="{{ route('attachment.show', $attachment) }}" target="_blank">{{ $attachment->filename }}</a>
                          <a href="{{ route('attachment.destroy', $attachment) }}"
                             onclick="return confirm('Remove this attachment?')"
                             class="badge bg-danger" style="text-decoration:none;">Remove</a>
                        </li>
                      @endforeach
                    </ul>
                  </div>
                @endif
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
      var typeConfig = @json($typeConfig);
      var savedValues = @json($existingValues ? (object) $existingValues : new stdClass);

      var typeSelect = document.getElementById('employee_document_type_id');
      var block = document.getElementById('custom-fields-block');
      var target = document.getElementById('custom-fields-target').querySelector('.row');
      var heading = document.getElementById('custom-fields-heading');
      var hint = document.getElementById('type-hint');
      var expiryInput = document.getElementById('expiry_date');
      var expiryRequiredMark = document.getElementById('expiry-required');
      var attachmentRequiredMark = document.getElementById('attachment-required');

      function escapeAttr(value) {
        return String(value).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      }

      function renderCustomFields(config) {
        target.innerHTML = '';

        var fields = (config && config.custom_fields) || [];
        if (!fields.length) {
          block.style.display = 'none';
          return;
        }

        heading.textContent = config.name + ' — Additional Information';
        block.style.display = '';

        fields.forEach(function (field) {
          var saved = savedValues && savedValues[field.key] ? savedValues[field.key] : '';
          var name = 'custom_values[' + field.key + ']';
          var req = field.required ? ' required' : '';
          var star = field.required ? ' <span class="text-danger">*</span>' : '';
          var control;

          if (field.type === 'textarea') {
            control = '<textarea class="form-control" name="' + name + '" rows="2"' + req + '>' + escapeAttr(saved) + '</textarea>';
          } else {
            var inputType = (field.type === 'number' || field.type === 'date') ? field.type : 'text';
            control = '<input type="' + inputType + '" class="form-control" name="' + name + '" value="' + escapeAttr(saved) + '"' + req + '>';
          }

          var col = document.createElement('div');
          col.className = 'form-group col-12 col-md-4 mb-4';
          col.innerHTML = '<label>' + escapeAttr(field.label) + star + '</label>' + control;
          target.appendChild(col);
        });
      }

      function applyType() {
        var config = typeConfig[typeSelect.value];

        if (!config) {
          block.style.display = 'none';
          hint.textContent = '';
          expiryInput.required = false;
          expiryRequiredMark.style.display = 'none';
          attachmentRequiredMark.style.display = 'none';
          return;
        }

        expiryInput.required = !!config.has_expiry;
        expiryRequiredMark.style.display = config.has_expiry ? '' : 'none';
        expiryInput.disabled = false;

        attachmentRequiredMark.style.display = config.requires_attachment ? '' : 'none';

        hint.textContent = config.has_expiry
          ? 'Expiry tracked — dashboard alerts ' + config.expiry_warning_days + ' days ahead.'
          : 'No expiry tracked for this type.';

        renderCustomFields(config);
      }

      typeSelect.addEventListener('change', applyType);
      applyType();
    </script>
  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>
