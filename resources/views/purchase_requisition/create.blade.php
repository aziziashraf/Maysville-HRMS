<x-base-layout :scrollspy="false">
  @php 
    if(isset($purchaseRequisition)){
      $title = 'Edit Purchase Requisition';
    }else{
      $title = 'Add Purchase Requisition';
    }
  @endphp
  <x-slot:pageTitle>
    {{$title}} | {{ env('APP_NAME') }}
  </x-slot>

  <!-- BEGIN GLOBAL MANDATORY STYLES -->
  <x-slot:headerFiles>
    <!--  BEGIN CUSTOM STYLE FILE  -->
    @vite(['resources/scss/light/assets/components/timeline.scss'])
    @vite(['resources/scss/dark/assets/components/timeline.scss'])
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/tomSelect/tom-select.default.min.css')}}">
    @vite(['resources/scss/light/plugins/tomSelect/custom-tomSelect.scss'])
    @vite(['resources/scss/dark/plugins/tomSelect/custom-tomSelect.scss'])
    <link rel="stylesheet" href="{{asset('plugins/flatpickr/flatpickr.css')}}">
    <link rel="stylesheet" href="{{asset('plugins/noUiSlider/nouislider.min.css')}}">
    @vite(['resources/scss/light/plugins/flatpickr/custom-flatpickr.scss'])
    @vite(['resources/scss/dark/plugins/flatpickr/custom-flatpickr.scss'])

    @vite(['resources/scss/light/assets/elements/alert.scss'])        
    @vite(['resources/scss/dark/assets/elements/alert.scss'])
    <style>
      .table tbody tr td {
        padding: 5px;
      }
      body.layout-dark .table tbody tr td {
        padding: 5px;
      }
      </style>
    <!--  END CUSTOM STYLE FILE  -->
  </x-slot>
  <!-- END GLOBAL MANDATORY STYLES -->

      <!-- BREADCRUMB -->
      <div class="page-meta">
        <nav class="breadcrumb-style-one" aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('purchase_requisition.index') }}">Purchase Requisition Application</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{$title}} </li>
          </ol>
        </nav>
      </div>
      <!-- /BREADCRUMB -->
      <form id="purchase_requisition_form" action="{{ route('purchase_requisition.store') }}" method="post" enctype="multipart/form-data">
        @csrf
        <div class="row layout-top-spacing">
          <div id="basic" class="col-12  collayout-spacing">
            @if(isset($purchaseRequisition) && ($purchaseRequisition->status != 'cancelled' && $purchaseRequisition->status != 'rejected'))
            <a class="btn btn-danger mb-2 me-2" style="float:right" onclick="cancel()">Cancel</a>
            @endif
            @if(!isset($purchaseRequisition) || $purchaseRequisition->status == 'draft')
            <a class="btn btn-primary mb-2 me-2" style="float:right" onclick="saveSubmit()">Submit</a>
            <a class="btn btn-info mb-2 me-2" style="float:right" onclick="saveDraft()">Save as Draft</a>
            @endif
            <input type="submit" id="submitButton" hidden>
          </div>
          <div id="basic" class="col-12 collayout-spacing">
            <div class="statbox widget box box-shadow">
              <div class="widget-header">
                <div class="row">
                  <div class="col-12">
                    <h4>Purchase Requisition Details</h4>
                  </div>
                </div>
                @if(session()->has('error'))
                  <div class="alert alert-light-danger alert-dismissible fade show border-0 mb-4" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ session()->get('error') }}. </div>
                @endif
                @if(session()->has('success'))
                  <div class="alert alert-light-success alert-dismissible fade show border-0 mb-4" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Success!</strong> {{ session()->get('success') }}. </div>
                @endif

                @if($errors->any())
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-light-danger alert-dismissible fade show border-0" role="alert"> <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"> <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x close" data-bs-dismiss="alert"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button> <strong>Error!</strong> {{ $error }} </div>
                    @endforeach
                @endif
              </div>

              <div class="widget-content widget-content-area">

                <div class="row">
                  <div class="col-12">
                    <input type="text" id="purchase_requisition_id" name="purchase_requisition_id" value="{{$purchaseRequisition->id ?? ''}}" hidden>
                    <input type="text" id="user_id" name="user_id" value="{{$purchaseRequisition->user_id ?? auth()->user()->id}}" hidden>
                    <input type="text" id="status" name="status" value="{{$purchaseRequisition->status ??''}}" hidden>
                  </div>
                  @isset($purchaseRequisition)
                  <div class="col-12 form-group mb-4">
                    <label for="status">Status:
                      @if($purchaseRequisition->status == 'draft')
                      <span class="badge badge-info">Draft</span>
                      @elseif($purchaseRequisition->status == 'submitted')
                      <span class="badge badge-primary">Submitted</span>
                      @elseif($purchaseRequisition->status == 'approved')
                      <span class="badge badge-success">Approved</span>
                      @elseif($purchaseRequisition->status == 'cancelled')
                      <span class="badge badge-danger">Cancelled</span>
                      @elseif($purchaseRequisition->status == 'rejected')
                      <span class="badge badge-danger">Rejected</span>
                      @endif
                    </label>
                  </div>
                  @endisset
                  <div class="col-12 form-group mb-4">
                    <label for="department_id" >Department</label>
                    <select id="department_id" name="department_id" class="form-select" <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                      <option selected disabled>--Select Department--</option>
                      @foreach($departments as $department)
                      <option value="{{$department->id}}" <?php echo isset($purchaseRequisition) && $purchaseRequisition->department_id == $department->id ? 'selected' : ''; ?>>{{$department->department_name}}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="purpose">Purpose/Use</label>
                    <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Purpose/Use" value="{{$purchaseRequisition->purpose ??''}}"<?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?> required>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="date_needed">Date Needed</label>
                    <input id="date_needed" name="date_needed" class="form-control flatpickr flatpickr-input active" type="text" value="{{$purchaseRequisition->date_needed ??''}}" <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?> required>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label for="date_ordered">Date Ordered (Optional)</label>
                    <input id="date_ordered" name="date_ordered" class="form-control flatpickr flatpickr-input active" type="text" value="{{$purchaseRequisition->date_ordered ??''}}" <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="purchased_from">Purchased From</label>
                    <input type="text" class="form-control" id="purchased_from" name="purchased_from" placeholder="Purchased From" value="{{$purchaseRequisition->purchased_from ??''}}" <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label>Auto Renew</label>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="auto_renew" id="auto_renew_enable" value="1" required {{ isset($purchaseRequisition) && $purchaseRequisition->auto_renew == '1' ? 'checked' : '' }} <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                      <label class="form-check-label" for="auto_renew_enable">
                        Yes
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="auto_renew" id="auto_renew_disable" value="0" required {{ isset($purchaseRequisition) && $purchaseRequisition->auto_renew == '0' ? 'checked' : '' }} <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                      <label class="form-check-label" for="auto_renew_disable">
                        No
                      </label>
                    </div>
                  </div>
                  <div class="col-12 col-md-6 form-group mb-4">
                    <label>Source of Fund</label>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="source_of_fund" id="source_of_fund_personal" value="personal" required {{ isset($purchaseRequisition) && $purchaseRequisition->source_of_fund == 'personal' ? 'checked' : '' }} <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                      <label class="form-check-label" for="source_of_fund_personal">
                        Personal (Can be reimbursed through Claim)
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="source_of_fund" id="source_of_fund_company" value="company" required {{ isset($purchaseRequisition) && $purchaseRequisition->source_of_fund == 'company' ? 'checked' : '' }} <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>
                      <label class="form-check-label" for="source_of_fund_company">
                        Company
                      </label>
                    </div>
                  </div>
                  <div class="col-12 mb-4 invoice-detail-items">
                    <div class="table-responsive">
                      <table class="table table-bordered table-striped item-table">
                        <thead>
                          <tr>
                            <th class="text-center">No</th>
                            <th hidden>ID</th>
                            <th class="w-50">Description</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Unit Price (Optional)</th>
                            <th class="text-center">Total Price (Optional)</th>
                            @if(!isset($purchaseRequisition) || $purchaseRequisition->status == 'draft')
                            <th class=""></th>
                            @endif
                          </tr>
                        </thead>
                        <tbody>
                          @isset($purchaseRequisition)
                          @if($purchaseRequisition->status == 'draft')
                            @foreach($purchaseRequisition->purchaseRequisitionItems as $item)
                            <tr>
                              <td class="text-center">{{ $loop->iteration }}</td>
                              <td hidden><input type="text" name="items[{{$loop->iteration - 1}}][purchase_requisition_item_id]" value="{{$item->id}}"></td>
                              <td><input type="text" class="form-control form-control-sm" name="items[{{$loop->iteration - 1}}][description]" placeholder="Description" value="{{$item->description}}" required></td>
                              <td class="text-end"><input type="number" class="form-control form-control-sm" name="items[{{$loop->iteration - 1}}][quantity]" placeholder="Quantity" value="{{$item->quantity}}" required></td>
                              <td class="text-end"><input type="number" class="form-control form-control-sm" name="items[{{$loop->iteration - 1}}][unit_price]" placeholder="Price" value="{{$item->unit_price}}"></td>
                              <td class="text-end"><input type="number" class="form-control form-control-sm" name="items[{{$loop->iteration - 1}}][total_price]" placeholder="Total Price" value="{{$item->total_price}}"></td>
                              <td class="text-center">
                                <button type="button" class="btn btn-light-danger btn-icon" onclick="removePurchaseRequisitionItem(this)">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                </button>
                              </td>
                            </tr>
                            @endforeach
                          @else
                            @foreach($purchaseRequisition->purchaseRequisitionItems as $item)
                            <tr>
                              <td class="text-center">{{ $loop->iteration }}</td>
                              <td hidden>{{$item->id}}</td>
                              <td>{{$item->description}}</td>
                              <td class="text-end">{{$item->quantity}}</td>
                              <td class="text-end">{{$item->unit_price}}</td>
                              <td class="text-end">{{$item->total_price}}</td>
                            </tr>
                            @endforeach
                          @endif
                          @endisset
                        </tbody>
                      </table>
                    </div>
                    @if(!isset($purchaseRequisition) || $purchaseRequisition->status == 'draft')
                    <button class="btn btn-success" type="button" onclick="addItemRow()">Add Item</button>
                    @endif
                  </div>
                  <div class="col-12 form-group mb-4">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control" id="remarks" name="remarks" placeholder="Remarks" rows="5" <?php echo isset($purchaseRequisition) && $purchaseRequisition->status != 'draft' ? 'disabled' : ''; ?>>{{$purchaseRequisition->remarks ??''}}</textarea>
                  </div>
                  <div class="col-12 mb-4">
                    <label for="attachment">Attachment</label>
                    @if(!isset($purchaseRequisition) || $purchaseRequisition->status == 'draft')
                    <div class="input-group mb-3">
                      <input type="file" id="attachment" name="attachment[]" class="form-control" multiple>
                      @if(isset($purchaseRequisition) && $purchaseRequisition->status == 'draft')
                      <button class="btn btn-primary" type="button" id="button-addon2" onclick="addAttachment()">Add Attachment</button>
                      @endif
                    </div>
                    @endif
                    
                    @isset($purchaseRequisition)
                      <p><small class="text-muted">Current Attachments:</small></p>
                      <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                          <thead>
                            <tr>
                              <th scope="col">Filename</th>
                              <th class="text-center" scope="col">Action</th>
                            </tr>
                          </thead>
                          <tbody>
                            @forelse ($purchaseRequisition->attachments as $row)
                            <tr>
                              <td>{{$row->filename}}</td>
                              <td class="text-center">
                                <div class="action-btns">
                                  <a href="{{ route('attachment.show',$row) }}"  target="_blank" class="action-btn btn-edit bs-tooltip me-2" data-toggle="tooltip" data-placement="top" title="View">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                  </a>
                                  @if($purchaseRequisition->status == 'draft')
                                  <a onclick="if(confirm('Are you sure you want to delete this purchase requisition?')){ window.location.href='{{ route('attachment.destroy',$row) }}' }" class="action-btn btn-delete bs-tooltip" data-toggle="tooltip" data-placement="top" title="Delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                  </a>
                                  @endif
                                </div>
                              </td>
                            </tr>
                            @empty
                            <tr>
                              <td colspan="2" class="text-center">No data available</td>
                            </tr>
                            @endforelse
                          </tbody>
                        </table>
                      </div>
                    @endisset
                  </div>
                </div>

              </div>
            </div>
          </div>
        </div>
      </form>

  <!--  BEGIN CUSTOM SCRIPTS FILE  -->
  <x-slot:footerFiles>
    <script src="{{asset('plugins/flatpickr/flatpickr.js')}}"></script>
    <script src="{{asset('plugins/global/jquery-3.6.4.min.js')}}"></script>
    <script>
      var f1 = flatpickr(document.getElementById('date_needed'), {
        mode: "single",
      });

      var f2 = flatpickr(document.getElementById('date_ordered'), {
        mode: "single",
      }); 
    </script>
    <script>
      // Get the input element by its name attribute
      const form = document.getElementById('purchase_requisition_form');
      const submitButton = document.getElementById('submitButton');

      function saveDraft() {
        document.getElementById("status").value = "draft";
        submitButton.click();
      }

      function saveSubmit() {
        document.getElementById("status").value = "submitted"; 
        submitButton.click();
      }

      function cancel() {
        document.getElementById("status").value = "cancelled";
        submitButton.click();
      }

      function addAttachment(){
        document.getElementById("user_id").disabled = true;
        document.getElementById("department_id").disabled = true;
        document.getElementById("purpose").disabled = true;
        document.getElementById("date_needed").disabled = true;
        document.getElementById("date_ordered").disabled = true;
        document.getElementById("purchased_from").disabled = true;
        document.getElementsByName("source_of_fund").disabled = true;
        document.getElementsByName("auto_renew").disabled = true;
        document.getElementById("remarks").disabled = true;
        document.getElementById("status").disabled = true;
        var inputElements = document.querySelectorAll('input[name^="items["]');
        inputElements.forEach(function(input) {
          input.disabled = true;
        });

        submitButton.click();
      }
    </script>
    <script>
      // Function to remove a purchase requisition item
      function removePurchaseRequisitionItem(button) {
        const tableBody = document.querySelector('.item-table tbody');
        const currentIndex = tableBody.rows.length;

        if (currentIndex === 1) {
          alert('Cannot remove all items');
        } else {
          const row = button.closest('tr');
          row.remove();
          reorderCounter();
        }
      }

      // Function to calculate the total price for a row
      function calculateTotalPrice(row) {
        const unitPriceInput = row.querySelector('input[name^="items"][name$="[unit_price]"]');
        const quantityInput = row.querySelector('input[name^="items"][name$="[quantity]"]');
        const totalPriceInput = row.querySelector('input[name^="items"][name$="[total_price]"]');

        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const quantity = parseFloat(quantityInput.value) || 0;

        const total = unitPrice * quantity;
        totalPriceInput.value = total.toFixed(2); // Adjust to your desired decimal precision.
      }

      // Function to add a new item row
      function addItemRow() {
        const tableBody = document.querySelector('.item-table tbody');
        const currentIndex = tableBody.rows.length;

        const newRow = document.createElement('tr');
        newRow.innerHTML = `
          <td class="text-center">${currentIndex + 1}</td>
          <td hidden></td>
          <td><input type="text" class="form-control form-control-sm" name="items[${currentIndex}][description]" placeholder="Description" required></td>
          <td class="text-end"><input type="number" class="form-control form-control-sm" name="items[${currentIndex}][quantity]" placeholder="Quantity" required></td>
          <td class="text-end"><input type="number" class="form-control form-control-sm" name="items[${currentIndex}][unit_price]" placeholder="Price"></td>
          <td class="text-end"><input type="number" class="form-control form-control-sm" name="items[${currentIndex}][total_price]" placeholder="Total Price"></td>
          <td class="text-center">
            <button type="button" class="btn btn-light-danger btn-icon" onclick="removePurchaseRequisitionItem(this)">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
            </button>
          </td>
        `;

        tableBody.appendChild(newRow);
        calculateTotalPrice(newRow);
        newRow.addEventListener('input', () => calculateTotalPrice(newRow));
        // reorderCounter();
      }

      // Function to update item counters and input field names
      function reorderCounter() {
        const itemRows = document.querySelectorAll('.item-table tbody tr');

        itemRows.forEach((row, index) => {
          const itemCounterCell = row.querySelector('.text-center');
          itemCounterCell.textContent = index + 1;

          const inputFields = row.querySelectorAll('input[name^="items"]');
          inputFields.forEach((input) => {
            const oldName = input.getAttribute('name');
            const newName = oldName.replace(/\[\d+\]/, `[${index}]`);
            input.setAttribute('name', newName);
            // input.setAttribute('placeholder', newName);
          });
        });
      }

      // Add event listener to all existing rows when the page loads
      document.addEventListener('DOMContentLoaded', () => {
        // Initialize the table with a few rows
        const existingRows = document.querySelectorAll('.item-table tbody tr');
        var numberOfExistingRows = existingRows.length;
        existingRows.forEach((row) => {
          row.addEventListener('input', () => calculateTotalPrice(row));
        });
        if (numberOfExistingRows == 0) {
          addItemRow();
        }
      });
    </script>

  </x-slot>
  <!--  END CUSTOM SCRIPTS FILE  -->
</x-base-layout>