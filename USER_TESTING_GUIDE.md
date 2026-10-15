# Barangay Disaster Assets Management & Monitoring System (BDES)
## Comprehensive User-Side Role Testing Manual & Operational Guide

---

## 1. System Overview & Testing Scope

**BDES (Barangay Disaster Assets Management & Monitoring System)** is a specialized, production-ready enterprise asset management and emergency logistics platform designed for local government units (LGUs).

The system enforces **Role-Based Access Control (RBAC)** across 6 distinct user tiers:

| Role Name | Database Role Identifier | Access Tier | Primary Responsibility |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `administrator` | Full Admin | System configuration, user management, financial valuations, audit inspection, master reporting |
| **Barangay Captain** | `barangay_captain` | Executive Admin | Executive oversight, asset retirement approvals, high-level reporting, executive dashboard |
| **BDRRMC Officer Member** | `bdrrmc_officer` | Operational Editor | Field deployments, return inspections, preventive maintenance, incident monitoring, asset dossiers |
| **Inventory & Logistics Officer** | `inventory_officer` | Operational Editor | Asset acquisition, stock counts, accessory tracking, consumables/operational requirements |
| **Maintenance Specialist** | `maintenance_officer` | Operational Editor | Work order execution, equipment servicing, preventive schedule completion, inspection ratings |
| **Barangay Staff Member** | `viewer` | Read-Only / Reporter | Asset registry lookup, defect report creation, personal profile management |

---

## 2. Test Credentials & Environment Setup

All accounts are pre-seeded in the database via `database/seeders/UserSeeder.php`.

> **Universal Test Password:** `password` (for all seeded test accounts)

### Seeded Test Accounts

```
+------------------------------------+--------------------------+-----------------------+
| Full Name                          | Email Address            | Role Identifier       |
+------------------------------------+--------------------------+-----------------------+
| System Administrator               | admin@bdes.gov           | administrator         |
| Hon. Barangay Captain              | captain@bdes.gov         | barangay_captain      |
| BDRRMC Officer Member              | member@bdes.gov          | bdrrmc_officer        |
| Inventory & Logistics Officer      | inventory@bdes.gov       | inventory_officer     |
| Maintenance Specialist             | maintenance@bdes.gov     | maintenance_officer   |
| Barangay Staff Member              | staff@bdes.gov           | viewer                |
+------------------------------------+--------------------------+-----------------------+
```

### Pre-Requisite Commands

To start the local environment with a clean, fully-seeded database:

```bash
# 1. Run migrations and database seeders
php artisan migrate:fresh --seed

# 2. Build frontend assets (Vite)
npm run build

# 3. Start the Laravel development server
php artisan serve
```

The system will be accessible in your web browser at: **`http://127.0.0.1:8000`** (or `http://localhost:8000`).

---

## 3. Step-by-Step Testing Guide: Role 1 — System Administrator

**Login:** `admin@bdes.gov` | **Password:** `password`

### 3.1. Dashboard & Resizable Navigation
1. Log in and arrive at `/dashboard`.
2. Notice the enterprise KPI counters: Total Assets, Available, Deployed, In Maintenance, Out of Service, and Active Emergency Deployments.
3. Observe the left sidebar navigation:
   - Notice the drag handle on the right edge of the sidebar.
   - Click and drag the handle horizontally to test custom sidebar width (200px to 420px).
   - Refresh the page and confirm the custom sidebar width is preserved in `localStorage`.
4. Verify the Admin sidebar displays:
   - **Dashboard**
   - **Asset Management** (Asset Registry, Asset Accessories, Operational Requirements, Borrowed / Return)
   - **Asset Locations** (Dedicated standalone navigation)
   - **Operations** (Maintenance & Inspection Hub, Monitoring / Reported Assets)
   - **Tracking** (Asset History & Audit Trail)
   - **Financials & Valuation** (Depreciation Tracking, Salvage Value Records)
   - **Reports Center**
   - **Administration** (User Management, Roles & Permissions)

### 3.2. User Management & Security Controls
1. Click **Administration -> User Management** (`/users`).
2. Click **Add New User**:
   - Enter Name: `Officer Juan Dela Cruz`, Username: `jdelacruz`, Email: `jdelacruz@bdes.gov`, Role: `bdrrmc_officer`, Password: `password`, Confirm: `password`.
   - Submit and verify the success notification.
