<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HandBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'handbookcategory_id',
        'name',
        'description',
        'status',
        'content',
        'index_number',
    ];

    public function handbookCategory()
    {
        return $this->belongsTo(HandBookCategory::class, 'handbookcategory_id');
    }
}
