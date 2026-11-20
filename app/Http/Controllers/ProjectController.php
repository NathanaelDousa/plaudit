<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Testimonial;
use App\Support\Avatar;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return view('projects.index', [
            'projects' => Project::withCount('testimonials')->latest()->get(),
        ]);
    }

    public function create()
    {
        return view('projects.form', ['project' => new Project]);
    }

    public function store(Request $request)
    {
        $project = Project::create($this->validated($request));

        return redirect()->route('projects.show', $project);
    }

    public function show(Request $request, Project $project)
    {
        $counts = $project->testimonials()->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $status = in_array($request->query('status'), Testimonial::STATUSES) ? $request->query('status') : null;
        // Default to the inbox when something is waiting, otherwise show everything.
        $status ??= $request->has('status') || ! $counts->get('pending') ? 'all' : 'pending';

        return view('projects.show', [
            'project' => $project,
            'status' => $status,
            'counts' => $counts,
            'testimonials' => $project->testimonials()
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->orderByDesc('featured')->latest()->get(),
        ]);
    }

    public function edit(Project $project)
    {
        return view('projects.form', ['project' => $project]);
    }

    public function update(Request $request, Project $project)
    {
        $project->update($this->validated($request));

        return redirect()->route('projects.show', $project)->with('status', 'Saved.');
    }

    public function destroy(Project $project)
    {
        $project->testimonials()->pluck('avatar_path')->each(fn ($path) => Avatar::delete($path));
        $project->delete();

        return redirect()->route('projects.index')->with('status', "Deleted {$project->name}.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'brand_color' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'prompt' => ['nullable', 'string', 'max:200'],
        ]);
    }
}
