<?php

namespace Tests\Feature;

use App\Models\Upload;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_invalid_file_is_rejected(): void
    {
        $response = $this->postJson(route('uploads.store'), ['image' => UploadedFile::fake()->create('script.php', 100, 'text/php')]);
        $response->assertUnprocessable()->assertJsonValidationErrors('image');
    }
}
