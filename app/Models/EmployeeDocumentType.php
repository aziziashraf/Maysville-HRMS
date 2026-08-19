<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EmployeeDocumentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'has_expiry',
        'expiry_warning_days',
        'requires_attachment',
        'is_active',
        'sort_order',
        'custom_fields',
    ];

    protected $casts = [
        'has_expiry' => 'boolean',
        'requires_attachment' => 'boolean',
        'is_active' => 'boolean',
        'custom_fields' => 'array',
    ];

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Normalise the admin-supplied custom field rows into
     * [ ['key' => .., 'label' => .., 'type' => .., 'required' => bool], ... ].
     *
     * Keys are derived from the label once and then kept, so renaming a label
     * later does not orphan the values already stored against it.
     */
    public static function normaliseCustomFields($rows): array
    {
        $fields = [];
        $used = [];

        foreach ((array) $rows as $row) {
            $label = trim($row['label'] ?? '');
            if ($label === '') {
                continue;
            }

            $key = trim($row['key'] ?? '');
            if ($key === '') {
                $key = Str::slug($label, '_') ?: 'field';
            }

            // Guarantee uniqueness within the type.
            $base = $key;
            $i = 2;
            while (in_array($key, $used, true)) {
                $key = $base . '_' . $i++;
            }
            $used[] = $key;

            $type = $row['type'] ?? 'text';
            if (!in_array($type, ['text', 'number', 'date', 'textarea'], true)) {
                $type = 'text';
            }

            $fields[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => !empty($row['required']),
            ];
        }

        return $fields;
    }

    public function customFieldList(): array
    {
        return $this->custom_fields ?? [];
    }
}
