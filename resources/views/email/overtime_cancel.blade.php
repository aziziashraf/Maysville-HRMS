<p>Greetings,</p>

<p>The following overtime request/submission has been cancelled by the employee:</p>

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
    @if ($overtime->reasons)
    <tr>
        <td>Reasons:</td>
        <td>{{ $overtime->reasons }}</td>
    </tr>
    @endif
</table>

<p>Please update your records accordingly.</p>

<p>Thank you.</p>
