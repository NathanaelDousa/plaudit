<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Week3Test extends TestCase
{
    use RefreshDatabase;

    public function test_widget_api_returns_only_published_public_fields(): void
    {
        User::create(['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'correct-horse-battery']);
        $project = Project::create(['name' => 'Acme', 'brand_color' => '#112233']);
        $make = fn (array $a) => $project->testimonials()->create(array_merge(['body' => 'Nice work.', 'consent' => true, 'email' => 'secret@example.test'], $a));
        $make(['name' => 'Pending Pat']);
        $make(['name' => 'Hidden Hal', 'status' => 'hidden']);
        $make(['name' => 'Amy', 'status' => 'approved', 'rating' => 5]);
        $make(['name' => 'Fay', 'status' => 'approved', 'featured' => true]);

        $response = $this->getJson('/api/walls/acme', ['Origin' => 'https://customer-site.example'])
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', '*')
            ->assertJsonPath('brand_color', '#112233')
            ->assertJsonPath('testimonials.0.name', 'Fay')
            ->assertJsonPath('testimonials.1.name', 'Amy')
            ->assertJsonCount(2, 'testimonials');

        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertStringNotContainsString('secret@example.test', $response->getContent());
        $this->assertSame(['name', 'title', 'rating', 'body', 'avatar_url', 'featured'], array_keys($response->json('testimonials.0')));
    }

    public function test_widget_api_works_before_setup_and_404s_unknown_projects(): void
    {
        // API routes skip the web group, so no setup redirect, session or cookies.
        $this->getJson('/api/walls/nope')->assertNotFound();
        $this->assertFalse($this->getJson('/api/walls/nope')->headers->has('Set-Cookie'));
    }
}
