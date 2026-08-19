<p>Greetings,</p>

<p>Your leave application has been reviewed. Please find the details below:</p>

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
        <td>{{ $leave->start_date }}</td>
    </tr>
    @if ($leave->end_date)
    <tr>
        <td>End Date:</td>
        <td>{{ $leave->end_date }}</td>
    </tr>
    @endif
    @if ($leave->start_time)
    <tr>
        <td>Start Time:</td>
        <td>{{ $leave->start_time }}</td>
    </tr>
    @endif
    @if ($leave->end_time)
    <tr>
        <td>End Time:</td>
        <td>{{ $leave->end_time }}</td>
    </tr>
    @endif
</table>

@if ($leave->review_status)
<p>Your leave application has been reviewed and is awaiting approval.</p>
@else
<p>Your leave application has been rejected.</p>
@endif

<table>
    @if ($leave->reviewed_by)
    <tr>
        <td>Reviewed By:</td>
        <td>{{ $leave->reviewer->name }}</td>
    </tr>
    @endif
    @if ($leave->reviewed_at)
    <tr>
        <td>Reviewed At:</td>
        <td>{{ \Carbon\Carbon::parse($leave->reviewed_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($leave->review_remark)
    <tr>
        <td>Reviewer's Remarks:</td>
        <td>{{ $leave->review_remark }}</td>
    </tr>
    @endif

</table>

<br>

<a href="{{ route('leave.edit', $leave->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
