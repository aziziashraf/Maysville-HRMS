<!DOCTYPE html>
<html>
<head>
  <title>Purchase Requisition ( ID: {{$purchaseRequisition->id}} )</title>
<style>
  .item-table {
    border-collapse: collapse;
  }
  .item-table table {
    border: 1px solid black;
  }
  .item-table td {
    border: 1px solid black;
  }

  .item-table th {
    border: 1px solid black;
  }

  /* Alignment classes */
  .text-left {
    text-align: left;
  }

  .text-center {
    text-align: center;
  }

  .text-right {
    text-align: right;
  }
</style>
</head>
<body>

<div class="header-container">
  <h2>Purchase Requisition Form ( ID: {{$purchaseRequisition->id}} )
  </h2>
  <div style="text-align: right;">Submitted at: {{ $purchaseRequisition->submitted_at }}</div>
</div>

<!-- Add the 'item-table' class to the table -->
<table style="width:100%" class="item-table">
  <thead>
    <tr>
      <th style="width:5%" class="text-center"></th>
      <th style="width:50%" class="text-left">Description</th>
      <th style="width:15%" class="text-center">Quantity</th>
      <th style="width:15%" class="text-right">Unit Price (Optional)</th>
      <th style="width:15%" class="text-right">Total Price (Optional)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($purchaseRequisition->purchaseRequisitionItems as $item)
    <tr>
      <td class="text-center">{{ $loop->iteration }}</td>
      <td class="text-left">{{$item->description}}</td>
      <td class="text-center">{{$item->quantity ?? '-'}}</td>
      <td class="text-right">{{$item->unit_price ?? '-'}}</td>
      <td class="text-right">{{$item->total_price ?? '-'}}</td>
    </tr>
    @endforeach
    <tr>
      <td colspan="4" class="text-right"><b>Total Amount</b></td>
      <td class="text-right">{{ number_format($purchaseRequisition->totalAmount(), 2) }}</td>
    </tr>
  </tbody>
</table>

<table style="width:100%">
  <tr>
    <td><b>**Auto Renew</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->auto_renew == '1' ? 'Yes' : 'No' }}</td>
    <td><b>**Source of Fund</b></td>
    <td>:</td>
    <td>{{ ucwords($purchaseRequisition->source_of_fund) }}</td>
  </tr>
  <tr>
    <td><b>For</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->department ? $purchaseRequisition->department->department_name . ' Department' : '-'}} </td>
    <td colspan="3" rowspan="5" style="vertical-align: top; border: 1px solid black; width:50%;"><b>Remark :</b><br>{{ $purchaseRequisition->remarks ?? "-" }}</td>
  </tr>
  <tr>
    <td><b>Purpose/Use</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->purpose ?? "-" }}</td>
  </tr>
  <tr>
    <td><b>When wanted</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->date_needed ?? "-" }}</td>
  </tr>
  <tr>
    <td><b>Date ordered </b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->date_ordered ?? "-" }}</td>
  </tr>
  <tr>
    <td><b>Purchase From</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->purchased_from ?? "-" }}</td>
  </tr>
  <tr>
    <td><b>Requested by</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->user->name ?? '-' }}</td>
    <td><b>Approved by</b></td>
    <td>:</td>
    <td>{{ $purchaseRequisition->approver->name ?? "-" }} ({{  $purchaseRequisition->approver->position->name ?? "-"}})</td>
  </tr>
</table>

</body>
</html>
