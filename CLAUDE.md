# Wayleave Permit Tracker System

## Project Overview
Laravel 12 prototype for a Wayleave Permit Tracking & Filing System.
To be presented to HQ as a blueprint for real deployment.
Three user roles: Admin, Officer (internal/TM Tech), Contractor (external).
This is a prototype — clean, readable, well-structured code matters most.
HQ will read this code to rebuild the real system.

---

## CHANGELOG (Read This First)
This section documents all changes from the previous version.
Claude Code must review this before working on any existing feature.

### Latest Update
    [1] STEP COUNT: Increased from 10 to 12 steps.
        A new Step 3 (Project Timeline) was inserted,
        shifting all subsequent step numbers down by one.

    [2] STEP 3 — NEW: Project Timeline
        A visual read-only stage indicator showing current project progress.
        No new database table needed — reads from existing records.

    [3] STEP 4 (was Step 2) — BOQ/INV RECEIVED: SIGNIFICANT CHANGE
        OLD: Single file upload only.
        NEW: Up to 6 files. Each file has its own:
             document_info, payment_type (BQ/INV), date, amount, eds_no, remarks.
        DATABASE: bq_inv_files table restructured (see Database Tables).

    [4] STEP 5 (was Step 3) — TM BOQ/INVOICE ENDORSEMENT: SIGNIFICANT CHANGE
        OLD: Single endorsed file + waived/charged + up to 3 INVs with basic fields.
        NEW: Mirrors Step 4 files. For each BQ: document_info, date, remarks.
             For each INV: document_info, date, amount, eds_no, remarks,
             payment_status now has 3 options: paid/outstanding/waived (was paid/outstanding).
        DATABASE: bq_endorsements and inv_endorsements tables added (see Database Tables).

    [5] STEP 6 (was Step 4) — WAYLEAVE: CHANGE
        OLD: Officer endorsement was a separate Step 5.
        NEW: Officer endorsement is now EMBEDDED inside Step 6.
             Officer overwrites the contractor's wayleave file (one file column per PBT)
             and adds an endorsement remark directly on the same record.
        DATABASE: endorsed_file column REMOVED. wayleave_file is now shared.
                  endorsed_by and endorsement_remarks columns added to wayleave_pbts.

    [6] STEP 7 (was Step 5) — WAYLEAVE PAYMENT: SUBTLE FIELD RENAME
        OLD: fi_date, deposit_date
        NEW: fi_application_date, deposit_application_date

    [7] STEP 10 (was Step 8) — WORK NOTICES: CHANGE
        OLD: Notis Mula + Notis Siap + combined gambar PDF
        NEW: Notis Mula + Notis Siap ONLY.
             Gambar (site photos) section REMOVED ENTIRELY from the system.
        DATABASE: gambar_file column REMOVED from work_notices table.

    [8] STEP 12 (was Step 10) — CPC RECEIVED: NEW FIELD
        OLD: cpc_file only.
        NEW: cpc_file + cpc_date (date field added).

    [9] CONTRACTOR/OFFICER SECTION MAPPING: UPDATED
        Contractor sections: Steps 2, 4, 6, 8, 9, 10, 11, 12
        Officer sections:    Steps 5, 7
        Officer action embedded in Step 6 (wayleave file overwrite + remark)

    [10] STEP 1 — SEPARATE PAGE CLARIFICATION
        Step 1 (Register New Project) is on its own separate page, not on the
        project detail page. After submission, contractor is redirected to the
        newly created project detail page.
        Step 1 data (ref_no, lor_no, project_no, project_desc, nd_state, remarks)
        is shown as an EDITABLE form at the top of the project detail page
        so details can be corrected later by anyone with access.

---

## Tech Stack
- Laravel 12
- Blade + Alpine.js for minor interactivity
- PostgreSQL (Railway)
- Spatie Laravel Permission for roles and access control
- Cloudflare R2 for file storage (S3-compatible, via Laravel Filesystem)
- UI template to be confirmed — cherry pick components only
- Bootstrap 5 for layout utilities

## Deployment
- App hosting:    Railway (PHP/Laravel native support)
- Database:       Railway PostgreSQL (same platform, same project)
- File storage:   Cloudflare R2 (10GB free, S3-compatible)
- Local dev:      Laravel Storage local disk
                  (swap to R2 via .env for deployment — no code changes needed)

