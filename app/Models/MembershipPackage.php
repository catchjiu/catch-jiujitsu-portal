<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPackage extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'duration_type',
        'duration_value',
        'price',
        'age_group',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get formatted duration string.
     */
    public function getDurationLabelAttribute(): string
    {
        if ($this->duration_type === 'classes') {
            return $this->duration_value . ' Classes';
        }

        $unit = $this->duration_type;
        if ($this->duration_value === 1) {
            // Remove the 's' for singular
            $unit = rtrim($unit, 's');
        }

        return $this->duration_value . ' ' . ucfirst($unit);
    }

    /**
     * Scope to get only active packages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
