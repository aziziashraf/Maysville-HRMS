{{--
    Resume layout, used both for the wkhtmltopdf render and for the printable
    view in the browser. Deliberately plain CSS with no external assets:
    wkhtmltopdf runs without the app's stylesheets and cannot fetch Vite output.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Resume — {{ $employee->name }}</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
      color: #14213D;
      font-size: 12px;
      line-height: 1.5;
      margin: 0;
      padding: 0;
    }
    .sheet { max-width: 760px; margin: 0 auto; padding: 24px; }

    .header { border-bottom: 3px solid #1C3A72; padding-bottom: 14px; margin-bottom: 18px; }
    .header h1 { margin: 0 0 4px 0; font-size: 24px; color: #1C3A72; letter-spacing: .3px; }
    .header .headline { font-size: 13px; color: #BF0000; font-weight: bold; margin-bottom: 8px; }
    .meta { font-size: 11px; color: #414D66; }
    .meta span { margin-right: 14px; white-space: nowrap; }

    h2 {
      font-size: 12px; text-transform: uppercase; letter-spacing: 1px;
      color: #1C3A72; border-bottom: 1px solid #DDE2ED;
      padding-bottom: 4px; margin: 20px 0 10px 0;
    }

    .entry { margin-bottom: 12px; page-break-inside: avoid; }
    .entry .line1 { font-weight: bold; font-size: 12.5px; }
    .entry .line2 { color: #414D66; font-size: 11.5px; }
    .entry .dates { float: right; color: #616C86; font-size: 11px; font-weight: normal; }
    .entry .desc { margin-top: 3px; color: #414D66; }

    table.grid { width: 100%; border-collapse: collapse; }
    table.grid td { padding: 3px 0; vertical-align: top; }
    table.grid td.k { color: #414D66; width: 45%; }
    table.grid td.v { color: #616C86; }

    table.certs { width: 100%; border-collapse: collapse; font-size: 11.5px; }
    table.certs th {
      text-align: left; border-bottom: 1px solid #DDE2ED; padding: 5px 6px;
      color: #414D66; font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px;
    }
    table.certs td { padding: 5px 6px; border-bottom: 1px solid #EBEEF5; }
    .expired { color: #BF0000; font-weight: bold; }

    .summary { color: #414D66; }
    .empty { color: #616C86; font-style: italic; }

    .footer { margin-top: 24px; border-top: 1px solid #DDE2ED; padding-top: 8px; font-size: 10px; color: #616C86; }

    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
      .sheet { max-width: none; padding: 0; }
    }
    .toolbar {
      background: #F5F6FA; border-bottom: 1px solid #DDE2ED;
      padding: 10px 16px; text-align: right;
    }
    .toolbar button, .toolbar a {
      font: inherit; background: #1C3A72; color: #fff; border: 0;
      padding: 8px 16px; border-radius: 6px; cursor: pointer;
      text-decoration: none; display: inline-block;
    }
  </style>
</head>
<body>

@if(!empty($printable))
  <div class="toolbar no-print">
    <a href="{{ route('employee_resume.edit', $employee) }}">Back to edit</a>
    <button onclick="window.print()">Print / Save as PDF</button>
  </div>
@endif

<div class="sheet">

  <div class="header">
    <h1>{{ $employee->name }}</h1>
    @if($resume->headline)
      <div class="headline">{{ $resume->headline }}</div>
    @endif
    <div class="meta">
      @if($employee->staff_id)<span><strong>Staff ID:</strong> {{ $employee->staff_id }}</span>@endif
      @if($employee->position?->name)<span><strong>Position:</strong> {{ $employee->position->name }}</span>@endif
      @if($employee->department?->department_name)<span><strong>Department:</strong> {{ $employee->department->department_name }}</span>@endif
      <br>
      @if($employee->email)<span><strong>Email:</strong> {{ $employee->email }}</span>@endif
      @if($employee->contact_no)<span><strong>Mobile:</strong> {{ $employee->contact_no }}</span>@endif
      @if($employee->start_date)<span><strong>Joined:</strong> {{ \Carbon\Carbon::parse($employee->start_date)->format('M Y') }}</span>@endif
    </div>
  </div>

  @if($resume->summary)
    <h2>Profile</h2>
    <div class="summary">{!! nl2br(e($resume->summary)) !!}</div>
  @endif

  @if(count($resume->section('experience')))
    <h2>Work Experience</h2>
    @foreach($resume->section('experience') as $row)
      <div class="entry">
        <div class="line1">
          {{ $row['position'] ?? '' }}
          @if(!empty($row['start_date']) || !empty($row['end_date']))
            <span class="dates">{{ $row['start_date'] ?? '' }}@if(!empty($row['end_date'])) &ndash; {{ $row['end_date'] }}@endif</span>
          @endif
        </div>
        <div class="line2">{{ $row['company'] ?? '' }}</div>
        @if(!empty($row['description']))
          <div class="desc">{!! nl2br(e($row['description'])) !!}</div>
        @endif
      </div>
    @endforeach
  @endif

  @if(count($resume->section('education')))
    <h2>Education</h2>
    @foreach($resume->section('education') as $row)
      <div class="entry">
        <div class="line1">
          {{ $row['qualification'] ?? '' }}@if(!empty($row['field_of_study'])) — {{ $row['field_of_study'] }}@endif
          @if(!empty($row['start_year']) || !empty($row['end_year']))
            <span class="dates">{{ $row['start_year'] ?? '' }}@if(!empty($row['end_year'])) &ndash; {{ $row['end_year'] }}@endif</span>
          @endif
        </div>
        <div class="line2">{{ $row['institution'] ?? '' }}</div>
      </div>
    @endforeach
  @endif

  @if($certifications->count())
    <h2>Certifications</h2>
    <table class="certs">
      <thead>
        <tr>
          <th>Certification</th>
          <th>Reference</th>
          <th>Issued By</th>
          <th>Expiry</th>
        </tr>
      </thead>
      <tbody>
        @foreach($certifications as $cert)
          <tr>
            <td>{{ $cert->display_title }}</td>
            <td>{{ $cert->reference_no ?? '—' }}</td>
            <td>{{ $cert->issued_by ?? '—' }}</td>
            <td class="{{ $cert->expiry_status === 'expired' ? 'expired' : '' }}">
              {{ $cert->expiry_date ? $cert->expiry_date->format('d M Y') : '—' }}
              @if($cert->expiry_status === 'expired') (expired) @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  @if(count($resume->section('skills')))
    <h2>Skills</h2>
    <table class="grid">
      @foreach($resume->section('skills') as $row)
        <tr>
          <td class="k">{{ $row['name'] ?? '' }}</td>
          <td class="v">{{ $row['level'] ?? '' }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  @if(count($resume->section('languages')))
    <h2>Languages</h2>
    <table class="grid">
      @foreach($resume->section('languages') as $row)
        <tr>
          <td class="k">{{ $row['name'] ?? '' }}</td>
          <td class="v">{{ $row['proficiency'] ?? '' }}</td>
        </tr>
      @endforeach
    </table>
  @endif

  @if(count($resume->section('references')))
    <h2>References</h2>
    @foreach($resume->section('references') as $row)
      <div class="entry">
        <div class="line1">{{ $row['name'] ?? '' }}</div>
        <div class="line2">
          {{ $row['position'] ?? '' }}@if(!empty($row['company'])) , {{ $row['company'] }}@endif
          @if(!empty($row['contact'])) — {{ $row['contact'] }}@endif
        </div>
      </div>
    @endforeach
  @endif

  @if($resume->isEmpty() && !$certifications->count())
    <p class="empty">No resume details have been recorded for this employee yet.</p>
  @endif

  <div class="footer">
    {{ $employee->company?->company_name ?? env('APP_NAME') }} — generated {{ now()->format('d M Y') }}
  </div>

</div>
</body>
</html>
