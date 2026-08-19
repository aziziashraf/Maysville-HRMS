<p>Greetings,</p>

<p>The following claim application has been cancelled by the employee:</p>

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

<p>Please update your records accordingly.</p>

<p>Thank you.</p>
