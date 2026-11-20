<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\Avatar;
use Illuminate\Http\Request;

// The public, no-account form strangers use to leave a testimonial.
class SubmissionController extends Controller
{
    public function show(Project $project)
    {
        return view('public.form', ['project' => $project]);
    }

    public function store(Request $request, Project $project)
    {
        // Honeypot: real people never see this field, bots fill it. Pretend success.
        if ($request->filled('website')) {
            return redirect()->route('submit.thanks', $project);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'title' => ['nullable', 'string', 'max:120'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'consent' => ['accepted'],
        ]);

        $avatar = $request->file('avatar');
        unset($data['avatar']);
        $data['avatar_path'] = $avatar ? Avatar::store($avatar) : null;
        $data['consent'] = true;
        $data['source'] = 'form';

        $project->testimonials()->create($data);

        return redirect()->route('submit.thanks', $project);
    }

    public function thanks(Project $project)
    {
        return view('public.thanks', ['project' => $project]);
    }
}
