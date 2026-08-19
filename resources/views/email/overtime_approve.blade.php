<p>Greetings,</p>
@php
if ($overtime->approval_status){
    $status = 'approved';
} else {
    $status = 'rejected';
}
@endphp

<p>Your overtime application has been {{$status}}. Please find the details below:</p>

<table>
    <tr>
        <td>Employee:</td>
        <td>{{ $overtime->user->name }}</td>
    </tr>
    <tr>
        <td>Date:</td>
        <td>{{ $overtime->date }}</td>
    </tr>
    <tr>
        <td>Estimated Time Taken:</td>
        <td>{{ $overtime->estimated_time_taken }}</td>
    </tr>
    <tr>
        <td>Actual Time Start:</td>
        <td>{{ $overtime->actual_time_start }}</td>
    </tr>
    <tr>
        <td>Actual Time End:</td>
        <td>{{ $overtime->actual_time_end }}</td>
    </tr>
    <tr>
        <td>Actual Time Taken:</td>
        <td>{{ $overtime->actual_time_taken }}</td>
    </tr>
    @if ($overtime->reasons)
    <tr>
        <td>Reasons:</td>
        <td>{{ $overtime->reasons }}</td>
    </tr>
    @endif
</table>

<br>

<table>
    @if ($overtime->approved_by)
    <tr>
        <td>{{ucwords($status)}} By:</td>
        <td>{{ $overtime->approver->name }}</td>
    </tr>
    @endif
    @if ($overtime->approved_at)
    <tr>
        <td>{{ucwords($status)}} At:</td>
        <td>{{ \Carbon\Carbon::parse($overtime->approved_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($overtime->approval_remark)
    <tr>
        <td>Remarks:</td>
        <td>{{ $overtime->approval_remark }}</td>
    </tr>
    @endif
</table>
<br>
<a href="{{ route('overtime.edit', $overtime->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
