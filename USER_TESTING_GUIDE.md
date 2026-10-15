# Barangay Disaster Assets Management & Monitoring System (BDES)
## Comprehensive User-Side Role Testing Manual & Operational Guide

---

## 1. System Overview & Testing Scope

**BDES (Barangay Disaster Assets Management & Monitoring System)** is a specialized, production-ready enterprise asset management and emergency logistics platform designed for local government units (LGUs).

The system enforces **Role-Based Access Control (RBAC)** across 5 distinct user roles:

| Role Name | Database Identifier | Access Tier | Primary Operational Responsibility |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `administrator` | Full Superadmin | System configuration, user management, financial valuations, audit logs, master reporting |
| **Hon. Barangay Captain** | `barangay_captain` | Executive Admin | Executive oversight, asset retirement approvals, high-level reporting, executive dashboard |
| **BDRRMC Inventory Officer** | `inventory_officer` | Operational Equipment Officer | All equipment works **aside from maintenance**: acquisitions, asset registry, accessories, operational requirements/supplies, field deployments & returns, incident reporting |
| **Maintenance Officer** | `maintenance_officer` | Operational Upkeep Officer | All equipment servicing & maintenance works: work orders, preventive maintenance schedules, physical inspections, condition rating |
| **Barangay Staff / Viewer** | `viewer` | Read-Only / Reporter | Asset registry lookup, defect report creation, personal profile management |

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
| BDRRMC Inventory Officer           | inventory@bdes.gov       | inventory_officer     |
| BDRRMC Operational Officer         | member@bdes.gov          | inventory_officer     |
| Maintenance Specialist Officer     | maintenance@bdes.gov     | maintenance_officer   |
| Barangay Staff Member              | staff@bdes.gov           | viewer                |
+------------------------------------+--------------------------+-----------------------+
```

### Pre-Requisite Commands

To start the local environment with a clean, fully-seeded database:

```bash
# 1. Run database migrations and seed default accounts
php artisan migrate --seed

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
   - **Asset Management** (Asset Registry, Attachments & Accessories, Operational Requirements, Asset Needs & Supplies)
   - **Asset Operations** (Deployments & Returns, Maintenance & Work Orders, Reported Assets)
   - **Asset Financials** (Valuation & Budgeting, Depreciation Tracking, Salvage Value Records)
   - **Tracking & Reporting** (Asset History & Audit, Asset Locations, Reports Center)
   - **Administration** (User Management, Roles & Permissions, Account Settings)

### 3.2. User Management & Security Controls
1. Click **Administration -> User Management** (`/users`).
2. Click **Add User**:
   - Enter Name: `Officer Juan Dela Cruz`, Username: `jdelacruz`, Email: `jdelacruz@bdes.gov`, Role: `Inventory Officer`, Password: `password`, Confirm: `password`.
   - Submit and verify the success notification.
3. Test **Account Disable/Enable**:
   - Locate any non-admin user and click **Disable Account**.
   - Verify the status badge updates to `Inactive`.
   - Click **Enable Account** to restore status.
4. Test **Password Reset**:
   - Click **Reset Password** on any user row.
   - Confirm the dialog and observe the 12-character secure temporary password generated.

### 3.3. Financials & Philippine Standard Depreciation Tracking
1. Navigate to **Asset Financials -> Depreciation Tracking** (`/financials/depreciation`).
2. Notice the **Philippine Government Accounting Standard Notice**:
   > Items with acquisition costs ≥ ₱50,000.00 are recognized as Capitalized PPE (COA Circular No. 2022-004) with Straight-Line Amortization and a 10% standard residual value (COA Circular No. 2003-007). Items below ₱50,000.00 are Semi-Expendable Property and statutorily non-depreciable.
3. Observe the table entries:
   - Capitalized assets display the green `PPE` badge with active straight-line annual/monthly depreciation.
   - Items under ₱50,000 display the amber `Semi-Expendable` badge, `₱0.00` depreciation, and `Carried at Cost`.
4. Click **Edit Parameters** on a Capitalized PPE asset:
   - Update useful life to `7` years.
   - Enter Custom Salvage Value: `15000`.
   - Save and verify instant recalculation.
5. Notice that for Semi-Expendable assets, the action is locked with `Non-Depreciable`.
6. Click **Export CSV** and verify the downloaded file `bdes_depreciation_schedule_*.csv` includes the `COA Classification` column.

### 3.4. Salvage Value Records
1. Navigate to **Asset Financials -> Salvage Value Records** (`/financials/salvage`).
2. Verify the 10% statutory default salvage value applied to capitalized assets.
3. Click **Update Record** on a capitalized asset:
   - Set custom salvage value: `12500.00`.
   - Add notes: `Appraised salvage value for rescue equipment motor.`
   - Save and verify status badge switches from `COA 10% Standard` to `Custom Appraised`.
4. Click **Export CSV** and verify downloaded file includes `COA Classification`.

