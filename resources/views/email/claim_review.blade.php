<p>Greetings,</p>

<p>Your claim application has been reviewed. Please find the details below:</p>

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

@if ($claim->review_status)
<p>Your claim application has been reviewed and is awaiting approval.</p>
@else
<p>Your claim application has been rejected.</p>
@endif

<table>
    @if ($claim->reviewed_by)
    <tr>
        <td>Reviewed By:</td>
        <td>{{ $claim->reviewer->name }}</td>
    </tr>
    @endif
    @if ($claim->reviewed_at)
    <tr>
        <td>Reviewed At:</td>
        <td>{{ \Carbon\Carbon::parse($claim->reviewed_at)->format('g:i A, j F Y') }}</td>
    </tr>
    @endif
    @if ($claim->review_remark)
    <tr>
        <td>Reviewer's Remarks:</td>
        <td>{{ $claim->review_remark }}</td>
    </tr>
    @endif

</table>

<br>

<a href="{{ route('claim.edit', $claim->id) }}">
    <button>View Request</button>
</a>

<p>Thank you.</p>