3. Test **Account Disable/Enable**:
   - Locate any non-admin user and click **Disable Account**.
   - Verify the status badge updates to `Inactive`.
   - Click **Enable Account** to restore status.
4. Test **Password Reset**:
   - Click **Reset Password** on any user row.
   - Confirm the dialog and observe the 12-character secure temporary password generated.

### 3.3. Financials & Depreciation Tracking
1. Navigate to **Financials & Valuation -> Depreciation Tracking** (`/financials/depreciation`).
2. Review the live calculations:
   - Total Acquisition Cost
   - Accumulated Depreciation (Straight-Line method based on purchase date and useful life)
   - Current Book Value
   - Annual Depreciation Expense
3. Click **Edit Parameters** on an asset:
   - Update useful life to `7` years.
   - Enter Salvage Value: `5000`.
   - Click Save and observe real-time recalculation of book value.
4. Click **Export CSV** and verify the downloaded file `bdes_depreciation_schedule_*.csv`.

### 3.4. Salvage Value Records
1. Navigate to **Financials & Valuation -> Salvage Value Records** (`/financials/salvage`).
2. Verify the 10% statutory default salvage value applied to assets without manual appraisal.
3. Click **Update Salvage** on an asset:
   - Set custom salvage value: `12500.00`.
   - Add notes: `Appraised after 3 years field duty.`
   - Save and verify status badge switches from `10% Default` to `Custom Appraised`.
4. Click **Export CSV** and verify downloaded file.

### 3.5. Reports Center
1. Navigate to **Reports Center** (`/reports`).
2. Select report type: **Comprehensive Master Asset Inventory**.
3. Apply filters: Status = `Available`.
4. Click **Generate Report Preview** to inspect table data.
5. Click **Download CSV Export** to generate official report.

---

## 4. Step-by-Step Testing Guide: Role 2 — Hon. Barangay Captain

**Login:** `captain@bdes.gov` | **Password:** `password`

### 4.1. Executive Oversight
1. Log in and inspect the Executive Dashboard.
2. Note the visual clarity and high-level summaries for disaster preparedness readiness.
3. Verify access to **Financials & Valuation** and **Reports Center** (Captains have full executive auditing access).

### 4.2. Asset Retirement / Decommissioning Authorization
1. Navigate to **Asset Management -> Asset Registry** (`/assets`).
2. Click on an asset to view its 360-degree dossier (e.g., an Out-of-Service chainsaw or generator).
3. Observe the **Retire / Decommission Asset** button (only Captain and Administrator have this authority).
4. Click **Retire Asset**:
   - Provide retirement justification: `Damaged beyond economic repair during Typhoon Openg.`
   - Confirm decommissioning.
5. Verify the asset status updates to `retired` and an audit trail log is permanently recorded with the Captain's identity.

---

## 5. Step-by-Step Testing Guide: Role 3 — BDRRMC Officer Member

**Login:** `member@bdes.gov` | **Password:** `password`

### 5.1. Member Navigation & Clean Interface
1. Log in and inspect the Member sidebar:
   - Notice that restricted Admin modules (**Administration**, **User Management**, **Depreciation Tracking**, **Salvage Value Records**, **Reports Center**) are **completely removed**.
   - Notice the direct link to **Account Settings** in place of Administration.
   - Notice **Asset Locations** is cleanly separated.

### 5.2. Field Asset Deployment (Borrowing)
1. Navigate to **Asset Management -> Borrowed / Return** (`/deployment`).
2. Click **Deploy Asset**:
   - Select an available asset (e.g., `Heavy Chainsaw`).
   - Destination: `Purok 4 Riverside Evacuation Center`.
   - Assigned Team: `Rapid Rescue Unit Alpha`.
   - Responsible Custodian: `Officer Dela Cruz`.
   - Deployment Date: Today's date.
   - Expected Return Date: Tomorrow's date.
   - Mission Purpose: `Emergency clearing operations along floodway.`
3. Submit the form.
4. Verify:
   - Deployment status shows `active` in the listing.
   - The asset's status automatically transitions from `available` to `deployed`.
   - Audit trail records the deployment.

### 5.3. Return Processing & Check-In Inspection
1. From `/deployment`, locate the active deployment created above.
2. Click **Process Return**:
   - Return Date: Today's date.
   - Condition on Return: Select `Good`.
   - Received By: Automatically pre-filled with logged-in user.
   - Actual Location: `Purok Central Storage`.
   - Status: `received`.