### 3.5. Reports Center
1. Navigate to **Tracking & Reporting -> Reports Center** (`/reports`).
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
3. Verify access to **Asset Financials** and **Reports Center** (Captains have full executive auditing access).

### 4.2. Asset Retirement / Decommissioning Authorization
1. Navigate to **Asset Management -> Asset Registry** (`/assets`).
2. Click on an asset to view its 360-degree dossier.
3. Observe the **Retire / Decommission Asset** button (only Captain and Administrator have this authority).
4. Click **Retire Asset**:
   - Provide retirement justification: `Damaged beyond economic repair during typhoon response.`
   - Confirm decommissioning.
5. Verify the asset status updates to `retired` and an audit trail log is permanently recorded with the Captain's identity.

---

## 5. Step-by-Step Testing Guide: Role 3 — BDRRMC Inventory Officer

**Login:** `inventory@bdes.gov` | **Password:** `password`

*(Handles all equipment-related workflows aside from maintenance)*

### 5.1. Equipment Acquisition & Philippine Standard Auto-Computation
1. Navigate to **Asset Management -> Asset Registry** (`/assets`).
2. Click **Add Asset**:
   - **Test Case 1 (Semi-Expendable Property < ₱50k)**:
     - Name: `Handheld Megaphone Siren Set`.
     - Category: `Rescue Hand Tools & Supplies`.
     - Location: `Purok Central Storage`.
     - Condition: `good`.
     - Status: `available`.
     - Acquisition Cost: `₱ 18,500.00`.
     - Submit and verify:
       - Asset is created with auto-generated code `AST-XXXXXXXX`.
       - Salvage value is automatically set to `₱0.00`.
       - Notes record: `Semi-Expendable Property (< ₱50,000.00 statutory threshold under COA Circular No. 2022-004 / GAM for LGUs). Non-depreciable.`
   - **Test Case 2 (Capitalized PPE ≥ ₱50k)**:
     - Name: `Flood Rescue Evacuation Boat`.
     - Category: `Transportation & Emergency Vehicles`.
     - Location: `Purok Central Storage`.
     - Condition: `excellent`.
     - Status: `available`.
     - Acquisition Cost: `₱ 150,000.00`.
     - Leave salvage value and useful life empty.
     - Submit and verify:
       - System automatically assigns standard useful life (**7 years** for Transportation).
       - System automatically computes **10% salvage value** (`₱ 15,000.00`).
       - Straight-line depreciation begins automatically amortizing.

### 5.2. Field Asset Deployment & Return Processing
1. Navigate to **Asset Operations -> Deployments & Returns** (`/deployment`).
2. Click **Deploy Asset**:
   - Select an available asset (e.g., `Flood Rescue Evacuation Boat`).
   - Destination: `Purok 4 Riverside Evacuation Center`.
   - Assigned Team: `Rapid Water Rescue Unit Alpha`.
   - Responsible Custodian: `Officer Juan Dela Cruz`.
   - Deployment Date: Today's date.
   - Expected Return Date: Tomorrow's date.
   - Mission Purpose: `Emergency evacuation drill along riverbank.`
3. Submit and verify asset status automatically transitions to `deployed`.
4. Locate the active deployment and click **Process Return**:
   - Condition on Return: `Good`.
   - Actual Location: `Purok Central Storage`.
   - Submit return and verify asset status automatically returns to `available`.

### 5.3. Managing Accessories, Consumables & Operational Requirements
1. Navigate to **Asset Management -> Attachments & Accessories** (`/attachments`).
   - Adjust accessory stock quantities up/down.
2. Navigate to **Asset Management -> Operational Requirements** (`/operational-requirements`).
   - Refill fuel/oil levels and verify capacity limits are enforced.

### 5.4. Security Boundary Verification (No Maintenance Authority)
1. While logged in as `inventory@bdes.gov`, observe that **Maintenance & Work Orders** is hidden from your primary operational workflows.
2. Attempt to create a maintenance work order via POST:
   - The backend `MaintenancePolicy` rejects the request with **HTTP 403 Forbidden**.
3. Attempt to record an inspection:
   - `InspectionController` rejects the request with **HTTP 403 Forbidden**.
4. Attempt to access `/users`, `/financials/depreciation`, `/reports`:
   - All return **HTTP 403 Forbidden**.

---

## 6. Step-by-Step Testing Guide: Role 4 — Maintenance Officer

**Login:** `maintenance@bdes.gov` | **Password:** `password`

*(Responsible for equipment upkeep, repairs, preventive maintenance schedules, and physical inspections)*

### 6.1. Maintenance Hub & Work Orders
1. Log in and notice the tailored sidebar:
   - **Asset Registry** is accessible for viewing equipment specs and condition.
   - **Maintenance & Work Orders** is prominently featured.
   - Deployments & Returns, Accessories, and Consumables are omitted to keep your workflow focused on equipment servicing.
