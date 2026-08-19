<style>
.table-bordered {
    border-collapse: collapse;
    border: 1px solid black;
}

.table-bordered th,
.table-bordered td {
    border: 1px solid black;
    padding: 8px;
}

table, th, td {
    padding: 5px;
}

th {
    text-align: left;
}
</style>
<p>Greetings,</p>
<p>A new purchase requisition has been submitted for your action. Please find the details below:</p>
<table>
    <tr>
        <th>Requested By</th>
        <th>:</th>
        <td>{{ $purchaseRequisition->user->name }}</td>
        <td></td>
        <th>Date</th>
        <th>:</th>
        <td>{{ $purchaseRequisition->submitted_at }}</td>
    </tr>
</table>
<table class="table-bordered">
    <thead>
        <tr>
            <th>Description</th>
            <th>Quantity</th>
            <th>Unit Price (RM)</th>
            <th>Amount (RM)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($purchaseRequisition->purchaseRequisitionItems as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>{{ $item->quantity }}</td>
            <td style="text-align: right;">{{ $item->unit_price ?? '-' }}</td>
            <td style="text-align: right;">{{ $item->total_price ?? '-' }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="3" style="text-align: right;"><b>Total</b></td>
            <td style="text-align: right;">{{ $purchaseRequisition->totalAmount() }}</td>
        </tr>
    </tbody>
</table>
<table>
    <tr>
        <th>Auto Renew</th>
        <th>:</th>
        <td>
            @if ($purchaseRequisition->auto_renew)
            <u>Yes</u>
            @else
            <u>No</u>
            @endif
        </td>
    </tr>
    <tr>
        <th>For</th>
        <th>:</th>
        <td><u>{{ $purchaseRequisition->department ? $purchaseRequisition->department->department_name . ' Department' : '-'}}</u></td>
    </tr>
    <tr>
        <th>Purpose</th>
        <th>:</th>
        <td><u>{{$purchaseRequisition->purpose ??''}}</u></td>
    </tr>
    <tr>
        <th>When Needed</th>
        <th>:</th>
        <td><u>{{$purchaseRequisition->date_needed ??''}}</u></td>
    </tr>
    <tr>
        <th>Date Ordered</th>
        <th>:</th>
        <td><u>{{$purchaseRequisition->date_ordered ??''}}</u></td>
    </tr>
    <tr>
        <th>Purchased From</th>
        <th>:</th>
        <td><u>{{$purchaseRequisition->purchased_from ??''}}</u></td>
    </tr>
    <tr>
        <th>Remarks</th>
        <th>:</th>
        <td><u>{{$purchaseRequisition->remarks ??''}}</u></td>
    </tr>
</table>

<p>This purchase requisition requires your approval.</p>

<a href="{{ route('purchase_requisition.requestEdit', $purchaseRequisition->id) }}">
    <button>View Request</button>
</a>
<p>Thank you.</p>