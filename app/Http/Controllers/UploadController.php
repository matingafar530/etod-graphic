<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UploadController extends Controller
{
    public function store(Request $request, UploadService $service): Response
    {
        $request->validate(['image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:10240']], ['image.required' => 'لطفاً تصویر خود را انتخاب کنید.']);
        $upload = $service->store($request->file('image'), $request->user()?->id, $request->session()->getId());

        return response(['id' => $upload->id, 'preview_url' => route('uploads.show', $upload), 'width' => $upload->width, 'height' => $upload->height], 201);
    }

    public function show(Request $request, Upload $upload): BinaryFileResponse
    {
        $ownedByUser = $upload->user_id !== null && $upload->user_id === $request->user()?->id;
        $ownedBySession = $upload->user_id === null && $upload->session_id === $request->session()->getId();
        abort_unless($ownedByUser || $ownedBySession, 404);
        abort_unless($upload->disk === 'local' && Storage::disk('local')->exists($upload->path), 404);

        return response()->file(Storage::disk('local')->path($upload->path), ['Content-Type' => $upload->mime_type, 'X-Content-Type-Options' => 'nosniff']);
    }
}
