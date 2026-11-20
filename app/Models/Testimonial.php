<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['name', 'email', 'title', 'avatar_path', 'rating', 'body', 'consent', 'source', 'status', 'featured'])]
class Testimonial extends Model
{
    public const STATUSES = ['pending', 'approved', 'hidden'];

    protected function casts(): array
    {
        return ['consent' => 'boolean', 'featured' => 'boolean', 'rating' => 'integer'];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // What the public wall (and later the widget) may show: approved only, favorites first.
    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'approved')->orderByDesc('featured')->latest();
    }
}
