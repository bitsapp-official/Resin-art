<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Tests\TestCase;

class ProductVideoUploadLimitTest extends TestCase
{
    public function test_livewire_temporary_upload_allows_up_to_15mb_product_video(): void
    {
        $this->assertSame(['required', 'file', 'max:15360'], FileUploadConfiguration::rules());
        $this->assertContains('webm', config('livewire.temporary_file_upload.preview_mimes', []));

        Storage::fake(FileUploadConfiguration::disk());

        $uploadUrl = URL::temporarySignedRoute(
            'livewire.upload-file',
            now()->addMinutes(5)
        );

        // 14.5 MiB (~15.2 MB decimal) video — exceeds Livewire's old 12 MB / 12288 KB limit, within 15 MB / 15360 KB
        $video145Mb = UploadedFile::fake()->create('1CT RING.mp4', 14848, 'video/mp4');

        $response = $this->postJson($uploadUrl, [
            'files' => [$video145Mb],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['paths']);

        $this->assertNotEmpty($response->json('paths'));
    }

    public function test_livewire_temporary_upload_rejects_video_larger_than_15mb(): void
    {
        Storage::fake(FileUploadConfiguration::disk());

        $uploadUrl = URL::temporarySignedRoute(
            'livewire.upload-file',
            now()->addMinutes(5)
        );

        // 15.5 MiB (15872 KB) video — exceeds 15 MB / 15360 KB production cap
        $videoOver15Mb = UploadedFile::fake()->create('oversized-showcase.mp4', 15872, 'video/mp4');

        $response = $this->postJson($uploadUrl, [
            'files' => [$videoOver15Mb],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['files.0']);
    }
}
