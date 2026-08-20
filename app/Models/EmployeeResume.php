<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeResume extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'headline',
        'summary',
        'education',
        'experience',
        'skills',
        'languages',
        'references',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'education' => 'array',
        'experience' => 'array',
        'skills' => 'array',
        'languages' => 'array',
        'references' => 'array',
    ];

    /**
     * The repeating sections and the fields each row carries. Drives both the
     * edit form and the cleaning done on save, so the two cannot disagree.
     */
    public const SECTIONS = [
        'experience' => [
            'label' => 'Work Experience',
            'fields' => ['company', 'position', 'start_date', 'end_date', 'description'],
            'required' => ['company', 'position'],
        ],
        'education' => [
            'label' => 'Education',
            'fields' => ['institution', 'qualification', 'field_of_study', 'start_year', 'end_year'],
            'required' => ['institution', 'qualification'],
        ],
        'skills' => [
            'label' => 'Skills',
            'fields' => ['name', 'level'],
            'required' => ['name'],
        ],
        'languages' => [
            'label' => 'Languages',
            'fields' => ['name', 'proficiency'],
            'required' => ['name'],
        ],
        'references' => [
            'label' => 'References',
            'fields' => ['name', 'position', 'company', 'contact'],
            'required' => ['name'],
        ],
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Drop blank rows and any key not declared for the section, so a stray
     * form field can never end up persisted.
     */
    public static function cleanSection(string $section, $rows): array
    {
        $config = self::SECTIONS[$section] ?? null;

        if (!$config) {
            return [];
        }

        $clean = [];

        foreach ((array) $rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $entry = [];

            foreach ($config['fields'] as $field) {
                $value = trim((string) ($row[$field] ?? ''));

                if ($value !== '') {
                    $entry[$field] = $value;
                }
            }

            // A row counts only when at least one of its key fields is filled.
            $hasRequired = false;
            foreach ($config['required'] as $field) {
                if (!empty($entry[$field])) {
                    $hasRequired = true;
                    break;
                }
            }

            if ($hasRequired) {
                $clean[] = $entry;
            }
        }

        return $clean;
    }

    public function section(string $name): array
    {
        return $this->{$name} ?? [];
    }

    public function isEmpty(): bool
    {
        if (trim((string) $this->headline) !== '' || trim((string) $this->summary) !== '') {
            return false;
        }

        foreach (array_keys(self::SECTIONS) as $section) {
            if (!empty($this->section($section))) {
                return false;
            }
        }

        return true;
    }
}