### Key Environment Variables
    APP_ENV=production
    DB_CONNECTION=pgsql
    DB_HOST=                        (Railway PostgreSQL host)
    DB_PORT=5432
    DB_DATABASE=                    (Railway PostgreSQL name)
    DB_USERNAME=                    (Railway PostgreSQL username)
    DB_PASSWORD=                    (Railway PostgreSQL password)

    FILESYSTEM_DISK=s3
    AWS_ACCESS_KEY_ID=              (Cloudflare R2 Access Key)
    AWS_SECRET_ACCESS_KEY=          (Cloudflare R2 Secret Key)
    AWS_DEFAULT_REGION=auto
    AWS_BUCKET=                     (Cloudflare R2 Bucket Name)
    AWS_ENDPOINT=                   (Cloudflare R2 endpoint URL)
    AWS_USE_PATH_STYLE_ENDPOINT=true

---

## Coding Standards

### Architecture
- Thin controllers — business logic in Service classes (App\Services)
- Form Request classes for ALL validation (never validate in controller)
- Laravel Policies for ALL authorisation (never check roles in controller)
- Eloquent relationships — no raw SQL unless absolutely necessary
- Keep it simple — no over-engineering for a prototype

### Naming Conventions
- Controllers:   singular PascalCase          (ProjectController)
- Models:        singular PascalCase          (Project, Company, User)
- Tables:        plural snake_case            (projects, companies, project_files)
- Variables:     camelCase, descriptive       ($companyProjects not $data or $d)
- Methods:       camelCase, verb-first        (storeProjectFile, getCompanyProjects)
- Blade views:   kebab-case in subfolders     (projects/project-detail.blade.php)
- Services:      singular + Service suffix    (ProjectService, CompanyService)
- Form Requests: action + model + Request     (StoreProjectRequest, UpdateBqInvRequest)
- Policies:      model + Policy               (ProjectPolicy, CompanyPolicy)

### Security Rules
- Always use Form Requests for validation — never validate in controller directly
- Always use Policies for authorisation — never check roles inside controller
- Always scope contractor queries by company_id — never trust user input
- File uploads must validate mime type (PDF only) and max 10MB per file
- Always use Eloquent or Query Builder — never raw SQL
- Always escape Blade output with {{ }} — never {!! !!} unless intentional

### Controller Pattern
Always follow this thin controller pattern:

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projectService->createProject(
            $request->validated(),
            auth()->user()
        );
        return redirect()->route('projects.show', $project)
                         ->with('success', 'Project registered successfully.');
    }

### Blade Standards
- Use @role and @can directives from Spatie — never raw if/else for auth checks
- Always escape output with {{ }} — never {!! !!} unless intentional
- No PHP business logic inside views — views only display data
- Use Blade components for repeated UI elements
- Use @foreach, @if, @isset — never mix raw PHP tags in Blade

### Comments
- Add a comment block at the top of every class explaining its purpose
- Comment any logic that is not immediately obvious
- Explain WHY not just WHAT
- Example:
    // Contractors are always scoped to their own company_id.
    // Officers and Admins bypass this filter to see all projects.

---

## System Flow Overview

The system follows a sequential 12-step process per project.
All steps are displayed on ONE scrollable project detail page.
Step 3 is a read-only visual timeline — no data entry.

    Step 1:  Contractor registers new file/project
    Step 2:  Contractor registers new project details
    Step 3:  PROJECT TIMELINE — read-only visual stage indicator (no DB table)
    Step 4:  Contractor uploads BOQ/INV files (up to 6 files, each with metadata)
    Step 5:  Officer endorses each BOQ/INV file and records BQ/INV payment status
    Step 6:  Contractor uploads Wayleave per PBT (up to 3 PBTs)
             Officer overwrites file and adds endorsement remark in same step
    Step 7:  Officer records FI and Deposit payment details per PBT
    Step 8:  Contractor submits doc permit application to KUTT
    Step 9:  Contractor records permit received date and uploads permit
    Step 10: Contractor uploads Notis Mula Kerja and Notis Siap Kerja
    Step 11: Contractor uploads CPC application documents
    Step 12: Contractor uploads received CPC + date — status changes to Completed

---

## User Roles & Access Rules

### Admin
- Can see ALL projects across ALL companies
- Can edit ANY section of any project
- Has exclusive access to Company Registration approval page
- Can view all registered users
- Can change roles for Officers only — Contractor roles cannot be changed
- Approves or rejects new Company ID registration requests
- Does NOT need to approve individual user sign-ups

### Officer (Internal — TM Tech staff)
- Can see ALL projects across ALL companies
- Can edit ANY section of any project (including contractor sections)
- Can change dates and update any field
- Cannot approve Company registrations (admin only)
- Belongs to one unit from the units table (expandable by Admin)

