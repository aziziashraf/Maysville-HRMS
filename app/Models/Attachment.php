<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class Attachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'attachable_id',
        'attachable_type',
        'filename',
        'path',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    // custom delete function to delete the associated file
    public function delete()
    {
        // Delete the associated file before deleting the attachment record
        if ($this->path) {
            Storage::delete('attachments/' . $this->path);
        }

        return parent::delete();
    }
}
