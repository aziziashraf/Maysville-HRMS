<p>Greetings,</p>

@php
if ($overtime->pre_review_status){
    $status = 'pre-reviewed';
} else {
    $status = 'rejected';
}
@endphp

<p>Your overtime request pre-review has completed. Please find the details below:</p>

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
    @if ($overtime->reasons)
    <tr>
        <td>Reasons:</td>
        <td>{{ $overtime->reasons }}</td>
    </tr>
    @endif

</table>

<p>Your overtime request has been {{$status}}.</p>

<table>
    @if ($overtime->pre_reviewed_by)
    <tr>
        <td>Pre-Reviewed By:</td>
        <td>{{ $overtime->pre_reviewer->name }}</td>
    </tr>
    @endif
    @if ($overtime->pre_reviewed_at)
    <tr>
        <td>Pre-Reviewed At:</td>
        <td>{{ \Carbon\Carbon::parse($overtime->pre_reviewed_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($overtime->review_remark)
    <tr>
        <td>Pre-Reviewer's Remarks:</td>
        <td>{{ $overtime->pre_review_remark }}</td>
    </tr>
    @endif

</table>

<br>

<a href="{{ route('overtime.edit', $overtime->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
