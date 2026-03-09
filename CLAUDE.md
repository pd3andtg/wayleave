# Wayleave Permit Tracker System

## Project Overview
Laravel 12 prototype for a Wayleave Permit Tracking & Filing System.
To be presented to HQ as a blueprint for real deployment.
Three user roles: Admin, Officer (internal/TM Tech), Contractor (external).
This is a prototype — clean, readable, well-structured code matters most.
HQ will read this code to rebuild the real system.

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

The system follows a sequential multi-step process per project.
All steps are displayed on ONE scrollable project detail page.
Each section corresponds to one step in the flow below.

    Step 1:  Contractor registers new file/project
    Step 2:  Contractor uploads BQ/INV
    Step 3:  Officer verifies and endorses BQ/INV + payment info
    Step 4:  Contractor uploads received Wayleave from KUTT/PBT (up to 3 PBTs)
    Step 5:  Officer endorses Wayleave and handles payment per PBT
    Step 6:  Contractor submits doc permit to KUTT
    Step 7:  Contractor records permit received date and uploads permit
    Step 8:  Contractor uploads Notis Mula/Siap Kerja + combined site photos PDF
    Step 9:  Contractor uploads CPC application documents
    Step 10: Contractor uploads received CPC — status changes to Completed

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
- Belongs to one unit: ND TRG / ND KEL / ND PHG

### Contractor (External)
- Can ONLY see projects belonging to their own company
- Cannot view other companies' projects under any circumstance
- Can only register new projects (Step 1 — button visible to contractors only)
- Works on contractor-designated sections (Steps 2, 4, 6, 7, 8, 9, 10)
- Cannot edit officer-designated sections (Steps 3, 5)

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
          Currently: ND TRG / ND KEL / ND PHG — expandable by Admin
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
    name                (text — e.g. ND TRG, ND KEL, ND PHG)
    timestamps
    Note: Stored as a separate table so Admin can add new units
    in the future without any code changes.
    Initially seeded with: ND TRG, ND KEL, ND PHG

### companies
    id
    name
    status              (enum: pending, approved, rejected)
    requested_by        (fk → users — who requested the registration)
    approved_by         (fk → users — admin who approved, nullable)
    timestamps

### users
    id
    name
    email
    password
    id_number           (staff_id for officers / ic_no for contractors)
    unit_id             (fk → units, nullable — officers only,
                         null for contractors and admin)
    company_id          (fk → companies)
    timestamps
    + Spatie roles table (admin / officer / contractor)

### projects
    id
    ref_no              (text, nullable — single column for both KUTT Ref No
                         and PBT Ref No, labelled as KUTT Ref No/PBT Ref No)
    lor_no              (text, nullable)
    project_no          (text, nullable)
    project_desc        (text, required)
    nd_state            (enum: ND_TRG, ND_PHG, ND_KEL)
    remarks             (text, nullable)
    company_id          (fk → companies)
    created_by          (fk → users)
    status              (enum: outstanding, completed)
    timestamps

### bq_invs
    id
    project_id          (fk → projects)
    bq_inv_file         (text — file path, stored in Laravel Storage)
    endorsed_file       (text — file path, nullable)
    payment_status      (enum: waived, charged, nullable)
    uploaded_by         (fk → users)
    endorsed_by         (fk → users, nullable)
    timestamps

### inv_payments
    id
    project_id          (fk → projects)
    inv_number          (enum: INV1, INV2, INV3)
    eds_no              (text)
    date                (date)
    amount              (decimal 10,2)
    payment_status      (enum: paid, outstanding)
    timestamps

### wayleave_pbts
    id
    project_id              (fk → projects)
    pbt_number              (enum: PBT1, PBT2, PBT3)
    pbt_name                (enum: MBKT, MPK, MDS, MDB, MPD, JKR_HT,
                                   JKR_KN, JKR_DN, JKR_KT, JKR_KM,
                                   JKR_ST, Others)
    pbt_name_other          (text, nullable — required only if pbt_name = Others,
                             contractor writes the PBT name themselves)
    wayleave_file           (text — file path)
    wayleave_received_date  (date)
    endorsed_file           (text — file path, nullable)
    fi_payment              (enum: required, not_required, waived, nullable)
    fi_eds_no               (text, nullable)
    fi_date                 (date, nullable)
    deposit_payment         (enum: required, not_required, waived, nullable)
    deposit_eds_no          (text, nullable)
    deposit_payment_type    (enum: BG, BD, nullable)
    deposit_date            (date, nullable)
    endorsed_by             (fk → users, nullable)
    timestamps

### permit_submissions
    id
    project_id          (fk → projects)
    submit_date         (date)
    submission_file     (text — file path, doc permit with PBT cop received)
    submitted_by        (fk → users)
    timestamps

### permits_received
    id
    project_id          (fk → projects)
    permit_received_date (date)
    permit_file         (text — file path)
    uploaded_by         (fk → users)
    timestamps

