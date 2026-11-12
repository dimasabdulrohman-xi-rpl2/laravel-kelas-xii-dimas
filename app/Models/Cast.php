<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Cast extends Model
{
    use HasFactory;

    protected $table = 'casts';

    protected $fillable = ['name', 'age', 'bio'];

    public function films(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'perans')
            ->withPivot('actor')
            ->withTimestamps();
    }
}
