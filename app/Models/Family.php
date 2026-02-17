<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Family extends Model
{
    protected $fillable = ['primary_user_id'];

    public function primaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'primary_user_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(FamilyMember::class)->orderBy('role')->orderBy('id');
    }

    public function users()
    {
        return $this->hasManyThrough(User::class, FamilyMember::class, 'family_id', 'id', 'id', 'user_id');
    }

    /** Count of children (for limit of 3). */
    public function childrenCount(): int
    {
        return $this->members()->where('role', 'child')->count();
    }

    /** Count of parents (for limit of 1 other parent). */
    public function parentsCount(): int
    {
        return $this->members()->where('role', 'parent')->count();
    }

    /** Max 4 members: 1 parent + 3 kids (or 2 parents + 2 kids). */
    public function canAddMember(string $role): bool
    {
        if ($role === 'child') {
            return $this->childrenCount() < 3;
        }
        return $this->parentsCount() < 2;
    }
}
