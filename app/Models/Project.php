<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'brand_color', 'prompt'])]
class Project extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            $base = Str::slug($project->name) ?: 'project';
            $slug = $base;
            while (static::where('slug', $slug)->exists()) {
                $slug = $base.'-'.Str::lower(Str::random(4));
            }
            $project->slug = $slug;
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function formPrompt(): string
    {
        return $this->prompt ?: "What do you think of {$this->name}?";
    }
}