### work_notices
    id
    project_id          (fk → projects)
    notis_mula_file     (text — file path)
    notis_siap_file     (text — file path)
    gambar_file         (text — file path, ONE combined PDF containing
                         gambar sebelum, semasa, dan selepas)
    uploaded_by         (fk → users)
    timestamps

### cpc_applications
    id
    project_id              (fk → projects)
    surat_serahan_file      (text — file path)
    laporan_bergambar_file  (text — file path)
    salinan_coa_file        (text — file path)
    salinan_permit_file     (text — file path)
    date_submit_to_kutt     (date)
    submitted_by            (fk → users)
    timestamps

### cpc_received
    id
    project_id          (fk → projects)
    cpc_file            (text — file path)
    uploaded_by         (fk → users)
    timestamps

---

## Project Status Flow

    outstanding → completed

Status changes to "completed" only when contractor uploads
the CPC in Step 10 (cpc_received record created).
All other steps keep status as "outstanding".

---

## Pages & Navigation

### Front Page — Project List
- Visible to all roles (admin, officer, contractor)
- Contractors only see their own company's projects (scoped by company_id)
- Officers and Admin see all projects from all companies
- Search by: Ref No (KUTT/PBT) or Project Description
- Filter by: Status (outstanding/completed), ND State
- "Register New Project" button visible to contractors only

### Project Detail Page
- Single scrollable page showing ALL 10 steps/sections
- All sections visible to everyone with access to the project
- Contractor sections: Steps 1, 2, 4, 6, 7, 8, 9, 10
- Officer sections: Steps 3, 5
- Contractors can only fill/edit contractor sections
- Officers and Admin can fill/edit all sections
- All files downloadable by anyone with project access
- Download via secure Laravel Storage signed URL

### Admin Exclusive Pages
- Company Registration Requests:
    View pending requests, approve or reject each company request
- User Management:
    View all registered users
    Change role for Officers only — Contractor roles cannot be changed
- Unit Management:
    Add new units to the units table (e.g. new ND regions in the future)
    No code changes needed — managed entirely through the admin UI

---

## File Handling Rules
- Accepted type: PDF only -- all uploads including site photos (gambar)
- Maximum file size: 10MB per file
- Local development: Laravel Storage local disk (FILESYSTEM_DISK=local)
- Production on Railway: Cloudflare R2 via S3-compatible Laravel Filesystem
  (FILESYSTEM_DISK=s3 -- no code changes needed, just .env swap)
- File path/key stored as text column in the respective database table
- Files are NOT publicly accessible -- served via Laravel temporaryUrl()
  which generates a signed URL valid for 30 minutes
- Validate mime type server-side in Form Request -- never trust browser input
- Site photos (gambar sebelum, semasa, selepas) must be combined into ONE
  single PDF by the contractor before uploading -- system accepts one PDF only
- All uploaded files must be downloadable from the project detail page
- All data including file paths/keys stored in PostgreSQL on Railway

### How File Upload and Download Works in Code (same for local and R2)
    // Upload
    $path = $request->file('bq_inv_file')
                    ->store('projects/' . $project->id . '/bq-inv', 's3');

    // Download -- signed URL, expires in 30 minutes
    $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30));

    // Switch between local and R2 just by changing .env
    // FILESYSTEM_DISK=local  -> saves to storage/app/
    // FILESYSTEM_DISK=s3     -> saves to Cloudflare R2

---

## Business Rules
- Contractors ALWAYS scoped by company_id at controller AND policy level
- New companies must be approved by admin before contractors can select them
- pbt_name_other is required only when pbt_name is set to Others
- Contractor writes their own PBT name when Others is selected
- Up to 3 PBTs allowed per project (wayleave_pbts, pbt_number PBT1–PBT3)
- Up to 3 INV payments allowed per project (inv_payments, INV1–INV3)
- Project status only changes to "completed" when CPC is uploaded (Step 10)
- Officers belong to exactly one unit: ND TRG / ND KEL / ND PHG
- Contractors identified by IC No, Officers identified by Staff ID
- Admin can change Officer roles only — Contractor roles are fixed
- All data including file paths stored in PostgreSQL

---

## Email Notifications
To be confirmed — placeholder for now.
Suggested triggers:
- New company registration request submitted → notify Admin
- Company registration approved or rejected → notify requesting user
- New project registered → notify Officers in the relevant ND State
- CPC uploaded (project completed) → notify relevant Officer

---

## Testing Accounts (Seeded)
- admin@wayleave.test            → Admin role
- officer_trg@wayleave.test      → Officer role, unit: ND TRG
- contractor_a@wayleave.test     → Contractor role, Company A
- contractor_b@wayleave.test     → Contractor role, Company B

---

## Important Reminders
- This is a prototype — understandable code matters more than complexity
- HQ reads this code to rebuild the real system — make it easy to follow
- Test all 3 roles after EVERY feature before moving to the next
- Do not batch test at the end — catch issues while context is fresh
- Keep controllers thin — if a method is long, move logic to a Service class
- UI approach to be added once design is finalised