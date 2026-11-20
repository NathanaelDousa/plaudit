<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Testimonial;

class WallController extends Controller
{
    public function show(Project $project)
    {
        return view('public.wall', [
            'project' => $project,
            'testimonials' => $project->testimonials()->published()->get(),
        ]);
    }

    // Public data for the embed widget: approved testimonials only, never emails.
    public function json(Project $project)
    {
        return response()->json([
            'name' => $project->name,
            'brand_color' => $project->brand_color,
            'wall_url' => route('wall', $project),
            'testimonials' => $project->testimonials()->published()->limit(100)->get()->map(fn (Testimonial $t) => [
                'name' => $t->name,
                'title' => $t->title,
                'rating' => $t->rating,
                'body' => $t->body,
                'avatar_url' => $t->avatar_path ? asset($t->avatar_path) : null,
                'featured' => $t->featured,
            ]),
        ]);
    }
}
