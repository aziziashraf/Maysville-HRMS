<p>Greetings,</p>

@php
if ($claim->approval_status){
    $status = 'approved';
} else {
    $status = 'rejected';
}
@endphp

<p>Your claim application has been {{$status}}. Please find the details below:</p>

<table>
    <tr>
        <td>Employee:</td>
        <td>{{ $claim->user->name }}</td>
    </tr>
    <tr>
        <td>Claim Type:</td>
        <td>{{ $claim->claimType->name }}</td>
    </tr>
    <tr>
        <td>Amount:</td>
        <td>{{ $claim->amount }}</td>
    </tr>
    <tr>
        <td>Unit:</td>
        <td>{{ $claim->claimType->unit }}</td>
    </tr>
</table>

<table>
    @if ($claim->approved_by)
    <tr>
        <td>{{ucwords($status)}} By:</td>
        <td>{{ $claim->approver->name }}</td>
    </tr>
    @endif
    @if ($claim->approved_at)
    <tr>
        <td>{{ucwords($status)}} At:</td>
        <td>{{ \Carbon\Carbon::parse($claim->approved_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($claim->approval_remark)
    <tr>
        <td>Remarks:</td>
        <td>{{ $claim->approval_remark }}</td>
    </tr>
    @endif
</table>
<br>
<a href="{{ route('claim.edit', $claim->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
