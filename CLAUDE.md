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

    [11] SECTION 1 (PROJECT INFORMATION) — MAJOR CHANGES
        - New fields added: pic_name (auto-filled), node_id (fk -> nodes),
          payment_to_kutt, application_status, cancellation_reason
        - New 'nodes' table added (like units — Admin manages via UI)
        - New 'TM' company record added to companies table (for self-applied projects)
        - self_applied_by_tm flag added to projects table
        - When self_applied_by_tm = true, company_id set to TM's company record
        - When payment_to_kutt = waived/not_required, Sections 2 & 3 show
          label only — existing data hidden but NOT deleted
        - When application_status = cancelled, all sections locked except
          Section 1 which shows cancellation reason
        - Admin can reopen a cancelled project (set status back to in_progress)
        - Anyone (contractor, officer, admin) can cancel a project

    [12] SECTIONS 2 & 3 (BOQ/INV) — RESTRUCTURED
        - Sections 2 and 3 now share the SAME database table (boq_inv_items)
        - Section 2 shows: document_info, type, date_received, amount, remarks,
          updated_by, date_updated (NO eds_no, NO payment_status, NO endorsed file)
        - Section 3 shows: ALL Section 2 columns PLUS eds_no, payment_status,
          and endorsed file upload button (officer/admin only)
        - payment_status options: Endorsed, Endorsed and Paid, Pending Endorsement,
          Waived, Cancelled (replaces old paid/outstanding/waived)
        - "Add New BOQ/INV Files" button lives in Section 3 but adds rows
          visible in BOTH sections simultaneously
        - Old bq_inv_files, bq_endorsements, inv_endorsements tables REPLACED
          by single boq_inv_items table

    [13] SECTION 3 — ENDORSED FILE PER ROW
        - Each BOQ/INV row in Section 3 has its own endorsed file upload button
        - Officer/admin uploads endorsed file per row
        - Officer overwrites the contractor's original file (one file column only)
        - file_path in boq_inv_items is shared -- officer upload replaces contractor upload
        - Only officer/admin can upload the endorsed file

    [14] SECTIONS 4 & 5 — WAYLEAVE SPLIT INTO TWO SECTIONS
        OLD: Officer endorsement embedded inside Section 5 (wayleave)
        NEW: Section 4 — WAYLEAVE RECEIVED (contractor uploads per PBT)
             Section 5 — TM WAYLEAVE ENDORSEMENT (officer file upload + signature only)
             Endorsed-by signature shown in BOTH sections
             Officer upload overwrites contractor's file (one shared file_path per PBT)
             No other fields in Section 5 besides file upload and endorsed-by

    [15] SECTION 6 — RENAMED + RESTRUCTURED TO TABLE FORMAT
        OLD: Box format per PBT, fi_date/deposit_date field names
        NEW: Table format (like Sections 2 & 3)
             Renamed to: TM: WAYLEAVE PAYMENT DETAILS (FI & DEPOSIT)
             Two rows per PBT in the table:
             Row 1 — FI Payment: status, amount, eds_no, method_of_payment, fi_application_date
             Row 2 — Deposit: status, amount, eds_no, method_of_payment, deposit_application_date
             method_of_payment is NEW field replacing old deposit_payment_type (BG/BD only)
             New options: BG, BD_DAP, EFT_DAP (applies to both FI and Deposit rows)
             wayleave_payments table restructured (see Database Tables)

    [16] SECTION 7 — NEW SECTION: TM: BG & BD RECEIVED FROM FINSSO
        NEW section inserted between old Section 6 and Permit Submission
        Mirrors Section 6 table but only shows rows where payment status = required
        Same wayleave_payments table — extra columns added (no new table)
        Additional columns: received_posted_date, bg_bd_file_path
        Only officer/admin can fill received_posted_date and upload bg_bd document
        Per PBT: up to 2 rows (FI row if required, Deposit row if required)
        All sections from Permit Submission onwards shift up by one number

    [17] SECTION RENUMBERING (due to wayleave split + new Section 7)
        OLD -> NEW
        Section 7  (Permit Submission)    -> Section 8
        Section 8  (Permit Received)      -> Section 9
        Section 9  (Notis Mula/Siap)      -> Section 10 & 11 (split)
        Section 10 (CPC Application)      -> Section 12
        Section 11 (Terima CPC)           -> Section 13
        Total sections: 13

    [18] SECTIONS 10 & 11 — NOTIS MULA/SIAP SPLIT INTO TWO SECTIONS
        OLD: Single section (work_notices) with both notis_mula_file and notis_siap_file
        NEW: Section 10 — Notis Mula Kerja (notis_mula_file only)
             Section 11 — Notis Siap Kerja (notis_siap_file only)
             Each has its own completion indicator on the timeline
             Still same work_notices table — just displayed as two separate sections

    [19] SECTIONS 12 & 13 — NAME CHANGES ONLY
        Section 12: renamed to PERMOHONAN SIJIL PERAKUAN SIAP KERJA (CPC)
                    same fields and behaviour as before
        Section 13: renamed to SIJIL PERAKUAN SIAP KERJA
                    same behaviour — CPC upload sets project status to Completed

    [20] PROJECT TIMELINE — LOGIC UPDATES
        Section 3 completion: ALL rows must be Endorsed and Paid OR Waived
                              (not just any status, not just one row)
        Sections 2 & 3: if payment_to_kutt = waived/not_required,
                        auto-marked as completed (waived) on timeline
        Sections 10 & 11: each has its own separate completion indicator
        Total timeline indicators updated to 13
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
- Works on contractor-designated sections: Sections 2, 4, 8, 9, 10, 11, 12, 13
        Note: Section 9 contractor can also upload (shared with officer)
