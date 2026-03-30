@extends('layouts.dashboard')

@section('title', 'Document References')

@section('content')

<div class="row">
  <div class="col-12" style="padding: 2rem;">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="card-title mb-0" style="font-weight:600;">Document References</h2>
      @role('admin')
        <a href="#" class="btn btn-primary btn-sm"
           data-bs-toggle="modal" data-bs-target="#uploadModal">
          + Upload New Document
        </a>
      @endrole
    </div>

    {{-- Search --}}
    <form method="GET" action="{{ route('document-references.index') }}" id="search-form">
      <div class="row g-2 mb-4">
        <div class="col">
          <input type="text" autocomplete="off"
                 name="search"
                 class="form-control"
                 placeholder="Search by title or description"
                 value="{{ request('search') }}"
                 style="height:52px;">
        </div>
      </div>
    </form>

    {{-- Flash messages --}}
    @if(session('success'))
      <div class="alert alert-success py-2">{{ session('success') }}</div>
    @endif

    {{-- Results count + Show All --}}
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="text-muted small">
        @if($references->total() > 0)
          Showing {{ $references->firstItem() }} to {{ $references->lastItem() }} of {{ $references->total() }} documents
        @else
          No documents found.
        @endif
      </div>
      @if(request('search'))
        <a href="{{ route('document-references.index') }}" class="text-muted small">Show All</a>
      @endif
    </div>

    {{-- Table --}}
    <div class="table-responsive">
      <table class="table table-hover" style="font-size:0.875rem;">
        <thead>
          <tr>
            <th style="font-weight:600; color:#07326A;">File</th>
            <th style="font-weight:600; color:#07326A;">Description</th>
            @role('admin')
              <th style="font-weight:600; color:#07326A;">Actions</th>
            @endrole
          </tr>
        </thead>
        <tbody>
          @forelse($references as $ref)
            <tr>
              <td>
                <a href="{{ route('document-references.download', $ref) }}"
                   style="color:#07326A; font-weight:500; text-decoration:none;"
                   onmouseover="this.style.textDecoration='underline'"
                   onmouseout="this.style.textDecoration='none'">
                  {{ $ref->title }}
                </a>
              </td>
              <td class="text-muted">{{ $ref->description ?? '—' }}</td>
              @role('admin')
                <td>
                  <button type="button" class="btn-action btn-action-sm"
                          data-bs-toggle="modal"
                          data-bs-target="#editModal{{ $ref->id }}">
                    Edit
                  </button>
                  <form action="{{ route('document-references.destroy', $ref) }}" method="POST"
                        class="d-inline ms-1"
                        onsubmit="return confirm('Delete this document? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                  </form>
                </td>
              @endrole
            </tr>

            {{-- Edit modal for this row (admin only) --}}
            @role('admin')
            <div class="modal fade" id="editModal{{ $ref->id }}" tabindex="-1">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <form action="{{ route('document-references.update', $ref) }}" method="POST"
                        enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="modal-header">
                      <h5 class="modal-title">Edit Document Reference</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                      <div class="mb-3">
                        <label class="form-label small">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title', $ref->title) }}" required>
                      </div>
                      <div class="mb-3">
                        <label class="form-label small">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $ref->description) }}</textarea>
                      </div>
                      <div class="mb-3">
                        <label class="form-label small">Replace File <span class="text-muted">(leave blank to keep current)</span></label>
                        <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
                          <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                            Choose File
                            <input type="file" name="file" class="d-none"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                   @change="fn = $event.target.files[0]?.name ?? ''">
                          </label>
                          <span class="text-muted small" x-text="fn || '{{ $ref->original_filename }}'"
                                style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px; font-weight:normal;"></span>
                        </div>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn-action">Save</button>
                      <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                              data-bs-dismiss="modal">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            @endrole

          @empty
            <tr>
              <td colspan="{{ auth()->user()->hasRole('admin') ? 3 : 2 }}" class="text-center text-muted py-4">
                No documents uploaded yet.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
      {{ $references->links() }}
    </div>

  </div>
</div>

{{-- Upload modal (admin only) --}}
@role('admin')
<div class="modal fade" id="uploadModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="{{ route('document-references.store') }}" method="POST"
            enctype="multipart/form-data">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Upload New Document Reference</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control"
                   value="{{ old('title') }}" required>
          </div>
          <div class="mb-3">
            <label class="form-label small">Description</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
          </div>
          <div class="mb-3">
            <label class="form-label small">File <span class="text-danger">*</span></label>
            <div x-data="{ fn: '' }" class="d-flex align-items-center gap-2">
              <label class="btn-action mb-0" style="cursor:pointer; white-space:nowrap;">
                Choose File
                <input type="file" name="file" class="d-none"
                       accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                       required @change="fn = $event.target.files[0]?.name ?? ''">
              </label>
              <span class="text-muted small" x-text="fn || 'No file chosen'"
                    style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px; font-weight:normal;"></span>
            </div>
            <div class="text-muted" style="font-size:0.75rem; margin-top:4px;">
              Accepted: PDF, DOC, DOCX, JPG, PNG — max 10MB
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn-action">Upload</button>
          <button type="button" class="btn-action" style="background:#6c757d; border-color:#6c757d;"
                  data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endrole

@endsection
