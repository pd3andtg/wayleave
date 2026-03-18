<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

// Manages system-wide reference/example images shown as visual guides on the project detail page.
// Each image is identified by a key (e.g. 'section8').
// Only admin and officer can upload or replace these images.
// All authenticated users can view them inline (opened in a modal, not downloaded).
class ExampleImageController extends Controller
{
    // Serve the image inline so the browser displays it (not triggers a download).
    public function show(string $key)
    {
        $path = $this->findPath($key);

        abort_if(!$path, 404);

        $disk = config('filesystems.default');

        if ($disk === 's3') {
            // Redirect to a temporary signed URL — browser renders image inline.
            return redirect(Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30)));
        }

        $fullPath = Storage::disk('local')->path($path);
        $mime     = mime_content_type($fullPath) ?: 'image/jpeg';

        return response()->file($fullPath, ['Content-Type' => $mime]);
    }

    // Upload or replace an example image. Admin/officer only — enforced via route middleware.
    public function upload(Request $request, string $key)
    {
        $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // Delete old file for this key if it exists.
        $existing = $this->findPath($key);
        if ($existing) {
            Storage::disk(config('filesystems.default') === 's3' ? 's3' : 'local')->delete($existing);
        }

        $ext  = $request->file('image')->getClientOriginalExtension();
        $path = 'example-images/' . $key . '.' . $ext;

        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';
        Storage::disk($disk)->putFileAs('example-images', $request->file('image'), $key . '.' . $ext);

        return back()->with('success', 'Example image updated.');
    }

    // Check whether an example image exists for a given key.
    // Called from ProjectController::show() to pass existence flags to the view,
    // keeping storage logic out of Blade templates.
    public static function exists(string $key): bool
    {
        return self::resolvePath($key) !== null;
    }

    // Find the stored path for a given key by checking common extensions.
    public static function resolvePath(string $key): ?string
    {
        $disk = config('filesystems.default') === 's3' ? 's3' : 'local';

        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            $path = 'example-images/' . $key . '.' . $ext;
            if (Storage::disk($disk)->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    // Non-static alias used internally by show() and upload().
    private function findPath(string $key): ?string
    {
        return self::resolvePath($key);
    }
}
