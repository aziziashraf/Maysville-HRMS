<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Models\EmployeeResume;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;

class EmployeeResumeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * The fill-in form.
     */
    public function edit(User $user)
    {
        return view('employee_resume.edit', [
            'employee' => $user->load(['department', 'position', 'company']),
            'resume' => $this->resumeFor($user),
            'sections' => EmployeeResume::SECTIONS,
        ]);
    }

    /**
     * Save the resume.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'headline' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:4000',
        ]);

        $data = [
            'headline' => $request->headline,
            'summary' => $request->summary,
            'updated_by' => Auth::id(),
        ];

        foreach (array_keys(EmployeeResume::SECTIONS) as $section) {
            $data[$section] = EmployeeResume::cleanSection($section, $request->input($section, []));
        }

        $resume = EmployeeResume::firstOrNew(['user_id' => $user->id]);

        if (!$resume->exists) {
            $data['created_by'] = Auth::id();
        }

        $resume->fill($data)->save();

        return redirect()
            ->route('employee_resume.edit', $user)
            ->with('success', 'Resume saved for ' . $user->name . '.');
    }

    /**
     * Printable view — also the fallback when PDF rendering is unavailable,
     * since any browser can save it as a PDF.
     */
    public function preview(User $user)
    {
        return view('employee_resume.pdf', $this->pdfData($user) + ['printable' => true]);
    }

    /**
     * Stream the resume as a PDF.
     *
     * The bundled wkhtmltopdf is a Linux amd64 binary (h4cc/wkhtmltopdf-amd64),
     * so this works on the deployment target but not on a Windows machine. When
     * the binary cannot run we fall back to the printable view rather than
     * throwing, so the feature stays usable in local development.
     */
    public function download(User $user)
    {
        $binary = config('snappy.pdf.binary');

        if (!$this->binaryUsable($binary)) {
            return redirect()
                ->route('employee_resume.preview', $user)
                ->with('error', 'PDF rendering is unavailable on this machine (wkhtmltopdf is a Linux binary). Showing the printable version — use your browser\'s Print, then "Save as PDF".');
        }

        try {
            $pdf = PDF::loadView('employee_resume.pdf', $this->pdfData($user) + ['printable' => false]);
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOption('margin-top', 12);
            $pdf->setOption('margin-bottom', 12);
            $pdf->setOption('margin-left', 12);
            $pdf->setOption('margin-right', 12);

            return $pdf->download($this->filename($user));
        } catch (\Throwable $e) {
            Log::error('Resume PDF generation failed: ' . $e->getMessage());

            return redirect()
                ->route('employee_resume.preview', $user)
                ->with('error', 'Could not generate the PDF (' . $e->getMessage() . '). Showing the printable version instead.');
        }
    }

    private function resumeFor(User $user): EmployeeResume
    {
        return EmployeeResume::firstOrNew(['user_id' => $user->id]);
    }

    /**
     * Everything the resume layout needs. Certifications are not re-typed here
     * — they are read from the employee information already captured against
     * the staff member, so the resume cannot drift from the record.
     */
    private function pdfData(User $user): array
    {
        $certifications = EmployeeDocument::with('documentType')
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn ($doc) => (bool) ($doc->documentType->has_expiry ?? false))
            ->sortByDesc(fn ($doc) => $doc->expiry_date)
            ->values();

        return [
            'employee' => $user->load(['department', 'position', 'company']),
            'resume' => $this->resumeFor($user),
            'certifications' => $certifications,
        ];
    }

    private function binaryUsable(?string $binary): bool
    {
        if (!$binary || !file_exists($binary)) {
            return false;
        }

        // A Linux ELF binary is present but unrunnable on Windows.
        return stripos(PHP_OS_FAMILY, 'Windows') === false && is_executable($binary);
    }

    private function filename(User $user): string
    {
        $base = $user->staff_id ? $user->staff_id . '_' . $user->name : $user->name;

        return 'Resume_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $base) . '.pdf';
    }
}
