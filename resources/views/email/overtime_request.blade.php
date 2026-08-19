<p>Greetings,</p>
<p>A new overtime request has been submitted for your action. Please find the details below:</p>
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

<p>This overtime request requires your pre-review.</p>

<a href="{{ route('overtime.requestEdit', $overtime->id) }}">
    <button>View Request</button>
</a>
<p>Thank you.</p>