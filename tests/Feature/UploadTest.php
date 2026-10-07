<?php

namespace Tests\Feature;

use App\Http\Controllers\UploadController;
use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_upload_valid_image_to_private_storage(): void
    {
        Storage::fake('local');
        $response = $this->postJson(route('uploads.store'), ['image' => UploadedFile::fake()->image('design.png', 800, 800)]);
        $response->assertCreated()->assertJsonStructure(['id', 'preview_url', 'width', 'height']);
        $upload = Upload::firstOrFail();
        Storage::disk('local')->assertExists($upload->path);
    }

    public function test_upload_owner_can_view_preview(): void
    {
        Storage::fake('local');
        $path = UploadedFile::fake()->image('design.png', 800, 800)->store('customizations/originals', 'local');
        $upload = Upload::create([
            'session_id' => session()->getId(),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'image/png',
            'size' => 1024,
            'width' => 800,
            'height' => 800,
            'sha256' => str_repeat('a', 64),
        ]);

        $request = Request::create("/uploads/{$upload->id}", 'GET');
        $request->setLaravelSession(app('session.store'));
        $response = app(UploadController::class)->show($request, $upload);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_upload_preview_is_hidden_from_other_sessions(): void
    {
        Storage::fake('local');
        $path = UploadedFile::fake()->image('design.png', 800, 800)->store('customizations/originals', 'local');
        $upload = Upload::create([
            'session_id' => 'another-guest-session',
            'disk' => 'local',
            'path' => $path,
            'mime_type' => 'image/png',
            'size' => 1024,
            'width' => 800,
            'height' => 800,
            'sha256' => str_repeat('a', 64),
        ]);

        $response = $this->get(route('uploads.show', $upload));

        $response->assertNotFound();
    }

    public function test_invalid_file_is_rejected(): void
    {
        $response = $this->postJson(route('uploads.store'), ['image' => UploadedFile::fake()->create('script.php', 100, 'text/php')]);
        $response->assertUnprocessable()->assertJsonValidationErrors('image');
    }
}