- Cannot edit officer-designated sections: Sections 3, 5, 6, 7
- Contractor uploads wayleave file in Section 4
- Officer endorses in Section 5 (separate section)

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

### nodes
    id
    acronym             (text -- short name, e.g. KT, KBR, TRG)
    full_name           (text -- full node name)
    timestamps
    Note: Separate table so Admin can add new nodes via UI without code changes.
    Searchable by acronym or full name in the project form (typeahead/search input)
    Initially seeded with existing TM nodes

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
    pic_name            (text -- auto-filled from auth()->user()->name on creation,
                         stored as text so it remains even if user is deleted)
    nd_state            (enum: ND_TRG, ND_PHG, ND_KEL)
    node_id             (fk -> nodes, nullable -- TM Node, searchable by acronym/full name)
    self_applied_by_tm  (boolean, default false -- only settable by officer/admin,
                         if true company_id is set to TM's company record)
    payment_to_kutt     (enum: charged, waived, not_required)
    application_status  (enum: in_progress, cancelled, default: in_progress)
    cancellation_reason (text, nullable -- required when application_status = cancelled)
    remarks             (text, nullable)
    company_id          (fk -> companies -- set to TM company if self_applied_by_tm)
    created_by          (fk -> users)
    status              (enum: outstanding, completed)
    timestamps

### boq_inv_items
    -- Shared table for BOTH Section 2 (BOQ/INV Files) and
    -- Section 3 (TM BOQ/Invoice Endorsement).
    --
    -- Section 2 shows: document_info, type, date_received, amount,
    --                  remarks, updated_by, updated_at
    --                  (contractor fills, no file column visible here)
    --
    -- Section 3 shows: ALL Section 2 columns PLUS:
    --                  file_path, eds_no, payment_status
    --                  + endorsed file upload button per row (officer/admin only)
    --
    -- File behaviour: contractor uploads file when adding a new row.
    --                 Officer can overwrite that file via upload button in Section 3.
    --                 One shared file_path column -- officer upload replaces original.
    --
    -- "Add New BOQ/INV" button in Section 3 adds rows visible in both sections.
    -- Old bq_inv_files, bq_endorsements, inv_endorsements tables are REMOVED.
    id
    project_id          (fk -> projects)
    document_info       (text)
    type                (enum: BQ, INV)
    date_received       (date)
    amount              (decimal 10,2, nullable)
    file_path           (text, nullable -- shared column:
                         contractor uploads first via Section 2 add row,
                         officer/admin can overwrite via Section 3 upload button)
    eds_no              (text, nullable -- Section 3 only, officer/admin fills)
    payment_status      (enum: endorsed, endorsed_and_paid, pending_endorsement,
                               waived, cancelled, nullable -- Section 3 only)
    endorsed_by         (fk -> users, nullable -- set when officer uploads endorsed file)
    remarks             (text, nullable -- hint: BOQ/INV Number)
    updated_by          (fk -> users, nullable -- tracks who last updated this row)
    timestamps          (updated_at serves as date_updated in Section 2 display)

### wayleave_pbts
    -- Section 4: Contractor uploads wayleave file per PBT
    -- Section 5: Officer overwrites file + endorsed_by set (file upload + signature only)
    -- endorsed_by shown in BOTH Section 4 and Section 5
    id
    project_id              (fk -> projects)
    pbt_number              (enum: PBT1, PBT2, PBT3)
    pbt_name                (enum: MBKT, MPK, MDS, MDB, MPD, JKR_HT,
                                   JKR_KN, JKR_DN, JKR_KT, JKR_KM,
                                   JKR_ST, Others)
    pbt_name_other          (text, nullable -- required only if pbt_name = Others)
    wayleave_file           (text, nullable -- shared file_path:
                             contractor uploads first in Section 4,
                             officer overwrites with endorsed version in Section 5)
    wayleave_received_date  (date, nullable)
    endorsed_by             (fk -> users, nullable -- set when officer uploads in Section 5,
                             displayed in both Section 4 and Section 5)
    timestamps

### wayleave_payments
    -- Shared table for BOTH Section 6 and Section 7.
    -- Section 6 (TM: Wayleave Payment Details) shows:
    --   payment_type, status, amount, eds_no, method_of_payment, application_date
    --   Two rows per PBT: one FI row, one Deposit row
    -- Section 7 (TM: BG & BD Received from FINSSO) shows:
    --   Same columns as Section 6 PLUS received_posted_date and bg_bd_file_path
    --   Only shows rows where status = required
    --   received_posted_date and bg_bd_file_path filled by officer/admin only
    --
    -- method_of_payment replaces old deposit_payment_type (BG/BD only)
    -- Now applies to BOTH FI and Deposit rows with 3 options
    id
    project_id              (fk -> projects)
    wayleave_pbt_id         (fk -> wayleave_pbts)
    payment_type            (enum: FI, Deposit -- one row per payment type per PBT)
    status                  (enum: required, not_required, waived, nullable)
    amount                  (decimal 10,2, nullable)
    eds_no                  (text, nullable)
    method_of_payment       (enum: BG, BD_DAP, EFT_DAP, nullable
                             BG = Bank Guarantee
                             BD_DAP = Bank Draft DAP
                             EFT_DAP = Electronic Fund Transfer DAP)
    application_date        (date, nullable -- FI or Deposit application date)
    received_posted_date    (date, nullable -- Section 7 only, officer/admin fills)
    bg_bd_file_path         (text, nullable -- Section 7 only, officer/admin uploads)
    recorded_by             (fk -> users)
    timestamps

### permit_submissions
    id
    project_id          (fk -> projects)
    submit_date         (date)
    submission_file     (text -- file path, doc permit with PBT cop received as evidence)
    submitted_by        (fk -> users)
    timestamps

### permits_received
    -- Section 9: labelled TM but both contractor and officer can upload
    id
    project_id           (fk -> projects)
    permit_received_date (date)
    permit_file          (text -- file path)
    uploaded_by          (fk -> users)
    timestamps

### work_notices
    -- CHANGED: gambar_file removed entirely (gambar removed from system)
    -- Displayed as TWO separate sections in UI:
    --   Section 10 shows notis_mula_file only
    --   Section 11 shows notis_siap_file only
    -- Each has its own completion indicator on the timeline
    -- Still one table -- just rendered as two sections
    id
    project_id          (fk -> projects)
    notis_mula_file     (text, nullable -- Section 10)
    notis_siap_file     (text, nullable -- Section 11)
    uploaded_by         (fk -> users)
    timestamps

### cpc_applications
    -- Section 12: Permohonan Sijil Perakuan Siap Kerja (CPC)
    -- Name changed from old "Permohonan CPC" -- same fields and behaviour
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
    -- Section 13: Sijil Perakuan Siap Kerja
    -- Name changed from old "Terima CPC" -- same behaviour
    -- Uploading CPC file sets project.status to "completed"
    id
    project_id          (fk -> projects)
    cpc_file            (text -- file path)
    cpc_date            (date)
    uploaded_by         (fk -> users)
    timestamps

---

## Project Status Flow

    outstanding -> completed
    in_progress -> cancelled (anyone can cancel, compulsory reason)
    cancelled -> in_progress (admin can reopen)

project.status (outstanding/completed):
    Changes to "completed" only when CPC uploaded in Section 13.

project.application_status (in_progress/cancelled):
    Independent of project.status.
    Cancelled locks all sections except Section 1.
    Admin can reopen by setting back to in_progress.

---

## Project Timeline (Displayed After Section 1 -- Read Only, Not Numbered)

The timeline is a visual indicator only -- no database table.
Displayed directly after Section 1 (Project Information) on the project detail page.
Not assigned a section number.

Stage completion rules:
    Section 1  -- projects record exists
    Section 2  -- at least one boq_inv_items record exists
                  IF payment_to_kutt = waived/not_required -> auto marked as waived (complete)
    Section 3  -- ALL boq_inv_items rows have payment_status = endorsed_and_paid OR waived
                  (not complete if even one row is pending/endorsed only/cancelled)
                  IF payment_to_kutt = waived/not_required -> auto marked as waived (complete)
    Section 4  -- at least one wayleave_pbts record exists
    Section 5  -- at least one wayleave_pbts row has endorsed_by set
    Section 6  -- at least one wayleave_payments record exists
    Section 7  -- all required wayleave_payments rows have received_posted_date set
                  (not_required and waived rows skipped -- not counted as incomplete)
    Section 8  -- permit_submissions record exists
    Section 9  -- permits_received record exists
    Section 10 -- work_notices.notis_mula_file is not null
    Section 11 -- work_notices.notis_siap_file is not null
    Section 12 -- cpc_applications record exists
    Section 13 -- cpc_received record exists (project.status = completed)

---

## Pages & Navigation

### Register New Project Page (Step 1)
- Separate standalone page, accessible via "Register New Project" button
- All roles can access this page (contractor, officer, admin)
- Contractors: company_id auto-set to their own company, no company dropdown shown
- Officers/Admin: shown a SELF APPLIED BY TM option (Yes/No)
    If Yes -> company_id set to TM's company record, no company dropdown
    If No  -> must choose which company this project belongs to from dropdown
              project will appear in that company's contractor project list
              (contractor with matching company_id will see it on their dashboard)
- Fields: ref_no, lor_no, project_no, project_desc, nd_state, remarks
- On successful submit -> redirected immediately to the newly created
  project detail page

### Front Page -- Project List
- Visible to all roles (admin, officer, contractor)
- Contractors only see their own company's projects (scoped by company_id)
- Officers and Admin see all projects from all companies
- Search by: Ref No (KUTT/PBT) or Project Description
- Filter by: Status (outstanding/completed), ND State
- "Register New Project" button visible to contractors only

### Project Detail Page (Sections 1 to 12)
- Single scrollable page starting from Section 1
- Section 1:  Editable project info form -- anyone with access can edit
              If payment_to_kutt = waived/not_required -> Sections 2 & 3 show label only
              If application_status = cancelled -> all sections locked, reason shown
- Section 2:  BOQ/INV Files table (contractor fills -- no eds_no, no payment_status)
- Section 3:  TM BOQ/Invoice Endorsement (same rows + eds_no + payment_status)
              Each row has endorsed file upload button (officer/admin only)
              Officer upload overwrites contractor's file (one shared file_path)
              "Add New BOQ/INV" button adds rows visible in both Sections 2 & 3
- Section 4:  Wayleave Received -- contractor uploads file per PBT (up to 3)
              Shows endorsed_by signature when officer has endorsed
- Section 5:  TM Wayleave Endorsement -- officer uploads endorsed file per PBT
              File overwrites contractor original (shared file_path in wayleave_pbts)
              Only file upload + endorsed_by (no other fields)
              endorsed_by shown in both Section 4 and Section 5
- Section 6:  TM: Wayleave Payment Details (FI & Deposit) -- table format
              Two rows per PBT (FI row + Deposit row)
              Columns: payment_type, status, amount, eds_no,
                       method_of_payment (BG/BD_DAP/EFT_DAP), application_date
- Section 7:  TM: BG & BD Received from FINSSO
              Mirrors Section 6 -- only rows where status = required
              Additional columns: received_posted_date, bg_bd file upload
              Officer/admin fills received_posted_date and uploads document
              Same wayleave_payments table as Section 6 (extra columns)
- Section 8:  Doc Permit Application Submission to KUTT (contractor)
- Section 9:  Permit Received (contractor)
- Section 10: Notis Mula Kerja -- upload Notis Mula Kerja only (contractor)
              Own completion indicator on timeline
- Section 11: Notis Siap Kerja -- upload Notis Siap Kerja only (contractor)
              Own completion indicator on timeline
- Section 12: Permohonan Sijil Perakuan Siap Kerja (CPC) (contractor)
- Section 13: Sijil Perakuan Siap Kerja -- CPC upload sets status to Completed
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
- Node Management:
    Add new nodes to the nodes table (acronym + full name)
    No code changes needed -- managed entirely through admin UI
- Reopen Cancelled Projects:
    Admin can set application_status back to in_progress for cancelled projects

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
- TM is a special company record in companies table (for self-applied projects)
- self_applied_by_tm can only be set by officer or admin, not contractor
- if officer/admin creates project and self_applied_by_tm = No, they must select
  a company from dropdown -- project appears in that company's contractor list
- contractors always have company_id auto-set from their own profile (no dropdown)
- When self_applied_by_tm = true, company_id is set to TM's company_id
- pic_name is auto-filled from auth()->user()->name when project is created
- node_id references nodes table -- searchable by acronym or full name in UI
- payment_to_kutt = waived or not_required -> Sections 2 & 3 are locked/hidden
  but existing BOQ/INV data is NOT deleted -- just hidden behind the label
- application_status = cancelled -> ALL sections locked except Section 1
  cancellation_reason is compulsory when cancelling
- Anyone (contractor, officer, admin) can cancel a project
- Admin can reopen a cancelled project (set application_status back to in_progress)
- Sections 2 & 3 share the same boq_inv_items table -- one row per BOQ/INV item
  Section 2 shows subset of columns, Section 3 shows all columns
- eds_no and payment_status in boq_inv_items are filled by officer/admin only
- file_path in boq_inv_items is a shared column:
  contractor uploads when adding a new row (via Section 2/3 add button)
  officer/admin can overwrite per row via endorsed file upload in Section 3
  one file column only -- officer upload replaces contractor original
- endorsed_by is set in boq_inv_items when officer uploads the endorsed file
- only officer/admin can upload the endorsed file in Section 3
- payment_status options: endorsed, endorsed_and_paid, pending_endorsement,
  waived, cancelled
- "Add New BOQ/INV" button in Section 3 creates rows visible in both sections
- pbt_name_other required only when pbt_name = Others (contractor writes name)
- Up to 3 PBTs per project (wayleave_pbts, pbt_number PBT1/PBT2/PBT3)
- Wayleave file is a single shared column -- officer overwrites contractor upload
- Officer sets endorsed_by and endorsement_remarks when overwriting wayleave file
- Gambar (site photos) removed entirely -- do not create any gambar fields
- Project status only changes to "completed" when CPC uploaded in Section 11
- Officers belong to one unit from units table (expandable by Admin)
- Nodes stored in nodes table -- searchable by acronym or full name (Admin manages)
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

## Testing Method
- Chrome       -> contractor_a (Company A -- sees only Company A projects)
- Edge         -> contractor_b (Company B -- sees only Company B projects)
- Firefox      -> officer_trg  (sees ALL projects, all sections editable)
- InPrivate    -> admin        (sees ALL projects + exclusive admin pages)

---

## Important Reminders
- This is a prototype -- understandable code matters more than complexity
- HQ reads this code to rebuild the real system -- make it easy to follow
- Test all 3 roles after EVERY feature before moving to the next
- Do not batch test at the end -- catch issues while context is fresh
- Keep controllers thin -- if a method is long, move logic to a Service class
- UI approach to be added once design is finalised
- Always refer to CHANGELOG at top of this file before touching existing features