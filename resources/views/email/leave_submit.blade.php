<p>Greetings,</p>
<p>A new leave application has been submitted for your action. Please find the details below:</p>
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
    @if ($leave->remarks)
    <tr>
        <td>Remarks:</td>
        <td>{{ $leave->remarks }}</td>
    </tr>
    @endif
</table>
@if ($leave->user->getLeaveReviewers()->isNotEmpty())
    @if ($leave->reviewed_by)
    <p>This leave application requires your approval.</p>
    @else
    <p>This leave application requires your review.</p>
    @endif
@else
<p>This leave application requires your approval.</p>
@endif

<a href="{{ route('leave.requestEdit', $leave->id) }}">
    <button>View Request</button>
</a>
<p>Thank you.</p>