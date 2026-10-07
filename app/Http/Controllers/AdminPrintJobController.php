<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use App\Models\Upload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPrintJobController extends Controller
{
    public function status(Request $request, PrintJob $job): RedirectResponse
    {
        $this->guardLocal();
        $data = $request->validate([
            'status' => ['required', 'in:printing,completed,failed,queued'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $next = $data['status'];
        $allowed = ['queued' => ['printing'], 'printing' => ['completed', 'failed'], 'failed' => ['queued']];
        abort_unless(in_array($next, $allowed[$job->status] ?? [], true), 422, 'تغییر وضعیت صف چاپ مجاز نیست.');
        if (in_array($next, ['failed', 'queued'], true)) {
            validator($data, ['notes' => ['required', 'string', 'min:3', 'max:2000']])->validate();
        }
        $entry = trim((string) ($data['notes'] ?? ''));
        $stamp = now()->setTimezone('Asia/Tehran')->format('Y-m-d H:i');
        $job->update([
            'status' => $next,
            'started_at' => $next === 'printing' ? ($job->started_at ?? now()) : ($next === 'queued' ? null : $job->started_at),
            'completed_at' => $next === 'completed' ? now() : null,
            'notes' => trim(($job->notes ? $job->notes."\n" : '')."[{$stamp}] {$next}".($entry ? ": {$entry}" : '')),
        ]);

        return back()->with('success', 'وضعیت کار چاپ به‌روزرسانی شد.');
    }

    public function uploadFile(Request $request, PrintJob $job): RedirectResponse
    {
        $this->guardLocal();
        $data = $request->validate(['file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,tif,tiff', 'max:51200']]);
        $path = $data['file']->store('print-jobs/'.$job->id, 'local');
        $job->update(['file_path' => $path]);

        return back()->with('success', 'فایل آمادهٔ چاپ بارگذاری شد. نسخه‌های قبلی به‌صورت خودکار حذف نمی‌شوند.');
    }

    public function downloadFile(PrintJob $job): StreamedResponse
    {
        $this->guardLocal();
        abort_unless($job->file_path && str_starts_with($job->file_path, 'print-jobs/'.$job->id.'/') && ! str_contains($job->file_path, '..') && Storage::disk('local')->exists($job->file_path), 404);

        return Storage::disk('local')->download($job->file_path, 'print-job-'.$job->id.'.'.pathinfo($job->file_path, PATHINFO_EXTENSION));
    }

    public function customerDesign(PrintJob $job): StreamedResponse
    {
        $this->guardLocal();
        $item = $job->orderItem;
        $path = $item->preview_image_path;
        abort_unless($path && str_starts_with($path, 'customizations/originals/') && ! str_contains($path, '..'), 404);
        $upload = Upload::query()->where('path', $path)->where('disk', 'local')->firstOrFail();
        abort_unless($upload->user_id === $job->order->user_id && $upload->session_id === $job->order->session_id, 404);
        abort_unless(Storage::disk('local')->exists($path), 404);
        $mime = Storage::disk('local')->mimeType($path);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return Storage::disk('local')->download($path, 'customer-design-'.$job->id.'.'.strtolower(pathinfo($path, PATHINFO_EXTENSION)));
    }

    private function guardLocal(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
    }
}
