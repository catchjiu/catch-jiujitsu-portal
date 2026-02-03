<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassTrial extends Model
{
    protected $fillable = [
        'class_id',
        'name',
        'age',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class, 'class_id');
    }
}
