<p>Greetings,</p>

<p>The following leave application has been cancelled by the employee:</p>

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

<p>Please update your records accordingly.</p>

<p>Thank you.</p>
