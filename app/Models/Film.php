<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Film extends Model
{
    use HasFactory;

    protected $table = 'films';

    protected $fillable = ['title', 'sinopsis', 'year', 'poster', 'genre_id'];

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class);
    }

    public function castMembers(): BelongsToMany
    {
        return $this->belongsToMany(Cast::class, 'perans')
            ->withPivot('actor')
            ->withTimestamps();
    }
}