3. Submit the return.
4. Verify:
   - Deployment status updates to `returned`.
   - Asset status automatically returns to `available`.
   - Return log is saved.

### 5.4. Asset Document Dossier
1. Navigate to **Asset Registry -> View Asset Dossier** for any asset.
2. Scroll to the **Documents & Manuals** tab.
3. Click **Upload Document**:
   - Title: `Chainsaw Safety & Operating Manual`.
   - Type: `manual`.
   - File: Attach a PDF or PNG file (up to 10MB).
   - Notes: `Official operation guide from manufacturer.`
4. Submit and verify document is listed.
5. Click **Download** to verify secure retrieval.

### 5.5. Security Enforcement (HTTP 403 Forbidden Verification)
1. While logged in as `member@bdes.gov`, attempt to access restricted URLs directly in browser address bar:
   - `http://127.0.0.1:8000/users` -> **HTTP 403 Forbidden** (BDES Branded Forbidden page).
   - `http://127.0.0.1:8000/financials/depreciation` -> **HTTP 403 Forbidden**.
   - `http://127.0.0.1:8000/financials/salvage` -> **HTTP 403 Forbidden**.
   - `http://127.0.0.1:8000/reports` -> **HTTP 403 Forbidden**.
2. Confirm the user cannot bypass permissions via direct URL traversal.

---

## 6. Step-by-Step Testing Guide: Role 4 — Inventory & Logistics Officer

**Login:** `inventory@bdes.gov` | **Password:** `password`

### 6.1. Registering New Equipment
1. Navigate to **Asset Management -> Asset Registry** (`/assets`).
2. Click **Add New Asset**:
   - Asset Name: `Honda 3-Inch Trash Pump`.
   - Category: `Flood Response Equipment`.
   - Type: `Water Pump`.
   - Serial Number: `SN-HPUMP-9921`.
   - Manufacturer: `Honda Power`.
   - Location: `Purok Central Storage`.
   - Condition: `excellent`.
   - Status: `available`.
   - Acquisition Cost: `₱ 45,000.00` (test typing commas and currency signs — normalized automatically).
3. Submit and verify asset code is auto-generated (e.g., `AST-XXXXXXXX`).

### 6.2. Accessories & Components
1. Navigate to **Asset Management -> Asset Accessories** (`/attachments?tab=accessories`).
2. Locate or add an accessory (e.g., `Suction Hose 20ft`).
3. Click **Adjust Stock**:
   - Action: `Add Stock` -> Amount: `5`.
   - Submit and verify quantity increases.
   - Action: `Consume/Reduce` -> Amount: `2`.
   - Submit and verify quantity decreases.
   - Test over-reduction: Try to subtract `999` and verify error prevents negative stock.

### 6.3. Consumables & Operational Requirements
1. Navigate to **Asset Management -> Operational Requirements** (`/operational-requirements`).
2. Inspect requirements for fuel, two-stroke oil, generator oil, etc.
3. Test **Refill**:
   - Click **Refill** on Diesel Fuel -> Add `20` Liters.
   - Verify current level increases up to maximum capacity.
4. Test **Consume**:
   - Click **Consume** -> Deduct `5` Liters.
   - Verify level updates. If level drops below minimum threshold, verify `Low Stock` badge triggers.

---

## 7. Step-by-Step Testing Guide: Role 5 — Maintenance Specialist

**Login:** `maintenance@bdes.gov` | **Password:** `password`

### 7.1. Preventive Maintenance Schedules
1. Navigate to **Operations -> Maintenance & Inspection Hub** (`/maintenance?tab=schedules`).
2. Review due and upcoming maintenance tasks.
3. Locate a schedule marked `Due` or `Scheduled`.
4. Click **Complete Task**:
   - Completion Date: Today's date.
   - Technician: Auto-populated or enter `Technician Santos`.
   - Notes: `Replaced spark plug, cleaned fuel filter, verified throttle response.`
5. Submit and verify schedule next due date rolls forward according to interval (e.g. 30 days).

### 7.2. Creating & Completing Work Orders
1. Click the **Work Orders & Logs** tab (`/maintenance?tab=logs`).
2. Click **Create Work Order**:
   - Asset: Select an asset.
   - Type: `repair`.
   - Priority: `high`.
   - Status: `in_progress`.
   - Scheduled Date: Today.
   - Notes: `Carburetor overhaul required.`