### Contractor (External)
- Can ONLY see projects belonging to their own company
- Cannot view other companies' projects under any circumstance
- Can only register new projects (Step 2 — button visible to contractors only)
- Works on contractor-designated sections: Steps 2, 4, 6, 8, 9, 10, 11, 12
- Cannot edit officer-designated sections: Steps 5, 7
- Can upload/overwrite wayleave file in Step 6 (officer also acts in Step 6)

### Editing Rules
- Anyone can edit any section anytime — no section is locked after saving
- Contractors restricted to their own company's projects only
- Officers and Admin have no restrictions on which project or section they edit

### File Download
- All users with access to a project can download any file from that project
- Files downloaded via secure Laravel Storage signed URLs
- Contractors can only download files from their own company's projects

---

## Authentication & Registration Flow

### Self Sign-Up
Users register themselves. No admin approval needed for individual users.

Registration fields:
- Name                  (text, required)
- Company Name          (dropdown):
    - TM Tech           → internal officer role assigned automatically
        - Choose Unit: dropdown from units table (required if TM Tech)
          Currently seeded: ND TRG / ND KEL / ND PHG — expandable by Admin
    - Existing approved companies → contractor role assigned automatically
    - "Request New Company" → triggers company registration request
- Staff ID              (text, required for TM Tech officers only)
- IC No                 (text, required for external contractors only)
- Password + confirmation

### New Company Registration Request
- If contractor's company not in dropdown:
    - Contractor submits a Company Registration Request
    - Admin receives and reviews the request
    - Admin approves → company appears in dropdown for future sign-ups
    - Admin rejects → contractor notified
    - Purpose: avoid duplicate company entries

### Role Assignment on Registration
- Selected TM Tech → Officer role assigned automatically
- Selected existing approved company → Contractor role assigned automatically
- Role applied immediately on sign-up — no admin approval needed for users

---

## Database Tables

### units
    id
    name                (text -- e.g. ND TRG, ND KEL, ND PHG)
    timestamps
    Note: Separate table so Admin can add new units via UI without code changes.
    Initially seeded with: ND TRG, ND KEL, ND PHG

### companies
    id
    name
    status              (enum: pending, approved, rejected)
    requested_by        (fk -> users -- who submitted the registration request)
    approved_by         (fk -> users -- admin who approved, nullable)
    timestamps

### users
    id
    name
    email
    password
    id_number           (staff_id for officers / ic_no for contractors)
    unit_id             (fk -> units, nullable -- officers only, null for contractors)
    company_id          (fk -> companies)
    timestamps
    + Spatie roles table (admin / officer / contractor)

### projects
    id
    ref_no              (text, nullable -- single column for both KUTT Ref No
                         and PBT Ref No, labelled KUTT Ref No/PBT Ref No in UI)
    lor_no              (text, nullable)
    project_no          (text, nullable)
    project_desc        (text, required)
    nd_state            (enum: ND_TRG, ND_PHG, ND_KEL)
    remarks             (text, nullable)
    company_id          (fk -> companies)
    created_by          (fk -> users)
    status              (enum: outstanding, completed)
    timestamps

### bq_inv_files
    -- CHANGED: replaces old bq_invs table
    -- Now supports up to 6 files per project, each with full metadata
    id
    project_id          (fk -> projects)
    file_number         (integer: 1-6 -- identifies which file slot)
    file_path           (text -- file path in storage)
    document_info       (text -- description of the document)
    payment_type        (enum: BQ, INV)
    date                (date)
    amount              (decimal 10,2)
    eds_no              (text)
    remarks             (text, nullable)
    uploaded_by         (fk -> users)
    timestamps

### bq_endorsements
    -- NEW: Officer endorsement details for BQ-type files in bq_inv_files
    id
    bq_inv_file_id      (fk -> bq_inv_files)
    project_id          (fk -> projects)
    document_info       (text)
    date                (date)
    remarks             (text, nullable)
    endorsed_by         (fk -> users)
    timestamps

### inv_endorsements
    -- NEW: Officer endorsement details for INV-type files in bq_inv_files
    id
    bq_inv_file_id      (fk -> bq_inv_files)
    project_id          (fk -> projects)
    document_info       (text)
    date                (date)
    amount              (decimal 10,2)
    payment_status      (enum: paid, outstanding, waived)
    eds_no              (text)
    remarks             (text, nullable)
    endorsed_by         (fk -> users)
    timestamps

