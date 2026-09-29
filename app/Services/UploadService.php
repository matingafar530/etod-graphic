<?php

namespace App\Services;

use App\Models\Upload;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class UploadService
{
    public function store(UploadedFile $file, ?int $userId, string $sessionId): Upload
    {
        $mime = $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            throw ValidationException::withMessages(['image' => 'فرمت تصویر باید JPG، PNG یا WEBP باشد.']);
        }

        $dimensions = @getimagesize($file->getRealPath());
        if (! $dimensions || $dimensions[0] < 300 || $dimensions[1] < 300 || $dimensions[0] > 10000 || $dimensions[1] > 10000) {
            throw ValidationException::withMessages(['image' => 'ابعاد تصویر باید بین ۳۰۰ تا ۱۰۰۰۰ پیکسل باشد.']);
        }

        $path = $file->store('customizations/originals', 'local');

        return Upload::create([
            'user_id' => $userId, 'session_id' => $sessionId, 'disk' => 'local', 'path' => $path,
            'original_name' => $file->getClientOriginalName(), 'mime_type' => $mime, 'size' => $file->getSize(),
            'width' => $dimensions[0], 'height' => $dimensions[1], 'sha256' => hash_file('sha256', $file->getRealPath()),
        ]);
    }
}
