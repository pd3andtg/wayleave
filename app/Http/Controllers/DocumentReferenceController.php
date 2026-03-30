<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentReferenceRequest;
use App\Http\Requests\UpdateDocumentReferenceRequest;
use App\Models\DocumentReference;
use App\Services\DocumentReferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

// Handles the Document References library.
// All authenticated users can view and download.
// Only admin can upload, edit, or delete — enforced via DocumentReferencePolicy.
class DocumentReferenceController extends Controller
{
    public function __construct(private DocumentReferenceService $referenceService) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', DocumentReference::class);

        $references = $this->referenceService->getList(
            $request->only('search')
        );

        return view('document-references.index', compact('references'));
    }

    public function store(StoreDocumentReferenceRequest $request)
    {
        $this->referenceService->store($request->validated(), auth()->user());

        return redirect()->route('document-references.index')
                         ->with('success', 'Document reference uploaded successfully.');
    }

    public function update(UpdateDocumentReferenceRequest $request, DocumentReference $documentReference)
    {
        $this->referenceService->update($request->validated(), $documentReference, auth()->user());

        return redirect()->route('document-references.index')
                         ->with('success', 'Document reference updated successfully.');
    }

    public function destroy(DocumentReference $documentReference)
    {
        $this->authorize('delete', $documentReference);

        $this->referenceService->delete($documentReference);

        return redirect()->route('document-references.index')
                         ->with('success', 'Document reference deleted.');
    }

    // Serves the file inline for local disk, or redirects to a signed R2 URL.
    public function download(DocumentReference $documentReference)
    {
        $this->authorize('viewAny', DocumentReference::class);

        $disk = config('filesystems.default');

        if ($disk === 's3') {
            return redirect(
                Storage::disk('s3')->temporaryUrl($documentReference->file_path, now()->addMinutes(30))
            );
        }

        $fullPath = Storage::disk('local')->path($documentReference->file_path);

        abort_if(!file_exists($fullPath), 404);

        return response()->download($fullPath, $documentReference->original_filename);
    }
}