### wayleave_pbts
    -- CHANGED: endorsed_file column removed
    -- Officer now overwrites wayleave_file directly and adds endorsement remark
    id
    project_id              (fk -> projects)
    pbt_number              (enum: PBT1, PBT2, PBT3)
    pbt_name                (enum: MBKT, MPK, MDS, MDB, MPD, JKR_HT,
                                   JKR_KN, JKR_DN, JKR_KT, JKR_KM,
                                   JKR_ST, Others)
    pbt_name_other          (text, nullable -- required only if pbt_name = Others)
    wayleave_file           (text -- file path, shared column:
                             contractor uploads first, officer overwrites with endorsed version)
    wayleave_received_date  (date)
    endorsement_remarks     (text, nullable -- officer adds "Endorsed" or other remark here)
    endorsed_by             (fk -> users, nullable -- set when officer overwrites file)
    timestamps

### wayleave_payments
    -- CHANGED: fi_date renamed to fi_application_date
    --          deposit_date renamed to deposit_application_date
    id
    project_id                  (fk -> projects)
    wayleave_pbt_id             (fk -> wayleave_pbts)
    fi_payment                  (enum: required, not_required, waived, nullable)
    fi_eds_no                   (text, nullable)
    fi_application_date         (date, nullable -- was fi_date)
    deposit_payment             (enum: required, not_required, waived, nullable)
    deposit_eds_no              (text, nullable)
    deposit_payment_type        (enum: BG, BD, nullable)
    deposit_application_date    (date, nullable -- was deposit_date)
    recorded_by                 (fk -> users)
    timestamps

### permit_submissions
    id
    project_id          (fk -> projects)
    submit_date         (date)
    submission_file     (text -- file path, doc permit with PBT cop received as evidence)
    submitted_by        (fk -> users)
    timestamps

### permits_received
    id
    project_id           (fk -> projects)
    permit_received_date (date)
    permit_file          (text -- file path)
    uploaded_by          (fk -> users)
    timestamps

### work_notices
    -- CHANGED: gambar_file column REMOVED entirely (gambar removed from system)
    id
    project_id          (fk -> projects)
    notis_mula_file     (text -- file path)
    notis_siap_file     (text -- file path)
    uploaded_by         (fk -> users)
    timestamps

### cpc_applications
    id
    project_id              (fk -> projects)
    surat_serahan_file      (text -- file path)
    laporan_bergambar_file  (text -- file path)
    salinan_coa_file        (text -- file path)
    salinan_permit_file     (text -- file path)
    date_submit_to_kutt     (date)
    submitted_by            (fk -> users)
    timestamps

### cpc_received
    -- CHANGED: cpc_date field added
    id
    project_id          (fk -> projects)
    cpc_file            (text -- file path)
    cpc_date            (date -- NEW: date CPC was received)
    uploaded_by         (fk -> users)
    timestamps

---

## Project Status Flow

    outstanding -> completed

Status changes to "completed" only when contractor uploads
the CPC in Step 12 (cpc_received record created).
All other steps keep status as "outstanding".

---

## Project Timeline (Step 3 — Read Only)

Step 3 is a visual indicator only — no database table.
It reads from existing records across all tables to determine
which stages are complete and renders a timeline/progress bar.

Stage is considered complete when:
    Stage 1  -- projects record exists
    Stage 2  -- projects record exists (same as Stage 1)
    Stage 3  -- (timeline display only)
    Stage 4  -- at least one bq_inv_files record exists for the project
    Stage 5  -- at least one bq_endorsements or inv_endorsements record exists
    Stage 6  -- at least one wayleave_pbts record exists
    Stage 7  -- at least one wayleave_payments record exists
    Stage 8  -- permit_submissions record exists
    Stage 9  -- permits_received record exists
    Stage 10 -- work_notices record exists
    Stage 11 -- cpc_applications record exists
    Stage 12 -- cpc_received record exists (project = completed)

---

## Pages & Navigation

### Register New Project Page (Step 1 -- Contractor Only)
- Separate standalone page, accessible via "Register New Project" button
- Visible to contractors only (button hidden from officers and admin)
- Fields: ref_no, lor_no, project_no, project_desc, nd_state, remarks
- On successful submit -> contractor redirected immediately to the
  newly created project detail page

### Front Page -- Project List
- Visible to all roles (admin, officer, contractor)
- Contractors only see their own company's projects (scoped by company_id)
- Officers and Admin see all projects from all companies
- Search by: Ref No (KUTT/PBT) or Project Description
- Filter by: Status (outstanding/completed), ND State
- "Register New Project" button visible to contractors only

