<p>Greetings,</p>
<p>A new claim application has been submitted for your action. Please find the details below:</p>
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
    @if ($claim->remarks)
    <tr>
        <td>Remarks:</td>
        <td>{{ $claim->remarks }}</td>
    </tr>
    @endif
</table>
@if ($claim->user->getLeaveReviewers()->isNotEmpty())
    @if ($claim->reviewed_by)
    <p>This claim application requires your approval.</p>
    @else
    <p>This claim application requires your review.</p>
    @endif
@else
<p>This claim application requires your approval.</p>
@endif

<a href="{{ route('claim.requestEdit', $claim->id) }}">
    <button>View Request</button>
</a>
<p>Thank you.</p>