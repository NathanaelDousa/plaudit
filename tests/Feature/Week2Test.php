<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Week2Test extends TestCase
{
    use RefreshDatabase;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'correct-horse-battery']));
        $this->project = Project::create(['name' => 'Acme']);
    }

    private function testimonial(array $attrs = [], ?Project $project = null): Testimonial
    {
        return ($project ?? $this->project)->testimonials()->create(array_merge([
            'name' => 'Ada', 'body' => 'Great product, would buy again.', 'consent' => true,
        ], $attrs));
    }

    public function test_owner_approves_hides_and_features(): void
    {
        $t = $this->testimonial();

        $this->patch("/projects/acme/testimonials/{$t->id}", ['status' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $t->fresh()->status);

        $this->patch("/projects/acme/testimonials/{$t->id}", ['featured' => 1]);
        $this->assertTrue($t->fresh()->featured);

        $this->patch("/projects/acme/testimonials/{$t->id}", ['status' => 'hidden']);
        $this->assertSame('hidden', $t->fresh()->status);

        $this->patch("/projects/acme/testimonials/{$t->id}", ['status' => 'published-by-hacker'])->assertSessionHasErrors('status');
    }

    public function test_moderation_never_edits_customer_words(): void
    {
        $t = $this->testimonial();
        $this->patch("/projects/acme/testimonials/{$t->id}", ['body' => 'Altered!', 'name' => 'Someone else', 'status' => 'approved']);

        $this->assertSame('Great product, would buy again.', $t->fresh()->body);
        $this->assertSame('Ada', $t->fresh()->name);
    }

    public function test_testimonials_cannot_be_reached_through_another_project(): void
    {
        $other = Project::create(['name' => 'Other']);
        $t = $this->testimonial([], $other);

        $this->patch("/projects/acme/testimonials/{$t->id}", ['status' => 'approved'])->assertNotFound();
        $this->delete("/projects/acme/testimonials/{$t->id}")->assertNotFound();
        $this->assertSame('pending', $t->fresh()->status);
    }

    public function test_delete_removes_testimonial_and_photo(): void
    {
        $this->post('/projects/acme/testimonials', [
            'name' => 'Bo', 'body' => 'Lovely.', 'consent' => '1',
            'avatar' => UploadedFile::fake()->image('bo.jpg', 300, 300),
        ]);
        $t = $this->project->testimonials()->sole();
        $this->assertSame(['approved', 'manual'], [$t->status, $t->source]);
        $this->assertFileExists(public_path($t->avatar_path));

        $this->delete("/projects/acme/testimonials/{$t->id}")->assertRedirect();
        $this->assertModelMissing($t);
        $this->assertFileDoesNotExist(public_path($t->avatar_path));
    }

    public function test_wall_shows_only_approved_with_featured_first_and_never_emails(): void
    {
        $this->testimonial(['name' => 'Pending Pat']);
        $this->testimonial(['name' => 'Hidden Hal', 'status' => 'hidden']);
        $this->testimonial(['name' => 'Approved Amy', 'status' => 'approved', 'email' => 'amy@secret.test']);
        $this->testimonial(['name' => 'Featured Fay', 'status' => 'approved', 'featured' => true]);

        auth()->logout();
        $this->get('/w/acme')->assertOk()
            ->assertSeeInOrder(['Featured Fay', 'Approved Amy'])
            ->assertDontSee('Pending Pat')->assertDontSee('Hidden Hal')
            ->assertDontSee('amy@secret.test');
    }

    public function test_csv_import_creates_pending_rows_and_skips_bad_ones(): void
    {
        $csv = "\xEF\xBB\xBFName,Body,Rating,Email,Title\n"
            ."Ada,\"Fast, friendly, \"\"great\"\"\",5,ada@example.test,CEO\n"
            .",Missing name,,,\n"
            ."Bo,Bad rating,9,,\n"
            ."\n"
            ."Cy,Solid support,,,\n";
        $file = UploadedFile::fake()->createWithContent('t.csv', $csv);

        $this->post('/projects/acme/import', ['file' => $file, 'consent' => '1'])
            ->assertSessionHas('status', fn ($s) => str_contains($s, 'Imported 2') && str_contains($s, 'Skipped 2'));

        $rows = $this->project->testimonials()->orderBy('id')->get();
        $this->assertSame(['Ada', 'Cy'], $rows->pluck('name')->all());
        $this->assertSame('Fast, friendly, "great"', $rows[0]->body);
        $this->assertSame([5, 'CEO', 'pending', 'import'], [$rows[0]->rating, $rows[0]->title, $rows[0]->status, $rows[0]->source]);
    }

    public function test_csv_import_requires_header_and_consent(): void
    {
        $file = UploadedFile::fake()->createWithContent('t.csv', "Ada,Great\n");
        $this->post('/projects/acme/import', ['file' => $file])->assertSessionHasErrors('consent');
        $this->post('/projects/acme/import', ['file' => $file, 'consent' => '1'])->assertSessionHasErrors('file');
        $this->assertSame(0, $this->project->testimonials()->count());
    }

    public function test_export_csv_neutralises_formulas_and_json_round_trips(): void
    {
        $this->testimonial(['name' => '=HYPERLINK("http://evil")', 'rating' => 4]);

        $csv = $this->get('/projects/acme/export.csv')->assertOk()->streamedContent();
        $this->assertStringStartsWith('name,title,email,rating,body', $csv);
        $this->assertStringContainsString("'=HYPERLINK", $csv);

        $this->get('/projects/acme/export.json')->assertOk()
            ->assertJsonPath('0.name', '=HYPERLINK("http://evil")')
            ->assertJsonPath('0.rating', 4);
        $this->get('/projects/acme/export.xml')->assertNotFound();
    }

    public function test_inbox_defaults_to_pending_and_filters(): void
    {
        $this->testimonial(['name' => 'Waiting Will']);
        $this->testimonial(['name' => 'Live Lou', 'status' => 'approved']);

        $this->get('/projects/acme')->assertSee('Waiting Will')->assertDontSee('Live Lou');
        $this->get('/projects/acme?status=approved')->assertSee('Live Lou')->assertDontSee('Waiting Will');
        $this->get('/projects/acme?status=all')->assertSee('Live Lou')->assertSee('Waiting Will');
    }

    public function test_admin_actions_require_login(): void
    {
        $t = $this->testimonial();
        auth()->logout();
        $this->patch("/projects/acme/testimonials/{$t->id}", ['status' => 'approved'])->assertRedirect('/login');
        $this->get('/projects/acme/export.csv')->assertRedirect('/login');
        $this->assertSame('pending', $t->fresh()->status);
    }

    public function test_deleting_a_project_removes_its_testimonials_and_photos(): void
    {
        $this->post('/projects/acme/testimonials', [
            'name' => 'Bo', 'body' => 'Lovely.', 'consent' => '1',
            'avatar' => UploadedFile::fake()->image('bo.jpg', 100, 100),
        ]);
        $path = public_path($this->project->testimonials()->sole()->avatar_path);

        $this->delete('/projects/acme')->assertRedirect('/projects');
        $this->assertModelMissing($this->project);
        $this->assertSame(0, Testimonial::count());
        $this->assertFileDoesNotExist($path);
    }
}