2. Navigate to **Asset Operations -> Maintenance & Work Orders** (`/maintenance?tab=logs`).
3. Click **Create Work Order**:
   - Asset: Select an asset needing repair.
   - Type: `repair`.
   - Priority: `high`.
   - Status: `in_progress`.
   - Scheduled Date: Today.
   - Notes: `Replaced spark plug, cleaned fuel carburetor.`
4. Submit and verify asset status automatically switches to `under_maintenance`.
5. Click **Mark Completed**:
   - Cost: `₱ 2,400.00`.
   - Parts Used: `Carburetor rebuild kit, spark plug`.
   - Notes: `Repairs successfully completed and engine load-tested.`
6. Submit and verify asset automatically returns to `available` status.

### 6.2. Preventive Maintenance Schedules
1. Click the **Preventive Schedules** tab (`/maintenance?tab=schedules`).
2. Review due and upcoming maintenance tasks.
3. Click **Complete Task** on any scheduled maintenance:
   - Completion Date: Today's date.
   - Technician: `Technician Santos`.
   - Notes: `Completed 30-day preventive servicing and oil change.`
4. Submit and verify schedule next due date rolls forward according to interval (e.g., 30 days).

### 6.3. Physical Equipment Inspections
1. Click the **Inspections** tab (`/maintenance?tab=inspections`).
2. Click **Record Inspection**:
   - Asset: Select equipment.
   - Inspection Date: Today.
   - Rating/Result: `passed`.
   - Next Due Date: 60 days from today.
   - Findings: `Emergency cutoff switch operational. Structural welds solid.`
3. Submit and verify inspection is logged in the permanent audit trail.

### 6.4. Security Boundary Verification (No Equipment Registration Authority)
1. While logged in as `maintenance@bdes.gov`, attempt to register a new asset (`POST /assets`):
   - The backend `AssetPolicy` rejects the request with **HTTP 403 Forbidden**.
2. Attempt to create a deployment (`POST /deployment`):
   - The backend `DeploymentPolicy` rejects the request with **HTTP 403 Forbidden**.
3. Attempt to access Admin routes (`/users`, `/financials/*`, `/reports`):
   - Returns **HTTP 403 Forbidden**.

---

## 7. Step-by-Step Testing Guide: Role 5 — Barangay Staff / Viewer

**Login:** `staff@bdes.gov` | **Password:** `password`

### 7.1. Read-Only Exploration & Defect Reporting
1. Log in as `staff@bdes.gov`.
2. Navigate to **Asset Registry** (`/assets`).
3. Notice that edit, delete, and add buttons are hidden or disabled.
4. Search for an asset by code or keyword.

### 7.2. Submitting an Asset Deficiency / Incident Report
1. Navigate to **Asset Operations -> Reported Assets** (`/reported-assets`).
2. Click **Submit Asset Report**:
   - Asset Name: `Water Rescue Life Vest Set B`.
   - Category: `Water Rescue`.
   - Location: `Purok Central Storage`.
   - Report Type: `damaged`.
   - Description: `Buckle cracked during community drill.`
3. Submit the report.
4. Verify:
   - Report is listed with status `pending`.
   - Reported By is automatically assigned to your user account.
   - Staff member cannot change status to `resolved` (only authorized officers can).

### 7.3. Self-Service Account Settings
1. Click **Account Settings** in sidebar (`/profile`).
2. Update Profile Name or Email.
3. Notice there is no role escalation field; members cannot elevate their role.

---

## 8. Comprehensive Cross-Role Workflow Scenario

```
[BDRRMC Inventory Officer] 
Registers new Generator (₱85,000) 
-> Auto-classified as Capitalized PPE (10% salvage value: ₱8,500, Useful life: 7 yrs)
       |
[BDRRMC Inventory Officer] 
Deploys Generator to Barangay Evacuation Center -> Status: Deployed
       |
[Barangay Staff Member] 
Submits report: "Engine pull cord stuck" -> Pending Incident Report
       |
[Maintenance Officer] 
Opens Work Order & Inspects Generator -> Status: Under Maintenance
       |
[Maintenance Officer] 
Completes repairs (replaced cord & spark plug) -> Status: Available
       |
[BDRRMC Inventory Officer] 
Processes return of Generator to Central Depot -> In Stock
       |
[Barangay Captain / Admin] 
Reviews live depreciation schedule, accumulated amortization, and book value.
```

---

## 9. Automated Test Suite Verification

Run the full automated test suite anytime using:

```bash
php artisan test
```

All **179 tests (641 assertions)** execute and pass with zero failures:
* `PhilippineAccountingStandardTest`: Validates ₱50,000 statutory capitalization threshold, 10% residual value, and straight-line depreciation formulas.
* `BdrrmcMemberAccessTest`: Validates role separation between Inventory Officer and Maintenance Officer, and HTTP 403 authorization guards.
* `AssetFinancialsTest`: Validates depreciation schedules, salvage records, and CSV exports.
* `SecurityAndInputHardeningTest`: Validates file upload security, input normalization, and rate limiting.
