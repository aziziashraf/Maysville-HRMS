<p>Greetings,</p>

@php
if ($leave->approval_status){
    $status = 'approved';
} else {
    $status = 'rejected';
}
@endphp

<p>Your leave application has been {{$status}}. Please find the details below:</p>

<table>
    <tr>
        <td>Employee:</td>
        <td>{{ $leave->user->name }}</td>
    </tr>
    <tr>
        <td>Leave Type:</td>
        <td>{{ $leave->leaveType->name }}</td>
    </tr>
    <tr>
        <td>Start Date:</td>
        <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('j F Y') }}</td>
    </tr>
    @if ($leave->end_date)
    <tr>
        <td>End Date:</td>
        <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('j F Y') }}</td>
    </tr>
    @endif
    @if ($leave->start_time)
    <tr>
        <td>Start Time:</td>
        <td>{{ \Carbon\Carbon::parse($leave->start_time)->format('g:i A') }}</td>
    </tr>
    @endif
    @if ($leave->end_time)
    <tr>
        <td>End Time:</td>
        <td>{{ \Carbon\Carbon::parse($leave->end_time)->format('g:i A') }}</td>
    </tr>
    @endif
</table>

<table>
    @if ($leave->approved_by)
    <tr>
        <td>{{ucwords($status)}} By:</td>
        <td>{{ $leave->approver->name }}</td>
    </tr>
    @endif
    @if ($leave->approved_at)
    <tr>
        <td>{{ucwords($status)}} At:</td>
        <td>{{ \Carbon\Carbon::parse($leave->approved_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($leave->approval_remark)
    <tr>
        <td>Remarks:</td>
        <td>{{ $leave->approval_remark }}</td>
    </tr>
    @endif
</table>
<br>
<a href="{{ route('leave.edit', $leave->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