3. Submit and verify asset status automatically reflects `under_maintenance`.
4. Click **Mark Completed**:
   - Cost: `₱ 1,850.00` (auto-cleaned to `1850.00`).
   - Parts Used: `Carburetor rebuild kit, gasket, spark plug`.
   - Notes: `Repairs successfully completed and engine load-tested.`
5. Submit and verify asset automatically returns to `available` status.

### 7.3. Physical Equipment Inspections
1. Click the **Inspections** tab (`/maintenance?tab=inspections`).
2. Click **Record Inspection**:
   - Asset: Select equipment.
   - Inspection Date: Today.
   - Rating/Result: `passed`.
   - Inspector: Defaults to authenticated user.
   - Next Due Date: 30 days from today.
   - Findings: `All structural elements intact. Emergency cutoff operational.`
3. Submit and verify record is stored in audit dossier.

---

## 8. Step-by-Step Testing Guide: Role 6 — Barangay Staff Member

**Login:** `staff@bdes.gov` | **Password:** `password`

### 8.1. Read-Only Exploration & Defect Reporting
1. Log in as `staff@bdes.gov`.
2. Navigate to **Asset Registry** (`/assets`).
3. Notice that edit, delete, and add buttons are hidden or disabled.
4. Search for an asset by code or keyword.

### 8.2. Submitting an Asset Deficiency / Incident Report
1. Navigate to **Operations -> Monitoring / Reported Assets** (`/reported-assets`).
2. Click **Submit Asset Report**:
   - Asset Name: `Water Rescue Life Vest Set B`.
   - Category: `Water Rescue`.
   - Location: `Purok Riverside Storage`.
   - Report Type: `damaged`.
   - Description: `Two buckles cracked on life vest #4 during yesterday drill.`
3. Submit the report.
4. Verify:
   - Report is listed with status `pending`.
   - Reported By is automatically assigned to the staff member's user account.
   - Staff member cannot change status to `resolved` (only authorized officers can).

### 8.3. Self-Service Account Settings
1. Click **Account Settings** in sidebar or top right user menu (`/profile`).
2. Update Profile Name or Email.
3. Test security barrier:
   - Verify no role selection field exists in the form.
   - Member cannot promote their account to Administrator.

---

## 9. Comprehensive Cross-Role Workflow Scenarios

### Workflow A: End-to-End Asset Lifecycle
```
[Inventory Officer] Creates Asset (AST-GEN-01) -> Available
       |
[BDRRMC Officer] Deploys Asset to Evacuation Center -> Deployed
       |
[Staff Member] Reports Damaged Pull Cord on Asset -> Pending Report
       |
[Maintenance Specialist] Opens Work Order -> Under Maintenance
       |
[Maintenance Specialist] Completes Repairs -> Available
       |
[Barangay Captain] Years later, reviews salvage valuation -> Approves Decommission / Retire
```

---

## 10. Security & Input Resilience Checklist

| Verification Item | Expected System Behavior | Result |
| :--- | :--- | :---: |
| **Direct URL IDOR / Route Tampering** | Navigating to `/users`, `/financials/*`, `/reports` as non-admin returns HTTP 403 Forbidden | PASS |
| **Upper-casing Asset Codes** | Inputs like `ast-pmp01` are auto-capitalized to `AST-PMP01` | PASS |
| **Currency & Number Formatting** | Inputs like `₱ 12,500.00` or `12,500` are stripped of commas/symbols and saved as `12500.00` | PASS |
| **Date Logical Bounds** | Expected return date cannot precede deployment date; Inspection next due date cannot precede inspection date | PASS |
| **Stock & Capacity Limits** | Operational requirement current level cannot exceed capacity; Consuming cannot drop below zero | PASS |
| **Dangerous File Uploads** | Attempting to upload `.php`, `.exe`, `.html`, `.sh` as asset document is rejected by strict MIME/extension validator | PASS |
| **Safe Document Downloads** | Downloaded filenames are sanitized against directory traversal; user must have view permission | PASS |
| **Rate Limiting on Heavy Exports** | Export routes are rate-limited (`30 requests/minute`) to prevent denial-of-service | PASS |

---

## 11. Automated Test Suite Verification

Run the full automated test suite anytime using:

```bash
php artisan test
```

All **164+ tests** across Feature and Unit directories will execute and pass, validating RBAC policies, lifecycle state transitions, financial calculations, audit trail logging, and UI permissions.

