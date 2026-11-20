<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Testimonial;
use App\Support\Avatar;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

// Owner-side moderation and manual entry. Customers' words are never editable here.
class TestimonialController extends Controller
{
    public function create(Project $project)
    {
        return view('testimonials.create', ['project' => $project]);
    }

    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'title' => ['nullable', 'string', 'max:120'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'consent' => ['accepted'],
        ]);

        $avatar = $request->file('avatar');
        unset($data['avatar']);
        $project->testimonials()->create([
            ...$data,
            'avatar_path' => $avatar ? Avatar::store($avatar) : null,
            'consent' => true,
            'source' => 'manual',
            'status' => 'approved',
        ]);

        return redirect()->route('projects.show', [$project, 'status' => 'approved'])->with('status', 'Testimonial added.');
    }

    public function update(Request $request, Project $project, Testimonial $testimonial)
    {
        $data = $request->validate([
            'status' => ['sometimes', Rule::in(Testimonial::STATUSES)],
            'featured' => ['sometimes', 'boolean'],
        ]);
        $testimonial->update($data);

        return back();
    }

    public function destroy(Project $project, Testimonial $testimonial)
    {
        Avatar::delete($testimonial->avatar_path);
        $testimonial->delete();

        return back()->with('status', 'Testimonial deleted.');
    }
}
