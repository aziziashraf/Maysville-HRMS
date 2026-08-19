<p>Greetings,</p>
<p>A new overtime has been submitted for your action. Please find the details below:</p>
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
        <td>{{ $overtime->estimated_time_taken }} hour(s)</td>
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
        <td>{{ $overtime->actual_time_taken }} hour(s)</td>
    </tr>
    @if ($overtime->reasons)
    <tr>
        <td>Reasons:</td>
        <td>{{ $overtime->reasons }}</td>
    </tr>
    @endif
</table>
@if ($overtime->user->getLeaveReviewers()->isNotEmpty())
    @if ($overtime->reviewed_by)
    <p>This overtime submission requires your approval.</p>
    @else
    <p>This overtime submission requires your review.</p>
    @endif
@else
<p>This overtime submission requires your approval.</p>
@endif

<a href="{{ route('overtime.requestEdit', $overtime->id) }}">
    <button>View Submission</button>
</a>
<p>Thank you.</p>