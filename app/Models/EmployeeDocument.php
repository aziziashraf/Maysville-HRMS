<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_document_type_id',
        'title',
        'reference_no',
        'issued_by',
        'issue_date',
        'expiry_date',
        'remarks',
        'custom_values',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'custom_values' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documentType()
    {
        return $this->belongsTo(EmployeeDocumentType::class, 'employee_document_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * The label shown to users: the record's own title if one was given,
     * otherwise the name of its (admin-defined) type.
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: ($this->documentType->name ?? 'Document');
    }

    public function getDaysToExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return Carbon::today()->diffInDays($this->expiry_date->copy()->startOfDay(), false);
    }

    /**
     * Warning window for this record, taken from its type so each kind of
     * document can have its own lead time.
     */
    public function getWarningDaysAttribute(): int
    {
        return (int) ($this->documentType->expiry_warning_days ?? 30);
    }

    /**
     * ok | expiring | expired | none
     */
    public function getExpiryStatusAttribute(): string
    {
        $days = $this->days_to_expiry;

        if ($days === null) {
            return 'none';
        }
        if ($days < 0) {
            return 'expired';
        }
        if ($days <= $this->warning_days) {
            return 'expiring';
        }

        return 'ok';
    }

    public function getExpiryBadgeClassAttribute(): string
    {
        return [
            'expired' => 'danger',
            'expiring' => 'warning',
            'ok' => 'success',
            'none' => 'secondary',
        ][$this->expiry_status];
    }

    public function getExpiryLabelAttribute(): string
    {
        $days = $this->days_to_expiry;

        if ($days === null) {
            return 'No expiry';
        }
        if ($days < 0) {
            return 'Expired ' . abs($days) . ' ' . str('day')->plural(abs($days)) . ' ago';
        }
        if ($days === 0) {
            return 'Expires today';
        }

        return 'Expires in ' . $days . ' ' . str('day')->plural($days);
    }

    public function scopeWithExpiry($query)
    {
        return $query->whereNotNull('expiry_date');
    }

    /**
     * Records that need attention: already expired, or inside the warning
     * window their type defines.
     *
     * Each type can carry a different warning window, so the SQL side narrows
     * by the widest window in use and the per-type comparison is finished in
     * PHP. That keeps the query portable across sqlite/mysql (no vendor date
     * arithmetic) and the row count here is small — one handful per employee.
     */
    public static function needingAttention($departmentId = null)
    {
        $widestWindow = (int) (EmployeeDocumentType::max('expiry_warning_days') ?? 30);

        return static::with(['user.department', 'documentType'])
            ->withExpiry()
            ->whereHas('user', function ($query) use ($departmentId) {
                $query->where('is_active', 1);

                if ($departmentId) {
                    $query->where('department_id', $departmentId);
                }
            })
            ->where('expiry_date', '<=', Carbon::today()->addDays($widestWindow)->toDateString())
            ->orderBy('expiry_date')
            ->get()
            ->filter(fn ($doc) => in_array($doc->expiry_status, ['expired', 'expiring'], true))
            ->values();
    }
}
