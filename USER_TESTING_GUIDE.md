# Barangay Disaster Assets Management & Monitoring System (BDES)

## Comprehensive User-Side Role Testing Manual & Operational Guide

---

## 1. System Overview & Testing Scope

**BDES (Barangay Disaster Assets Management & Monitoring System)** is a specialized, production-ready enterprise asset management and emergency logistics platform designed for local government units (LGUs).

The system enforces **Role-Based Access Control (RBAC)** across 5 distinct user roles:

| Role Name | Database Identifier | Access Tier | Primary Operational Responsibility |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `administrator` | Full Superadmin | System configuration, user accounts & soft deletes, expenses ledger, financial valuations, centralized archived records management, audit logs, master reporting |
| **Hon. Barangay Captain** | `barangay_captain` | Executive Admin | Executive oversight, asset retirement approvals, high-level reporting, executive dashboard |
| **BDRRMC Inventory Officer** | `inventory_officer` | Operational Equipment Officer | All equipment works **aside from maintenance**: acquisitions with auto-SKU and batch quantity, asset registry, attachments & accessories, operational supplies/restocks, barangay personnel & resident deployments & returns, location management & asset relocations, incident reporting |
| **Maintenance Officer** | `maintenance_officer` | Operational Upkeep Officer | All equipment servicing & maintenance works: work orders, preventive maintenance schedules, physical inspections, condition rating, spare parts inventory consumption, repair cost tracking with receipts |
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

### Pre-Requisite Commands (Zero-Configuration Setup)

To start the local environment with a clean, fully-seeded database:

```bash
# Option A: Automatic single-command setup (Recommended)
composer run setup
php artisan serve

# Option B: Manual setup
copy .env.example .env     # On Windows (use cp on Linux/macOS)
php artisan key:generate
php artisan storage:link
php artisan migrate --force
php artisan db:seed --force
npm run build
php artisan serve
```

The system will be accessible in your web browser at: **`http://127.0.0.1:8000`** (or `http://localhost:8000`).

---

## 3. Step-by-Step Testing Guide: Role 1 — System Administrator

**Login:** `admin@bdes.gov` | **Password:** `password`

### 3.1. Dashboard & Navigation
1. Log in and arrive at `/dashboard`. Notice that all password input fields in the application (including login, user edit, and profile deletion) now feature a cleanly centered eye icon on the right edge (`top: 50%; transform: translateY(-50%)`) for instant password reveal/hide toggling.
2. Notice the enterprise KPI counters: Total Assets, Available, Deployed, In Maintenance, Out of Service, and Active Emergency Deployments.
3. Observe the left sidebar navigation:
   - Notice the drag handle on the right edge of the sidebar.
   - Click and drag the handle horizontally to test custom sidebar width (200px to 420px).
   - Refresh the page and confirm the custom sidebar width is preserved in `localStorage`.
4. Verify the Admin sidebar displays:
   - **Dashboard**
   - **Asset Management** (Asset Registry, Attachments & Accessories, Operational Requirements, Asset Needs & Supplies)
   - **Asset Operations** (Deployments & Returns, Maintenance & Work Orders, Reported Assets)
   - **Asset Financials** (Expenses Ledger, Depreciation Tracking, Salvage Value Records)
   - **Tracking & Reporting** (Asset History & Audit, Asset Locations, Reports Center)
   - **Administration** (User Management, Roles & Permissions, Account Settings)

### 3.2. User Management, Split Names, Default Password & Soft Deletion
1. Click **Administration -> User Management** (`/users`).
2. Click **Add User**:
   - Notice the form fields:
     - **First Name** (Required): e.g. `Juan`
     - **Middle Name** (Optional): e.g. `Santos`
     - **Last Name** (Required): e.g. `Dela Cruz`
     - **Suffix** (Optional): e.g. `Jr.`
     - **Username** (Required): e.g. `jdelacruz`
     - **Email Address** (Required): e.g. `jdelacruz@bdes.gov`
     - **Assigned Role**: Select `Inventory Officer`
   - Notice the **Password Input is completely removed** from this modal. An informational banner clearly explains that all accounts are automatically provisioned with the standard temporary password:
     ```
     TempPass_123
     ```
   - Click **Create Account**. Verify that the user is saved with their assembled full name (`Juan Santos Dela Cruz Jr.`) and can log in using `TempPass_123`.
