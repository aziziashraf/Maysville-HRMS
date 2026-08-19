<p>Greetings,</p>

<p>Your overtime submission has been reviewed. Please find the details below:</p>

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

@if ($overtime->review_status)
<p>Your overtime application has been reviewed and is awaiting approval.</p>
@else
<p>Your overtime application has been rejected.</p>
@endif

<table>
    @if ($overtime->reviewed_by)
    <tr>
        <td>Reviewed By:</td>
        <td>{{ $overtime->reviewer->name }}</td>
    </tr>
    @endif
    @if ($overtime->reviewed_at)
    <tr>
        <td>Reviewed At:</td>
        <td>{{ \Carbon\Carbon::parse($overtime->reviewed_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($overtime->review_remark)
    <tr>
        <td>Reviewer's Remarks:</td>
        <td>{{ $overtime->review_remark }}</td>
    </tr>
    @endif

</table>

<br>

<a href="{{ route('overtime.edit', $overtime->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