### Project Detail Page (Steps 2 to 12)
- Single scrollable page starting from Step 2 (Step 1 is a separate page)
- Step 2: Editable form showing project info from Step 1 -- anyone can edit
- Step 3: Read-only visual timeline indicator
- All sections visible to everyone with access to the project
- Contractor sections: Steps 2, 4, 6, 8, 9, 10, 11, 12
- Officer sections: Steps 5, 7
- Officer also acts in Step 6 (overwrites wayleave file + adds endorsement remark)
- Contractors can only fill/edit their designated sections
- Officers and Admin can fill/edit all sections
- All files downloadable by anyone with project access
- Download via secure Laravel Storage temporaryUrl() signed for 30 minutes

### Admin Exclusive Pages
- Company Registration Requests:
    View pending requests, approve or reject each company request
- User Management:
    View all registered users
    Change role for Officers only -- Contractor roles cannot be changed
- Unit Management:
    Add new units to the units table (e.g. new ND regions in future)
    No code changes needed -- managed entirely through admin UI

---

## File Handling Rules
- Accepted type: PDF only -- all uploads
- Gambar (site photos) are NO LONGER part of the system (removed entirely)
- Maximum file size: 10MB per file
- Local development: Laravel Storage local disk (FILESYSTEM_DISK=local)
- Production on Railway: Cloudflare R2 via S3-compatible Laravel Filesystem
  (FILESYSTEM_DISK=s3 -- no code changes needed, just .env swap)
- File path stored as text column in respective database table
- Files are NOT publicly accessible -- served via Laravel temporaryUrl()
  which generates a signed URL valid for 30 minutes
- Validate mime type server-side in Form Request -- never trust browser input
- All uploaded files must be downloadable from the project detail page

### How File Upload and Download Works in Code (same for local and R2)
    // Upload
    $path = $request->file('wayleave_file')
                    ->store('projects/' . $project->id . '/wayleave', 's3');

    // Download -- signed URL expires in 30 minutes
    $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30));

    // Switch between local and R2 just by changing .env
    // FILESYSTEM_DISK=local  -> saves to storage/app/
    // FILESYSTEM_DISK=s3     -> saves to Cloudflare R2

---

## Business Rules
- Contractors ALWAYS scoped by company_id at controller AND policy level
- New companies must be approved by admin before contractors can select them
- pbt_name_other required only when pbt_name = Others (contractor writes name)
- Up to 3 PBTs per project (wayleave_pbts, pbt_number PBT1/PBT2/PBT3)
- Up to 6 BOQ/INV files per project (bq_inv_files, file_number 1-6)
- BQ endorsements go to bq_endorsements table
- INV endorsements go to inv_endorsements table (determined by payment_type)
- INV payment status has 3 options: paid / outstanding / waived
- Wayleave file is a single shared column -- officer overwrites contractor upload
- Officer sets endorsed_by and endorsement_remarks when overwriting wayleave file
- Gambar (site photos) removed entirely -- do not create any gambar fields
- Project status only changes to "completed" when CPC uploaded in Step 12
- Officers belong to one unit from units table (expandable by Admin)
- Contractors identified by IC No, Officers by Staff ID
- Admin can change Officer roles only -- Contractor roles are fixed
- fi_application_date and deposit_application_date (NOT fi_date / deposit_date)
- All file paths stored in PostgreSQL on Railway

---

## Email Notifications
To be confirmed -- placeholder for now.
Suggested triggers:
- New company registration request submitted -> notify Admin
- Company registration approved or rejected -> notify requesting user
- New project registered -> notify Officers in the relevant ND State
- CPC uploaded (project completed) -> notify relevant Officer

---

## Testing Accounts (Seeded)
- admin@wayleave.test            -> Admin role
- officer_trg@wayleave.test      -> Officer role, unit: ND TRG
- contractor_a@wayleave.test     -> Contractor role, Company A
- contractor_b@wayleave.test     -> Contractor role, Company B

---

## Important Reminders
- This is a prototype -- understandable code matters more than complexity
- HQ reads this code to rebuild the real system -- make it easy to follow
- Test all 3 roles after EVERY feature before moving to the next
- Do not batch test at the end -- catch issues while context is fresh
- Keep controllers thin -- if a method is long, move logic to a Service class
- UI approach to be added once design is finalised
- Always refer to CHANGELOG at top of this file before touching existing features