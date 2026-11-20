<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class Week1Test extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        return User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'correct-horse-battery']);
    }

    private function validSubmission(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Ada', 'title' => 'Founder, Acme', 'rating' => 5,
            'body' => 'Genuinely changed how we work.', 'consent' => '1',
        ], $overrides);
    }

    public function test_fresh_install_sends_everyone_to_setup_once(): void
    {
        $this->get('/projects')->assertRedirect('/setup');

        $this->post('/setup', [
            'name' => 'Owner', 'email' => 'owner@example.test',
            'password' => 'long-enough-pw', 'password_confirmation' => 'long-enough-pw',
        ])->assertRedirect('/projects');

        $this->assertAuthenticated();
        $this->get('/setup')->assertNotFound();
        $this->post('/setup', [])->assertNotFound();
    }

    public function test_admin_pages_require_login(): void
    {
        $this->owner();
        $this->get('/projects')->assertRedirect('/login');
        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'owner@example.test', 'password' => 'correct-horse-battery'])->assertRedirect('/projects');
    }

    public function test_owner_creates_and_edits_projects_with_unique_slugs(): void
    {
        $this->actingAs($this->owner());

        $this->post('/projects', ['name' => 'Acme App', 'brand_color' => '#ff0000'])->assertRedirect('/projects/acme-app');
        $this->post('/projects', ['name' => 'Acme App', 'brand_color' => '#ff0000']);
        $this->assertSame(2, Project::where('slug', 'like', 'acme-app%')->distinct()->count('slug'));

        $this->post('/projects', ['name' => 'Bad', 'brand_color' => 'red;}body{'])->assertSessionHasErrors('brand_color');
        $this->put('/projects/acme-app', ['name' => 'Acme', 'brand_color' => '#00ff00'])->assertRedirect('/projects/acme-app');
        $this->assertSame('Acme', Project::where('slug', 'acme-app')->value('name'));
    }

    public function test_public_form_saves_a_pending_testimonial(): void
    {
        $this->owner();
        $project = Project::create(['name' => 'Acme']);

        $this->get('/t/acme')->assertOk()->assertSee('What do you think of Acme?');
        $this->post('/t/acme', $this->validSubmission())->assertRedirect('/t/acme/thanks');

        $t = $project->testimonials()->sole();
        $this->assertSame('pending', $t->status);
        $this->assertSame('form', $t->source);
        $this->assertTrue($t->consent);
    }

    public function test_public_form_requires_consent_and_escapes_input(): void
    {
        $this->owner();
        $project = Project::create(['name' => 'Acme']);

        $this->post('/t/acme', $this->validSubmission(['consent' => null]))->assertSessionHasErrors('consent');
        $this->assertSame(0, $project->testimonials()->count());

        $this->post('/t/acme', $this->validSubmission(['name' => '<script>alert(1)</script>']));
        $this->actingAs(User::first())->get('/projects/acme')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_honeypot_silently_drops_bots(): void
    {
        $this->owner();
        $project = Project::create(['name' => 'Acme']);

        $this->post('/t/acme', $this->validSubmission(['website' => 'http://spam.example']))->assertRedirect('/t/acme/thanks');
        $this->assertSame(0, $project->testimonials()->count());
    }

    public function test_avatar_is_reencoded_to_a_square_jpeg(): void
    {
        $this->owner();
        $project = Project::create(['name' => 'Acme']);

        $this->post('/t/acme', $this->validSubmission(['avatar' => UploadedFile::fake()->image('me.png', 800, 600)]));

        $path = public_path($project->testimonials()->sole()->avatar_path);
        try {
            $this->assertFileExists($path);
            [$w, $h, $type] = getimagesize($path);
            $this->assertSame([256, 256, IMAGETYPE_JPEG], [$w, $h, $type]);
        } finally {
            @unlink($path);
        }
    }

    public function test_submissions_are_rate_limited(): void
    {
        $this->owner();
        Project::create(['name' => 'Acme']);

        foreach (range(1, 5) as $_) {
            $this->post('/t/acme', $this->validSubmission());
        }
        $this->post('/t/acme', $this->validSubmission())->assertTooManyRequests();
    }
}
