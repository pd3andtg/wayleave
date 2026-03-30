<?php

namespace App\Services;

use App\Models\DocumentReference;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

// Handles all business logic for the Document References library.
// All authenticated users can view and download.
// Only admin can upload, edit, or delete.
class DocumentReferenceService
{
    // Returns a paginated, searchable list of document references.
    public function getList(array $filters): LengthAwarePaginator
    {
        $query = DocumentReference::with('uploadedBy')->latest();

        if (!empty($filters['search'])) {
            $search   = $filters['search'];
            $operator = config('database.default') === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $operator) {
                $q->where('title', $operator, "%{$search}%")
                  ->orWhere('description', $operator, "%{$search}%");
            });
        }

        return $query->paginate(20)->withQueryString();
    }

    // Store a new document reference and its uploaded file.
    public function store(array $data, User $user): DocumentReference
    {
        $file             = $data['file'];
        $originalFilename = $file->getClientOriginalName();
        $ext              = $file->getClientOriginalExtension() ?: 'bin';
        $storedName       = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $data['title']) . '.' . $ext;
        $filePath         = $file->storeAs('document-references', $storedName, config('filesystems.default'));

        return DocumentReference::create([
            'title'             => $data['title'],
            'description'       => $data['description'] ?? null,
            'file_path'         => $filePath,
            'original_filename' => $originalFilename,
            'uploaded_by'       => $user->id,
        ]);
    }

    // Update title/description and optionally replace the file.
    public function update(array $data, DocumentReference $reference, User $user): DocumentReference
    {
        $filePath         = $reference->file_path;
        $originalFilename = $reference->original_filename;

        if (isset($data['file'])) {
            // Delete old file before storing the replacement.
            Storage::disk(config('filesystems.default'))->delete($reference->file_path);

            $file             = $data['file'];
            $originalFilename = $file->getClientOriginalName();
            $ext              = $file->getClientOriginalExtension() ?: 'bin';
            $storedName       = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $data['title']) . '.' . $ext;
            $filePath         = $file->storeAs('document-references', $storedName, config('filesystems.default'));
        }

        $reference->update([
            'title'             => $data['title'],
            'description'       => $data['description'] ?? null,
            'file_path'         => $filePath,
            'original_filename' => $originalFilename,
        ]);

        return $reference;
    }

    // Delete the record and its stored file.
    public function delete(DocumentReference $reference): void
    {
        Storage::disk(config('filesystems.default'))->delete($reference->file_path);
        $reference->delete();
    }

    // Generate a temporary signed download URL (30 min), or a local file response path.
    public function getDownloadUrl(DocumentReference $reference): string
    {
        $disk = config('filesystems.default');

        if ($disk === 's3') {
            return Storage::disk('s3')->temporaryUrl($reference->file_path, now()->addMinutes(30));
        }

        return route('document-references.download', $reference);
    }
}