3. Test **Professional Soft Deletion**:
   - Locate the newly created user and click **Delete Account**.
   - Notice that the user is safely soft-deleted (`deleted_at` timestamp recorded) without cascading hard deletions.
   - Self-deletion is strictly blocked (you cannot delete your own logged-in admin account).
   - Last-admin protection prevents deleting or disabling the sole remaining active administrator.
   - Click the **Archived / Trashed** filter tab on the User Management table.
   - Notice the soft-deleted user appears with a `Trashed` status badge and a **Restore Account** action button.
   - Click **Restore** to immediately recover the account back to active standing.
4. Test **Account Disable/Enable**:
   - Locate any non-admin user and click **Disable Account**.
   - Verify the status badge updates to `Inactive`.
   - Attempt to log in with that disabled account in an Incognito window: verify that access is blocked with a polite error notification.
   - Click **Enable Account** to restore status.

### 3.3. Asset Financials: Dedicated Money & Valuation Hub
All financial data, acquisition costs, valuation summaries, budgeting figures, depreciation, salvage appraisal, and proof-of-cost receipts have been centralized exclusively into the **Asset Financials** suite (`/financials/*`). Operational pages (Asset Registry, Accessories Hub, Maintenance Logs) remain dedicated to equipment status, serial numbers, locations, and readiness.

A unified top tab bar allows seamless one-click switching between:
- **Expenses Ledger & Outlay** (`/financials/expenses`)
- **Depreciation Schedule** (`/financials/depreciation`)
- **Salvage Value Records** (`/financials/salvage`)

#### 3.3.1. Consolidated Expenses Ledger Module
1. Click **Asset Financials -> Expenses Ledger** (`/financials/expenses`).
2. Notice the comprehensive ledger aggregating all financial outlays across:
   - **Capital Assets** (Initial asset procurement cost per asset)
   - **Accessories** (Peripheral attachments and tools)
   - **Attachments** (Specialized equipment add-ons with unit costs)
   - **Operational Supplies** (Quantity restocks with suppliers and unit prices)
   - **Maintenance & Repairs** (Completed service orders, parts replaced, and contractor invoices)
3. Review the top KPI cards:
   - **Total System Expenses** (Consolidated sum of all expenditure categories)
   - **Capital Equipment Investment**
   - **Maintenance & Repairs**
   - **Supplies & Accessories**
4. Test **Filtering & Search**:
   - Filter by Category: `Capital Assets`, `Maintenance & Repairs`, or `Operational Supplies`.
   - Search by keyword, date range (`From Date` - `To Date`), and minimum amount bounds.
   - Click **Apply Filters** and confirm instant table updates.
5. Inspect **Proof-of-Cost Receipts**:
   - Columns display receipt badges (`X Files` or thumbnail link).
   - Click on any receipt to preview or download the invoice/purchase order file.
6. Click **Export CSV**:
   - Verify that `bdes-expenses-ledger-YYYY-MM-DD.csv` downloads cleanly with RFC-compliant headers and formula injection sanitization (`=` and `+` escaped).

#### 3.3.2. Philippine Standard Depreciation Tracking & Salvage Records
1. Switch to the **Depreciation Schedule** tab or navigate to `/financials/depreciation`.
2. Notice the **Philippine Government Accounting Standard Notice**:
   > Items with acquisition costs ≥ ₱50,000.00 are recognized as Capitalized PPE (COA Circular No. 2022-004) with Straight-Line Amortization and a 10% standard residual value (COA Circular No. 2003-007). Items below ₱50,000.00 are Semi-Expendable Property and statutorily non-depreciable.
3. Observe the table entries:
   - Capitalized assets display the green `PPE` badge with active straight-line annual/monthly depreciation.
   - Items under ₱50,000 display the amber `Semi-Expendable` badge, `₱0.00` depreciation, and `Carried at Cost`.
4. Click **Edit Parameters** on a Capitalized PPE asset:
   - Update useful life to `7` years.
   - Enter Custom Salvage Value: `15000`.
   - Save and verify instant recalculation.
5. Switch to the **Salvage Value Records** tab (`/financials/salvage`) to view appraisal values and scrap salvage recovery forecasts.
6. Click **Export CSV** on either schedule to export official COA-compliant valuation spreadsheets.

### 3.4. System Audit Trail (Full Administrative Oversight)
1. Click **Tracking & Reporting -> Asset History & Audit Trail** (`/asset-history`).
2. Observe the green badge: `Full Administrative Access • All System Accounts`.
3. Notice that as an Administrator, you can view all audit events across all accounts and actors.
4. Test filtering by specific actors in the **All Actors / Users** dropdown.
5. Click **Export Audit Log** to export the comprehensive system audit trail CSV.

### 3.5. Centralized Archived Records (Soft Deletes & Data Protection)
1. Click **Tracking & Reporting -> Archived Records** (`/archived-records`).
2. Notice the centralized table containing soft-deleted records across 10 system modules:
   - Assets, Deployments, Returns, Maintenance, Maintenance Schedules, Inspections, Reported Assets, Operational Requirements, Attachments, and Accessories.
3. Review the table columns:
   - **Record ID & Name** (e.g., `AST-005 - Heavy Chainsaw`, `WO-2026-0004 - Water Pump Overhaul`)
   - **Source Module** (color-coded badge: Asset Registry, Deployments, Maintenance, etc.)
   - **Deletion Reason** (audit justification recorded at time of soft delete)
   - **Deleted By** (actor responsible for archiving)
   - **Date Deleted** (relative and exact timestamp)
   - **Actions** (Restore Record, Permanent Delete)
4. Test **Filters**:
   - Filter by **Module**: Select `Maintenance`. Verify only deleted work orders and schedules appear.
   - Filter by **Deleted By**: Select a user to review their deletions.
   - Search by keyword or specify a date range.
5. Test **Record Restoration**:
   - Locate an archived record and click **Restore**.
   - Review the confirmation modal explaining that the record will be restored with its existing relationships intact.
   - Click **Confirm Restore**.
   - Verify that the record disappears from Archived Records and reappears in its original operational table.
   - Check **Audit Trail** to confirm the `restored` event was recorded with your admin user ID.
6. Test **Permanent Purge (Force Delete)**:
   - Locate a disposable archived test record and click **Delete Permanently**.
   - Notice the prominent danger confirmation modal warning that this action is irreversible.
   - Confirm permanent deletion and verify the record is expunged and an audit log of type `force_deleted` is recorded.

### 3.6. Intelligent Autocomplete Search Bar (Fast Multi-Module Jump)
1. Navigate to any module page equipped with a search bar (e.g. Deployments, Maintenance, Users, Returns).
2. Type at least 2 characters (e.g. `gen`, `amb`, `bar`, or `wo`).
3. Notice the live dropdown suggestions appear instantly (minimum 5 categorized results):
   - Categorized by **Assets** (Code, Name, Category), **Deployments**, **Work Orders**, and **Locations**.
4. Click on any search suggestion or press Enter:
   - Verify you are immediately redirected to the exact dossier, work order, or location page.

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

### 5.1. Unified Asset Hub & Add Asset Card
1. Navigate to **Asset Management -> Asset Registry** (`/assets`).
2. Notice the unified tabbed interface:
   - **Assets** tab (Full equipment registry with search and sorting)
   - **Categories** tab (Merged category management directly in-page)
   - **Asset Types** tab (Merged asset classification type management)
3. Click **Add Asset** to open the enhanced modal:
   - Notice the **Automated SKU** informational banner: SKU codes are automatically derived based on category and type prefix (`FE-003`, `GEN-002`, `FAK-003`, `BOT-001`, etc.), skipping any colliding numbers.
   - Notice the **+ Add New** quick links next to **Category**, **Asset Type**, and **Assigned Location**:
     - Click **+ Add New** next to Category.
     - You are smoothly navigated to the category creator; when created, the system returns you right back to the Add Asset modal with your drafted form preserved in `sessionStorage` and the new category pre-selected.
   - Notice the **Cost per Asset (PHP)** label (formerly "Acquisition Cost").
   - Notice the **Quantity** input (1 to 100):
     - Entering a quantity of `5` automatically calculates the **Total Cost**: `5 × Cost per Asset`.
     - Submitting creates 5 separate asset records with distinct sequential SKUs in a single safe database transaction.
   - Notice the **Proof of Cost / Receipt Upload** dropzone:
     - Upload multiple PDF, JPG, or PNG receipts.
     - The receipts are automatically linked to all created asset records in the batch.

### 5.2. Deployments & Returns (Barangay Personnel & Residents)
1. Navigate to **Asset Operations -> Deployments & Returns** (`/deployment`).
2. Notice the table headers:
   - The former "Return Inspection" column is now cleanly labeled **Inspection**.
   - Resident borrowings display a distinct **Resident Borrower** badge with contact number and valid ID type/number.
3. Test **Prominent "Record Return" Button in Header**:
   - Click the green **Record Return** button at the top right of the page.
   - An active deployment selector allows you to pick any deployed asset immediately.
   - Set condition on return (`good`, `fair`, `poor`), return date, and inspection notes.
   - Click **Save Return** to instantly mark the asset back as `available`.
4. Test **Deploying to a Barangay Resident**:
   - Click **Deploy Asset**.
   - Choose **Borrower Type**: `Barangay Resident`.
   - Enter Resident Full Name: `Maria Corazon Santos`.
   - Enter Contact Number: `0917-123-4567`.
   - Enter Residential Address: `House 14, Purok 3, Barangay Proper`.
   - Select Valid ID Type: `Barangay ID` (or `PhilHealth`, `UMID`, `Driver's License`, `Voter's ID`).
   - Enter Valid ID Number: `BRGY-2026-9812`.
   - Purpose: `Emergency residential pumping due to localized gutter overflow`.
   - Submit and verify the deployment appears with full resident borrower credentials.
5. Test **Chronological Return Date Validation**:
   - Locate an active deployment deployed on e.g. `Nov 01, 2026`.
   - Click **Return** and attempt to set the Return Date to `Oct 25, 2026` (before deployment).
   - Click **Save Return**.
   - Verify that the system strictly blocks the return and displays an inline validation alert: `The return date cannot be earlier than the deployment date.`
   - Set the Return Date to today (valid date) and confirm the return saves smoothly.
6. Test **Deployment Edit Status Protection**:
   - Click **Edit** on any existing deployment.
   - Notice that the status field cannot be arbitrarily altered to bypass return verification. The official return process (`/returns`) remains the sanctioned mechanism for closing out borrowings.

### 5.3. Asset Locations & In-Place Asset Relocation
1. Navigate to **Tracking & Reporting -> Asset Locations** (`/locations`).
2. Click on any Location card (e.g. `Barangay Storage Yard` or `Main Hall Depot`).
3. View the list of assets currently stationed at that physical facility.
4. Locate an asset (e.g. `AMB-001 - Rescue Ambulance Alpha`) and click **Relocate Asset**:
   - A modal opens displaying the current location.
   - Select the **New Destination Location** (e.g. `Emergency Evacuation Center`).
   - Enter relocation notes/reason: `Pre-staged for flood season response.`
   - Click **Confirm Relocation**.
5. Verify that:
   - The asset is immediately removed from the old location count and credited to the new location.
   - An audit trail event (`asset_relocated`) and an Asset History movement record are created automatically.

### 5.4. Managing Accessories, Attachments & Operational Requirements
1. Navigate to **Asset Management -> Attachments & Accessories** (`/attachments`).
   - Verify that all **4 seeded accessories** (Heavy-Duty Extension Cord, Oxygen Tank Regulator, Suction Hose 3-inch, Spark Plug Spare Set) are visible and linked to their assets.
   - Verify that **6 disaster attachments** (Tow Hitch Trailer, Marine Propeller Guard, Heavy-Duty Spreader Jaws, etc.) are populated with unit costs.
2. Navigate to **Asset Management -> Operational Requirements** (`/operational-requirements`).
   - Notice the unified dashboard summarizing all warehouse consumables and equipment needs seamlessly.
   - Interactive KPI Summary Cards:
     - **Total Requirements**: Total count of all supplies and needs.
     - **Adequate Reserves / In Stock**: Click to filter entries with safe reserve levels (`?status=adequate`).
     - **Low Stock Alert**: Click to filter entries requiring replenishment (`?low=1`).
   - Unified Inventory Table columns:
     - **Requirement / Supply**: Item name, icon, and description.
     - **Target Equipment / Type**: Linked equipment unit or general category.
     - **Available Stock**: Visual color-coded fill bar with current level against capacity.
     - **Status**: Status badge (`In Stock`, `Low Stock`, `Requires Maintenance`).
     - **Actions**: Restock/Replenish (`+`), Edit, and Delete (Row click opens full Dossier modal).
   - Test **Add Requirement**:
     - Click **Add Requirement**.
     - Notice the inputs are cleanly arranged: **Equipment Type**, **Requirement Name**, **Unit of Measurement**, **Current Level**, **Maximum Capacity**, and **Minimum Safety Level**.
     - Test clicking **Cancel**: verify that the modal immediately closes without error.
   - Test **Add Asset Need**:
     - Click **Add Asset Need** to record equipment-specific consumable specifications.
   - Test **Restock / Replenish**:
     - Click **+ Add Quantity** on a requirement:
       - Quantity: `10`
       - Supplier: `Petron Dagupan Station`
       - Reference / Invoice: `INV-2026-009`
     - Submit and verify stock increases and restock transaction is recorded into the expenses ledger.
   - For an exhaustive reference of all data points and operations, consult [OPERATIONAL_REQUIREMENTS_GUIDE.md](file:///c:/Capstone/BDES/OPERATIONAL_REQUIREMENTS_GUIDE.md).

### 5.5. Account-Specific Audit Trail
1. Click **Tracking & Reporting -> Asset History & Audit** (`/asset-history`).
2. Observe the blue badge: `Account-Specific Access • Logs for BDRRMC Inventory Officer`.
3. Notice that you only see audit actions performed by your specific account.
4. Verify that you cannot see activities performed by administrators or other officers.
5. Attempting to filter by another user is strictly blocked by backend RBAC scoping.
6. Open any asset dossier (`/assets/{id}`) and click Tab 8 (**Audit Trail**): notice that only actions performed by your account on this asset are rendered.

---

## 6. Step-by-Step Testing Guide: Role 4 — Maintenance Officer

**Login:** `maintenance@bdes.gov` | **Password:** `password`

*(Responsible for equipment upkeep, repairs, preventive maintenance schedules, physical inspections, spare parts consumption, and repair cost proof)*

### 6.1. Unified Maintenance Hub, Record Classification & KPI Cards
1. Log in and navigate to **Asset Operations -> Maintenance & Work Orders** (`/maintenance?tab=logs`).
2. Review the top KPI cards:
   - **Active Orders**: In-progress, scheduled, and pending repairs. Click this card to instantly filter for active work orders without error; confirm that preventive schedules are cleanly excluded.
   - **Completed Tasks**: Resolved and verified work orders.
   - **Asset Inspections**: Quality & safety audits count.
   - **Preventive Schedules**: Recurring schedules with due/overdue counters.
3. Test **Record Classification Filter (`record_type`)**:
   - Filter by **Work Orders (WO)**: The table displays only work orders with `WO-YYYY-XXXX` badges.
   - Filter by **Preventive Schedules (SCHED)**: The table displays only recurring schedules with `SCHED-XXXX` badges.
   - Filter by **All Records**: Seamlessly interweaves both streams chronologically.

### 6.2. Parts & Materials Tracking with Live Inventory Deduction
1. In the table, observe the **Parts & Materials** column:
   - Shows consumed spare parts with quantities, units, and itemized costs (e.g. `2 pcs Oil Filter (₱800.00)`).
2. Click **Create Work Order** (`/maintenance`):
   - Select an asset, maintenance type, priority, and scheduled date.
   - In the **Parts & Materials Consumed** section:
     - Select items from the integrated spare parts inventory (attachments and accessories with stock > 0).
     - Enter quantity used (e.g. `1` unit of `Spark Plug Spare Set`).
     - Notice the unit cost and total cost calculate dynamically.
   - Upload repair quotation or receipt.
   - Submit work order:
     - Confirm the asset status updates to `under_maintenance`.
     - Confirm the spare parts stock in inventory decreases by the consumed amount.

### 6.3. Accurate Service Dates & View Details Modal
1. Observe the **Service Dates** column:
   - **For Work Orders**: Displays Created, Scheduled, Started, and Completed dates.
   - **For Schedules**: Displays Last Serviced date and Next Due date (color-coded red if overdue).
2. Click **View Details** on any work order:
   - Verify the comprehensive dossier displays: Asset details, Problem description, Assigned technicians, Inspection findings, Consumed parts & costs, Technician remarks, and Attached receipts.

### 6.4. Work Order Completion & Asset Condition Verification
1. Click **Complete Work Order** on an in-progress repair.
2. Enter completion details: Work performed, actual labor/parts cost, and attach service invoice.
3. Notice the **Post-Maintenance Operational Condition Verification**:
   - The system does **not** blindly mark the asset as Available.
   - The officer must verify and certify the asset's true operational condition (`good`, `fair`, `poor`, `operational`, `under_maintenance`).
4. Save completion: confirm the financial outlay syncs to the Expenses Ledger with receipts attached.

### 6.5. Asset Inspections & Work Order Generation from Findings
1. Switch to the **Inspections** tab (`/maintenance?tab=inspections`).
2. Click **Record Inspection**:
   - Asset: Select equipment.
   - Rating: `failed` or `conditional_pass`.
   - Findings: `Hydraulic cylinder seal leaking under pressure.`
   - Attach condition photo.
   - Submit inspection.
3. For any inspection needing repair, test clicking **Create Work Order**:
   - Notice the work order modal automatically pre-populates with the asset, inspection reference, and finding notes, establishing complete operational traceability.

---

## 7. Step-by-Step Testing Guide: Role 5 — Barangay Staff / Viewer

**Login:** `staff@bdes.gov` | **Password:** `password`

### 7.1. Read-Only Exploration & Defect Reporting
1. Log in as `staff@bdes.gov`.
2. Navigate to **Asset Registry** (`/assets`).
3. Notice that administrative modification buttons (Edit, Delete, Add Asset) are properly hidden.
4. Search for an asset by code or keyword using the search bar.

### 7.2. Incident Reporting, Confirmation & Multiple Personnel Assignments
1. Navigate to **Asset Operations -> Reported Assets** (`/reported-assets`).
2. Click **Submit Asset Report**:
   - Select Asset, report type (`damaged`), and describe the defect.
   - Upload defect photo evidence or damage documentation.
   - Submit and confirm status is set to `pending`.
3. Log in as an Administrator or Inventory Officer (`inventory@bdes.gov`):
   - Open the reported asset record.
   - Test **Confirm Report**: Click **Confirm** to verify the reported issue (records `confirmed_by` and confirmation timestamp).
   - Test **Assign Personnel**:
     - Click **Assign Personnel**.
     - Notice you can select **multiple technicians / officers** who will collaborate on the issue.
     - Save assignment and confirm status progresses towards repair resolution.

---

## 8. Universal File, Photo & Document Upload Verification

Every "Add" creation modal and operational submission across BDES now includes an optional, secure multi-file uploader component (`x-receipt-input`) supporting PDFs, JPGs, PNGs, and WEBP documents (up to 5 MB per file, max 5 files per record):

| Form Modal | Module Location | Accepted Document Evidence |
| :--- | :--- | :--- |
| **Add Asset** | `/assets` | Equipment photo, manufacturer user manual, spec sheet, or proof-of-purchase receipt |
| **Add Attachment** | `/attachments?tab=attachments` | Attachment photo, compatibility spec sheet, or purchase receipt |
| **Add Accessory** | `/attachments?tab=accessories` | Accessory photo, user guide, or official receipt |
| **Add Operational Requirement** | `/operational-requirements` | Safety Data Sheet (MSDS), supplier spec sheet, or initial quotation |
| **Restock Supply** | `/operational-requirements` | Delivery receipt, supplier official receipt (OR), or invoice |
| **Deploy Asset (Personnel or Resident)** | `/deployments` | Signed borrowing slip, resident government/barangay ID photocopy, or requisition slip |
| **Record Return** | `/deployments` | Return condition inspection photo, turn-in clearance, or damage documentation |
| **Create Preventive Schedule** | `/maintenance?tab=schedules` | Maintenance procedure SOP, checklist manual, or manufacturer servicing schedule |
| **Record Inspection** | `/maintenance?tab=inspections` | Condition assessment photo, testing certificate, or signed inspection checklist |
| **Create / Complete Work Order** | `/maintenance?tab=logs` | Repair quotation, parts purchase receipt, or technician service invoice |
| **File Incident Report** | `/reported-assets` | Defect photo evidence, incident report sheet, or damage documentation |
| **Add Asset Need** | `/asset-needs` | Product specification sheet, price quotation, or purchase requisition |

---

## 9. Automated Test Suite Verification

Run the full automated test suite anytime using:

```bash
php artisan test
```

All **310 tests (1,706 assertions)** execute and pass with **100% green rate (zero failures)**:
* `ArchivedRecordsTest`: Centralized soft-deletes management across 10 modules, filtering by module and actor, relationship-preserving restoration, permanent force delete with confirmation, and audit logging.
* `AssetRelocationTest`: In-place asset relocation directly from physical locations, audit movement history generation, and location inventory updates.
* `ReportedAssetAndMaintenanceConfirmationTest`: Confirmation workflow, multiple simultaneous personnel assignments, and resolution tracking.
* `MaintenanceModuleEnhancementsTest` & `UnifiedMaintenanceTest`: Record classification (`WO` vs `SCHED`), Active Orders KPI card filtering without errors, spare parts inventory consumption & automatic stock updates, accurate service dates, and operational condition verification.
* `DeploymentAndReturnTest`: Chronological return date validation (`return_date >= deployment_date`), deployment status protection against unauthorized edits, resident borrowings with valid IDs, and return condition inspections.
* `GeneralAutocompleteSearchTest`: Real-time autocomplete suggestions (≥5 suggestions) across Assets, Deployments, Work Orders, and Locations.
* `UniversalFileUploadTest`: Universal file uploads across Asset Attachments, Accessories, Operational Requirements, Deployments, Returns, Preventive Schedules, Inspections, Reported Assets, and Asset Needs.
* `UserManagementTest`: Split name fields, default `TempPass_123` password, soft deletion & restoration, self-delete protection, last-admin defense.
* `ExpensesLedgerTest`: Admin-only access guard, consolidated financial compilation across Capital Assets, Accessories, Attachments, Supplies Restocks, and Maintenance, filtering, and CSV export.
* `SkuGeneratorTest`: Automatic 2-4 letter category/type prefixing (`FE`, `GEN`, `FAK`, `BOT`), collision handling, and sequential batch generation.
* `PhilippineAccountingStandardTest`: ₱50,000 statutory capitalization threshold, 10% residual value, straight-line depreciation formulas.
* `BdrrmcMemberAccessTest`: Role separation between Inventory Officer and Maintenance Officer, and HTTP 403 authorization guards.
* `SecurityAndInputHardeningTest`: File upload security, input normalization, and rate limiting.
