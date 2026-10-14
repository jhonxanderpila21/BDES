# Barangay Disaster Assets Management & Monitoring System (BDES)
## User-Side Role Testing & Verification Guide

This guide provides step-by-step instructions for testing the **Barangay Disaster Assets Management & Monitoring System (BDES)** from the end-user side across all six (6) system roles.

---

## 1. Quick Environment Setup & Server Launch

Before testing, ensure your local environment is running and seeded with default test accounts and mock data.

### Step 1: Open Terminal in Project Root
```bash
cd c:\Capstone\BDES
```

### Step 2: Initialize Database and Seed Mock Data
```bash
# Run migrations and seed all 6 default roles + sample disaster assets + audit logs
php artisan migrate:fresh --seed
```

### Step 3: Compile Frontend Assets
```bash
npm run build
```

### Step 4: Start Laravel Development Server
```bash
php artisan serve
```
Open your browser and navigate to: **`http://localhost:8000`** (or `http://127.0.0.1:8000`).

---

## 2. Role Credentials & Access Matrix

All test accounts use the universal password: **`password`**

| Role Title | Email Address | Password | Role Key | Primary Operational Scope |
| :--- | :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@bdes.gov` | `password` | `administrator` | Full unrestricted access (Users, Roles, Financials, Reports, Deletions) |
| **Hon. Barangay Captain** | `captain@bdes.gov` | `password` | `barangay_captain` | Executive Admin tier (Sole authority to officially Retire/Decommission assets) |
| **BDRRMC Officer Member** | `member@bdes.gov` | `password` | `bdrrmc_officer` | Operational Editor (Asset registry, field deployments, returns, work orders) |
| **Inventory & Logistics Officer** | `inventory@bdes.gov` | `password` | `inventory_officer` | Logistics Specialist (Consumables, fuel refill/consume, stock replenishment) |
| **Maintenance Specialist** | `maintenance@bdes.gov` | `password` | `maintenance_officer` | Technical Specialist (Preventive schedules, physical inspections, work orders) |
| **Barangay Staff / Viewer** | `staff@bdes.gov` | `password` | `viewer` | Operational Viewer (Read-only search, location directory, audit timeline) |

---

## 3. Step-by-Step Testing Walkthrough by Role

---

### Scenario 1: Testing as System Administrator (`admin@bdes.gov`)
> **Goal:** Verify unrestricted administrative capabilities, user management, straight-line depreciation, reports center, and UI customization.

1. **Sign In**:
   - Navigate to `http://localhost:8000/login`.
   - Enter Email: `admin@bdes.gov`, Password: `password`.
   - Click **Sign In**.
   - **Verification**: The top navigation bar displays your name ("System Administrator") and an **Administrator** badge.

2. **Test Adjustable Navigation Bar Width**:
   - Hover over the right vertical border of the left sidebar.
   - The cursor changes to a column resizer (`col-resize`) with a blue highlight.
   - **Click and drag** to the right to expand the sidebar (up to 420px), or left to shrink it (down to 200px).
   - **Double-click** the resizer handle: it resets back to the default 256px (`w-64`).
   - Refresh the page: the width preference persists via `localStorage`.

3. **Verify Executive Dashboard & KPI Metrics**:
   - Check the 6 clickable KPI cards (*Total Assets, Available, Deployed, Maintenance, Out of Service, Inspections*).
   - Click the **Available** KPI card: it filters the Asset Registry to show only available equipment.
   - View the **Category Distribution** panel: note the total asset count inside the cutout center of the doughnut chart and the progress bars on the right.
   - View the **Operational Attention Required** alert banner (if overdue deployments or maintenance exist).

4. **Test User & Role Management (Admin-Only)**:
   - In the sidebar, expand **Administration** -> click **User Management** (`/users`).
   - Verify you can search users, filter by role or status.
   - Click **Roles & Permissions** (`/roles`): view the RBAC permission matrix.

5. **Test Asset Financials: Straight-Line Depreciation & Salvage Records (Admin-Only)**:
   - In the sidebar, expand **Asset Financials** -> click **Depreciation Tracking** (`/financials/depreciation`).
   - Verify summary KPIs: *Total Capitalization, Accumulated Depreciation, Net Book Value*.
   - Click the action button on an asset to adjust *Useful Life Years* or *Salvage Value*.
   - Click **Export Depreciation CSV** to download a spreadsheet.
   - Click **Salvage Value Records** (`/financials/salvage`): view appraisal notes and residual values.

6. **Test Reports Center (Admin-Only)**:
   - In the sidebar, expand **Tracking & Reporting** -> click **Reports Center** (`/reports`).
   - Select a report type (*Inventory Dossier, Financial Valuation, Maintenance Logs, Deployment Tracking*).
   - Click **Export Report CSV**.

