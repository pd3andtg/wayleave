@extends('layouts.dashboard')

@section('title', 'Node Management')

@section('content')
<div class="row">
  <div class="col-lg-8">

    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4 class="fw-bold mb-0">Node Management</h4>
    </div>

    @if (session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @php
      $stateOptions = [
        'TERENGGANU', 'PAHANG', 'KELANTAN', 'SELANGOR', 'JOHOR', 'KEDAH',
        'MELAKA', 'NEGERI SEMBILAN', 'PERAK', 'PERLIS', 'PULAU PINANG',
        'SABAH', 'SARAWAK', 'KUALA LUMPUR', 'PUTRAJAYA', 'LABUAN',
      ];
    @endphp

    {{-- Add new node form --}}
    <div class="card mb-4" x-data="{ showBulk: false }">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="fw-semibold mb-0">Add New Node</h5>
        <button type="button" class="btn-action btn-action-sm" x-on:click="showBulk = !showBulk"
                x-text="showBulk ? 'Single Add' : '+ Add Multiple Nodes'"></button>
      </div>
      <div class="card-body">

        {{-- Single add --}}
        <div x-show="!showBulk">
          <form action="{{ route('admin.nodes.store') }}" method="POST">
            @csrf
            <div class="row g-3 mb-3">
              <div class="col-md-2">
                <label class="form-label">Acronym <span class="text-danger">*</span></label>
                <input type="text" autocomplete="off" name="acronym"
                       class="form-control @error('acronym') is-invalid @enderror"
                       value="{{ old('acronym') }}" placeholder="e.g. AJH" required>
                @error('acronym') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" autocomplete="off" name="full_name"
                       class="form-control @error('full_name') is-invalid @enderror"
                       value="{{ old('full_name') }}" placeholder="e.g. AIR JERNIH" required>
                @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-2">
                <label class="form-label">ND</label>
                <select name="nd" class="form-control @error('nd') is-invalid @enderror" style="height:calc(1.5em + 0.75rem + 2px);">
                  <option value="">— Select —</option>
                  @foreach($ndOptions as $val => $label)
                    <option value="{{ $val }}" {{ old('nd') === $val ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
                @error('nd') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
              <div class="col-md-4">
                <label class="form-label">State</label>
                <select name="state" class="form-control @error('state') is-invalid @enderror" style="height:calc(1.5em + 0.75rem + 2px);">
                  <option value="">— Select —</option>
                  @foreach($stateOptions as $s)
                    <option value="{{ $s }}" {{ old('state') === $s ? 'selected' : '' }}>{{ $s }}</option>
                  @endforeach
                </select>
                @error('state') <div class="invalid-feedback">{{ $message }}</div> @enderror
              </div>
            </div>
            <div class="text-center">
              <button type="submit" class="btn-action" style="font-weight:400; text-transform:none; color:#ffffff;">Add Node</button>
            </div>
          </form>
        </div>

        {{-- Bulk add — ND and State chosen once, applied to all rows --}}
        <div x-show="showBulk" x-cloak
             x-data="{
               nd: '', state: '',
               rows: [{ acronym: '', full_name: '' }],
               addRow() { this.rows.push({ acronym: '', full_name: '' }); },
               removeRow(i) { if (this.rows.length > 1) this.rows.splice(i, 1); }
             }">
          <form action="{{ route('admin.nodes.storeBulk') }}" method="POST">
            @csrf
            {{-- Shared ND and State --}}
            <div class="row g-3 mb-3">
              <div class="col-md-3">
                <label class="form-label">ND <span class="text-danger">*</span></label>
                <select name="nd" x-model="nd" class="form-control">
                  <option value="">— Select —</option>
                  @foreach($ndOptions as $val => $label)
                    <option value="{{ $val }}">{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-4">
                <label class="form-label">State <span class="text-danger">*</span></label>
                <select name="state" x-model="state" class="form-control">
                  <option value="">— Select —</option>
                  @foreach($stateOptions as $s)
                    <option value="{{ $s }}">{{ $s }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            {{-- Hidden fields to pass nd/state per row --}}
            <div class="table-responsive">
              <table class="table table-bordered table-sm align-middle mb-3">
                <thead class="table-light">
                  <tr>
                    <th style="color:#07326A;">#</th>
                    <th style="color:#07326A;">Acronym <span class="text-danger">*</span></th>
                    <th style="color:#07326A;">Full Name <span class="text-danger">*</span></th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="(row, i) in rows" :key="i">
                    <tr>
                      <td x-text="i + 1" class="text-muted" style="width:2.5rem;"></td>
                      <td style="width:9rem;">
                        <input type="text" autocomplete="off" :name="`nodes[${i}][acronym]`" x-model="row.acronym"
                               class="form-control form-control-sm" placeholder="e.g. AJH" required>
                        {{-- Pass shared nd/state per row as hidden inputs --}}
                        <input type="hidden" :name="`nodes[${i}][nd]`" :value="nd">
                        <input type="hidden" :name="`nodes[${i}][state]`" :value="state">
                      </td>
                      <td>
                        <input type="text" autocomplete="off" :name="`nodes[${i}][full_name]`" x-model="row.full_name"
                               class="form-control form-control-sm" placeholder="e.g. AIR JERNIH" required>
                      </td>
                      <td style="width:2.5rem;">
                        <button type="button" class="btn-action btn-action-red btn-action-sm"
                                x-on:click="removeRow(i)" x-show="rows.length > 1">&times;</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <button type="button" class="btn-action btn-action-sm" x-on:click="addRow()"
                      style="font-weight:400; text-transform:none; color:#ffffff;">+ Add Row</button>
              <button type="submit" class="btn-action"
                      style="font-weight:400; text-transform:none; color:#ffffff;">Save All Nodes</button>
            </div>
          </form>
        </div>

      </div>
    </div>

    {{-- Existing nodes list --}}
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="fw-semibold mb-0">Existing Nodes</h5>
        <form method="GET" action="{{ route('admin.nodes.index') }}">
          <div class="d-flex align-items-center gap-2">
            <input type="text" autocomplete="off" name="search" class="form-control form-control-sm" placeholder="Search acronym, name, ND or state…" value="{{ $search ?? '' }}" style="min-width:220px;">
            @if($search)
              <a href="{{ route('admin.nodes.index') }}" class="btn-action btn-action-sm" style="font-weight:400; text-transform:none; color:#ffffff; white-space:nowrap;">Clear</a>
            @endif
          </div>
        </form>
      </div>
      <div class="card-body p-0">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              <th style="padding-left: 1.25rem; font-weight: 600; color: #07326A;">#</th>
              <th style="font-weight: 600; color: #07326A;">Acronym</th>
              <th style="font-weight: 600; color: #07326A;">Full Name</th>
              <th style="font-weight: 600; color: #07326A;">ND</th>
              <th style="font-weight: 600; color: #07326A;">State</th>
              <th style="font-weight: 600; color: #07326A;">Action</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($nodes as $node)
              <tr x-data="{ editing: false }">
                <td style="padding-left: 1.25rem;">{{ $loop->iteration }}</td>
                <td class="fw-semibold">
                  <span x-show="!editing">{{ $node->acronym }}</span>
                  <span x-show="editing" x-cloak>
                    <form id="node-edit-{{ $node->id }}" action="{{ route('admin.nodes.update', $node) }}" method="POST">
                      @csrf @method('PATCH')
                      <input type="text" autocomplete="off" name="acronym" value="{{ $node->acronym }}" class="form-control form-control-sm d-inline" style="width:5rem;" required>
                    </form>
                  </span>
                </td>
                <td>
                  <span x-show="!editing">{{ $node->full_name }}</span>
                  <span x-show="editing" x-cloak>
                    <input type="text" autocomplete="off" name="full_name" form="node-edit-{{ $node->id }}" value="{{ $node->full_name }}" class="form-control form-control-sm d-inline" style="width:12rem;" required>
                  </span>
                </td>
                <td>
                  <span x-show="!editing">{{ $node->nd ?? '—' }}</span>
                  <span x-show="editing" x-cloak>
                    <select name="nd" form="node-edit-{{ $node->id }}" class="form-control form-control-sm d-inline" style="width:9rem;">
                      <option value="">— None —</option>
                      @foreach($ndOptions as $val => $label)
                        <option value="{{ $val }}" {{ $node->nd === $val ? 'selected' : '' }}>{{ $label }}</option>
                      @endforeach
                    </select>
                  </span>
                </td>
                <td>
                  <span x-show="!editing">{{ $node->state ?? '—' }}</span>
                  <span x-show="editing" x-cloak>
                    <select name="state" form="node-edit-{{ $node->id }}" class="form-control form-control-sm d-inline" style="width:11rem;">
                      <option value="">— None —</option>
                      @foreach($stateOptions as $s)
                        <option value="{{ $s }}" {{ $node->state === $s ? 'selected' : '' }}>{{ $s }}</option>
                      @endforeach
                    </select>
                  </span>
                </td>
                <td>
                  <span x-show="!editing">
                    <button type="button" class="btn-action btn-action-sm" x-on:click="editing = true">Edit</button>
                    <form action="{{ route('admin.nodes.destroy', $node) }}" method="POST" class="d-inline ms-1"
                          onsubmit="return confirm('Delete node {{ $node->acronym }}? Projects referencing it will have node cleared.')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn-action btn-action-red btn-action-sm">Delete</button>
                    </form>
                  </span>
                  <span x-show="editing" x-cloak>
                    <button type="submit" form="node-edit-{{ $node->id }}" class="btn-action btn-action-sm">Save</button>
                    <button type="button" class="btn-action btn-action-sm ms-1" x-on:click="editing = false">Cancel</button>
                  </span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted py-4">No nodes found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection
