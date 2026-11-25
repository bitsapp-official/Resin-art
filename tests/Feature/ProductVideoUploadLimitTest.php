<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Tests\TestCase;

class ProductVideoUploadLimitTest extends TestCase
{
    public function test_livewire_temporary_upload_allows_up_to_20mb_product_video(): void
    {
        $this->assertSame(['required', 'file', 'max:20480'], FileUploadConfiguration::rules());
        $this->assertContains('webm', config('livewire.temporary_file_upload.preview_mimes', []));

        Storage::fake(FileUploadConfiguration::disk());

        $uploadUrl = URL::temporarySignedRoute(
            'livewire.upload-file',
            now()->addMinutes(5)
        );

        // 19 MiB (19456 KB) video — within 20 MB / 20480 KB production cap
        $video19Mb = UploadedFile::fake()->create('1CT RING.mp4', 19456, 'video/mp4');

        $response = $this->postJson($uploadUrl, [
            'files' => [$video19Mb],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['paths']);

        $this->assertNotEmpty($response->json('paths'));
    }

    public function test_livewire_temporary_upload_rejects_video_larger_than_20mb(): void
    {
        Storage::fake(FileUploadConfiguration::disk());

        $uploadUrl = URL::temporarySignedRoute(
            'livewire.upload-file',
            now()->addMinutes(5)
        );

        // 21 MiB (21504 KB) video — exceeds 20 MB / 20480 KB cap
        $videoOver20Mb = UploadedFile::fake()->create('oversized-showcase.mp4', 21504, 'video/mp4');

        $response = $this->postJson($uploadUrl, [
            'files' => [$videoOver20Mb],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }
}