7. **Test Permanent Asset Deletion (Admin-Only)**:
   - Go to **Asset Management** -> **Asset Registry** (`/assets`).
   - Verify the red **Delete** action is available for Administrator accounts.

8. **Log Out**:
   - Click user avatar in top-right corner -> **Log Out**.

---

### Scenario 2: Testing as Hon. Barangay Captain (`captain@bdes.gov`)
> **Goal:** Verify executive oversight and exclusive asset retirement/decommissioning authority.

1. **Sign In**:
   - Navigate to `http://localhost:8000/login`.
   - Enter Email: `captain@bdes.gov`, Password: `password`.
   - **Verification**: User badge indicates **Barangay Captain**.

2. **Verify Executive Access**:
   - Observe full access to **Asset Financials**, **Reports Center**, and **Administration**.

3. **Test Exclusive Asset Retirement Authority**:
   - Navigate to **Asset Management** -> **Asset Registry** (`/assets`).
   - Click on any asset to view its detail page (`/assets/{id}`).
   - Locate the **Retire / Decommission Asset** button (only available to Barangay Captain and Admin).
   - Enter a retirement justification (e.g., *"Damaged during emergency flood response, beyond economic repair"*).
   - Submit the retirement request.
   - **Verification**: The asset status updates to **Retired**, and an immutable entry is logged in the Audit Trail.

4. **Log Out**:
   - Click top-right avatar -> **Log Out**.

---

### Scenario 3: Testing as BDRRMC Officer Member (`member@bdes.gov`)
> **Goal:** Verify operational field editor capabilities, streamlined sidebar, and backend security boundaries.

1. **Sign In**:
   - Navigate to `http://localhost:8000/login`.
   - Enter Email: `member@bdes.gov`, Password: `password`.
   - **Verification**: User badge indicates **BDRRMC Officer**.

2. **Inspect Streamlined Navigation Layout**:
   - Verify the **Administration** dropdown is **NOT shown**; replaced with a direct **Account Settings** link.
   - Verify **Reports Center** is **NOT shown** under Tracking & Reporting (and absent from dashboard header).
   - Verify **Asset Financials** is **NOT shown**.
   - Verify **Asset Locations** is visible as a separated top-level link.

3. **Test Asset Registry Operations**:
   - Click **Asset Management** -> **Asset Registry** (`/assets`).
   - Click **Register Asset**: create a new emergency tool (e.g., *"Rescue Helmet Pack"*).
   - Edit an existing asset record.
   - **Boundary Check**: Verify there is **NO permanent Delete button**.

4. **Test Field Deployment & Return Workflow**:
   - Click **Asset Operations** -> **Deployments & Returns** (`/deployment`).
   - Click **Deploy Asset**: choose an available asset, destination (e.g., *"Evacuation Center 1"*), and expected return date.
   - Once deployed, click **Return Asset**: record the return condition (*Good / Damaged*) and remarks.
   - Verify status transitions from `deployed` back to `available`.

5. **Test Maintenance & Physical Inspections**:
   - Click **Asset Operations** -> **Asset Inspection** (`/maintenance?tab=inspections`).
   - Record a physical inspection checkup.
   - Complete an overdue maintenance routine.

6. **Test Tamper-Evident Asset History**:
   - Click **Tracking & Reporting** -> **Asset History & Audit** (`/asset-history`).
   - Review audit logs recording your recent deployment, return, and asset modifications.

7. **Test Boundary Security (Attempt Direct URL Access)**:
   - In your browser's URL address bar, manually type:
     - `http://localhost:8000/users`
   - **Verification**: The system blocks access and displays the custom **HTTP 403 - Access Restricted** page with a *"Return to Dashboard"* button.
   - Repeat for:
     - `http://localhost:8000/financials/depreciation` -> **403 Forbidden**
     - `http://localhost:8000/financials/salvage` -> **403 Forbidden**
     - `http://localhost:8000/reports` -> **403 Forbidden**

8. **Log Out**.

---

### Scenario 4: Testing as Inventory & Logistics Officer (`inventory@bdes.gov`)
> **Goal:** Verify logistics, consumables management (fuel/supplies), stock refills, and accessories.

1. **Sign In**:
   - Enter Email: `inventory@bdes.gov`, Password: `password`.
   - **Verification**: User badge indicates **Inventory Officer**.

2. **Test Operational Requirements (Consumables & Readiness Gaps)**:
   - Click **Asset Management** -> **Operational Requirements** (`/operational-requirements`).
   - Locate an item (e.g., *"Generator Fuel Reserve"*).
   - Click **Refill Supplies**: add 50 liters.
   - Click **Consume Supplies**: deduct 15 liters for generator standby.
   - **Verification**: Quantity, percentage bar, and reorder status update immediately.

3. **Test Asset Needs & Supplies**:
   - Click **Asset Management** -> **Asset Needs & Supplies** (`/asset-needs`).
   - Replenish requested disaster supplies.

