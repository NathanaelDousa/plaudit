<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImportExportController extends Controller
{
    private const COLUMNS = ['name', 'title', 'email', 'rating', 'body', 'status', 'featured', 'source', 'avatar_url', 'created_at'];

    public function import(Request $request, Project $project)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
            'consent' => ['accepted'],
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        $header = fgetcsv($handle, escape: '');
        if (! $header) {
            return back()->withErrors(['file' => 'The file is empty.']);
        }
        // Normalise headers; strip a UTF-8 BOM that Excel adds.
        $header = array_map(fn ($h) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $h))), $header);
        if (! in_array('name', $header) || ! in_array('body', $header)) {
            fclose($handle);

            return back()->withErrors(['file' => 'The first row must name the columns, including “name” and “body”.']);
        }

        $imported = $skipped = 0;
        while (($row = fgetcsv($handle, escape: '')) !== false && $imported + $skipped < 1000) {
            if ($row === [null]) {
                continue; // blank line
            }
            $record = array_combine($header, array_pad(array_slice($row, 0, count($header)), count($header), null));
            $data = [
                'name' => trim((string) ($record['name'] ?? '')),
                'title' => trim((string) ($record['title'] ?? '')) ?: null,
                'email' => trim((string) ($record['email'] ?? '')) ?: null,
                'rating' => trim((string) ($record['rating'] ?? '')) ?: null,
                'body' => trim((string) ($record['body'] ?? '')),
            ];
            $valid = Validator::make($data, [
                'name' => ['required', 'max:100'],
                'title' => ['nullable', 'max:120'],
                'email' => ['nullable', 'email', 'max:255'],
                'rating' => ['nullable', 'integer', 'between:1,5'],
                'body' => ['required', 'max:2000'],
            ])->passes();

            if (! $valid) {
                $skipped++;

                continue;
            }
            // Imports land in Pending so the owner reviews them before anything goes public.
            $project->testimonials()->create([...$data, 'consent' => true, 'source' => 'import']);
            $imported++;
        }
        fclose($handle);

        $message = "Imported $imported ".str('testimonial')->plural($imported).' into Pending.';
        if ($skipped) {
            $message .= " Skipped $skipped ".str('row')->plural($skipped).' with a missing name or text, or invalid values.';
        }

        return redirect()->route('projects.show', [$project, 'status' => 'pending'])->with('status', $message);
    }

    public function export(Project $project, string $format)
    {
        $rows = $project->testimonials()->oldest()->get()->map(fn (Testimonial $t) => [
            'name' => $t->name,
            'title' => $t->title,
            'email' => $t->email,
            'rating' => $t->rating,
            'body' => $t->body,
            'status' => $t->status,
            'featured' => $t->featured,
            'source' => $t->source,
            'avatar_url' => $t->avatar_path ? asset($t->avatar_path) : null,
            'created_at' => $t->created_at->toIso8601String(),
        ]);
        $filename = "{$project->slug}-testimonials.$format";

        if ($format === 'json') {
            return response()->json($rows, 200, ['Content-Disposition' => "attachment; filename=\"$filename\""],
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, self::COLUMNS, escape: '');
            foreach ($rows as $row) {
                fputcsv($out, array_map([self::class, 'csvCell'], array_values($row)), escape: '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Stop spreadsheet apps from running formulas a stranger typed into the form.
    private static function csvCell(mixed $value): mixed
    {
        if (is_bool($value)) {
            return $value ? 'yes' : 'no';
        }

        return is_string($value) && preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }
}