4. **Test Attachments & Accessories**:
   - Click **Asset Management** -> **Attachments & Accessories** (`/attachments`).
   - Adjust accessory counts and verify stock reconciliation.

5. **Test Separated Asset Locations**:
   - Click **Asset Locations** (`/locations`).
   - View disaster asset capacity across Barangay Storage Facilities.
   - **Verification**: Location list is readable; non-admin users cannot alter station definitions.

6. **Log Out**.

---

### Scenario 5: Testing as Maintenance Specialist (`maintenance@bdes.gov`)
> **Goal:** Verify work order lifecycle, physical inspections, and document uploads.

1. **Sign In**:
   - Enter Email: `maintenance@bdes.gov`, Password: `password`.
   - **Verification**: User badge indicates **Maintenance Officer**.

2. **Test Work Order Management**:
   - Click **Asset Operations** -> **Maintenance & Work Orders** (`/maintenance?tab=logs`).
   - Click **Create Work Order**: select an asset, specify maintenance type (*Preventive / Corrective*), priority (*Urgent*), and description.
   - Locate an in-progress work order and click **Complete Maintenance**: enter actual repair cost and notes.
   - Verify asset condition and status update accordingly.

3. **Test Preventive Schedules**:
   - Switch to **Asset Inspection** (`/maintenance?tab=inspections`).
   - Mark a scheduled preventive checkup complete.

4. **Test Document Dossier Uploads**:
   - Go to **Asset Management** -> **Asset Registry** (`/assets`).
   - Open any asset detail view (`/assets/{id}`).
   - Scroll to the Documents tab and upload an inspection certificate or maintenance receipt.

5. **Log Out**.

---

### Scenario 6: Testing as Barangay Staff Member / Viewer (`staff@bdes.gov`)
> **Goal:** Verify read-only transparency without mutation privileges.

1. **Sign In**:
   - Enter Email: `staff@bdes.gov`, Password: `password`.
   - **Verification**: User badge indicates **Viewer**.

2. **Verify Read-Only Navigation & Dossiers**:
   - View **Dashboard**: inspect real-time KPIs and category statistics.
   - Navigate to **Asset Registry** (`/assets`): search and filter equipment dossiers.
   - Open an asset view: view specifications, serial numbers, attachments, and service history.
   - **Verification**: All action buttons (*Register Asset, Edit, Delete, Deploy, Retire*) are hidden or disabled.
   - Navigate to **Asset Locations** (`/locations`): view storage locations in read-only mode.
   - Navigate to **Asset History & Audit** (`/asset-history`): view timeline of equipment movements.

3. **Test Self-Service Account Management**:
   - Click **Account Settings** (`/profile`).
   - Update full name or password.
   - **Verification**: Profile updates cleanly without permission escalation.

4. **Log Out**.

---

## 4. Summary of Role Permissions & UI Behaviors

| Feature / Action | Admin | Barangay Captain | BDRRMC Officer | Inventory Officer | Maintenance Officer | Viewer / Staff |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: |
| **View Dashboard & KPIs** | Yes | Yes | Yes | Yes | Yes | Yes |
| **Resize Navigation Bar** | Yes | Yes | Yes | Yes | Yes | Yes |
| **Register / Edit Assets** | Yes | Yes | Yes | Yes | Restricted | Read-Only |
| **Permanently Delete Assets** | Yes | No | No | No | No | No |
| **Retire / Decommission Assets** | Yes | Yes | No | No | No | No |
| **Field Deployments & Returns** | Yes | Yes | Yes | Yes | No | Read-Only |
| **Work Orders & Maintenance** | Yes | Yes | Yes | Yes | Yes | Read-Only |
| **Refill / Consume Supplies** | Yes | Yes | Yes | Yes | No | Read-Only |
| **View Asset Locations** | Yes | Yes | Yes | Yes | Yes | Yes |
| **Manage Storage Locations** | Yes | Yes | No | No | No | No |
| **Depreciation Tracking** | Yes | Yes | Blocked (403) | Blocked (403) | Blocked (403) | Blocked (403) |
| **Salvage Value Records** | Yes | Yes | Blocked (403) | Blocked (403) | Blocked (403) | Blocked (403) |
| **Reports Center & CSV Export** | Yes | Yes | Blocked (403) | Blocked (403) | Blocked (403) | Blocked (403) |
| **User & Role Management** | Yes | Yes | Blocked (403) | Blocked (403) | Blocked (403) | Blocked (403) |

---

## 5. Automated Verification Test Suite

To run all automated feature, policy, lifecycle, and role access tests in terminal:

```bash
# Run complete test suite (164 tests, 567 assertions)
php artisan test

# Run dedicated role access and boundary test
php artisan test --filter=BdrrmcMemberAccessTest

# Run straight-line asset financials test
php artisan test --filter=AssetFinancialsTest
```
All tests should return **PASS** with 0 failures and 0 errors.

