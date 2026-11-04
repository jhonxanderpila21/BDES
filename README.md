# Barangay Disaster Assets Management & Monitoring System (BDES)

A modern, robust, and secure Laravel-based enterprise asset management and disaster preparedness logistics system tailored for Barangay Disaster Risk Reduction and Management Committees (BDRRMC) and Local Government Units (LGUs).

---

## 🚀 Key Features

- **Master Asset Registry & 360° Dossier**: Comprehensive lifecycle tracking (available, deployed, under maintenance, out of service, retired, lost) with audit trails, document attachments, batch quantity creation, auto-SKU generation, and specification history.
- **Field Deployments & Return Check-Ins**: Real-time tracking of emergency equipment dispatch, resident borrowings with valid IDs, chronological date validation (`return_date >= deployment_date`), status protection against tampering, and condition inspections on return.
- **Preventive Maintenance & Work Orders Hub**: Unified management with `WO` vs `SCHED` record classification, Active Orders KPI filtering, parts and materials tracking with automated spare parts inventory deductions, accurate service dates, and mandatory condition verification before returning assets to service.
- **Reported Assets & Incident Confirmation**: Formally confirm reported defects, assign multiple technicians simultaneously, track work progress, and automatically generate work orders from inspection findings.
- **Asset Locations & In-Place Relocation**: Real-time facility inventory overview with direct in-place asset relocation modals and automatic movement history logging.
- **Centralized Archived Records (Soft Deletes Management)**: Centralized table under Tracking & Reporting (`/archived-records`) managing deleted records across 10 modules with filtering, audit logging, relationship-preserving restoration, and permanent purge protection.
- **Operational Requirements & Consumables**: Tracking fuel, two-stroke oil, and emergency consumables with automated threshold alerts for low-stock scenarios and restock expense logging.
- **Attachments & Accessories**: Fine-grained tracking of hoses, nozzles, blades, harnesses, and safety accessories linked to parent assets.
- **Automated Philippine Government Standard Accounting (COA Circular No. 2022-004 & 2003-007)**:
  - **₱50,000.00 Capitalization Threshold**: Equipment `< ₱50,000.00` is recognized as **Semi-Expendable Property** (issued via Inventory Custodian Slip / ICS) and is statutorily non-depreciable (`salvage = ₱0.00`, `depreciation = ₱0.00`).
  - **Capitalized PPE Amortization**: Equipment `≥ ₱50,000.00` is capitalized as **Property, Plant, and Equipment (PPE)** and automatically calculates a **10% standard residual value** and straight-line depreciation based on standard COA useful life schedules.
- **Financial Valuation & Residual Accounting**: Straight-line depreciation tracking, dynamic current book value computation, statutory 10% salvage value estimation, consolidated Expenses Ledger, and CSV export capabilities with official COA Classification tags.
- **Real-Time Global Autocomplete Search**: Interactive search bar with instant multi-module suggestions (Assets, Deployments, Work Orders, Locations) with prioritized direct navigation.
- **Enterprise Role-Based Access Control (RBAC)**: Enforced across 5 roles with backend authorization policies, gates, form requests, and branded HTTP 403 Forbidden protection.
- **Security Hardening**: Strict MIME-type document validation, path sanitization, rate-limiting on sensitive export endpoints, and intelligent input normalization.

---

## 👥 Default Test Roles & Credentials

All test accounts are automatically seeded with password: **`password`**

| Role | Username | Email | Permissions Scope |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin` | `admin@bdes.gov` | Full access, user management, financial valuations, reports center |
| **Barangay Captain** | `captain` | `captain@bdes.gov` | Executive oversight, asset retirement approvals, financial audits |
| **BDRRMC Inventory Officer** | `inventory` | `inventory@bdes.gov` | All equipment works **aside from maintenance**: acquisitions, registry, accessories, consumables, field deployments, returns, damage reporting |
| **Maintenance Officer** | `maintenance` | `maintenance@bdes.gov` | Equipment upkeep & maintenance: work orders, preventive schedules, physical inspections, condition rating |
| **Barangay Staff / Viewer** | `staff` | `staff@bdes.gov` | Read-only inventory browsing, submitting asset deficiency reports |

---

## 📖 System Documentation & Manuals

* 👉 **[USER_TESTING_GUIDE.md](file:///c:/Capstone/BDES/USER_TESTING_GUIDE.md)**: Complete step-by-step testing manual for all user roles and security checkpoints.
* 👉 **[PHILIPPINE_GOVERNMENT_ACCOUNTING_STANDARD.md](file:///c:/Capstone/BDES/PHILIPPINE_GOVERNMENT_ACCOUNTING_STANDARD.md)**: Detailed technical and legal reference for COA Circular No. 2022-004, COA Circular No. 2003-007, straight-line depreciation formulas, and capitalization thresholds.

---

## ⚙️ Quick Start Installation (Zero-Configuration Onboarding)

For anyone pulling or cloning this repository on a new computer:

### Option A: Automatic Setup (Recommended)
```bash
composer run setup
php artisan serve
```

### Option B: Manual Setup Steps
```bash
# 1. Install PHP dependencies
composer install

# 2. Setup environment configuration
# On Windows (CMD / PowerShell):
copy .env.example .env
# On Linux / macOS:
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Link storage and compile frontend assets (if modifying UI)
php artisan storage:link
npm install
npm run build

# 5. Start local development server
php artisan serve
```

> **Note on SQLite Database:** The pre-seeded database file (`capstone_db`) is included in the repository and configured by default in `.env.example`. You do not need to configure MySQL or run migrations manually unless you wish to reset the database (`php artisan migrate:fresh --seed`).

Visit `http://127.0.0.1:8000` to log in with any of the seeded accounts above.

---

## 🧪 Automated Testing

Execute the complete feature and unit test suite:

```bash
php artisan test
```

All **310 tests (1,706 assertions)** execute with **100% pass rate (zero failures)** covering full-stack authentication, role authorizations, lifecycle management, financial calculations, archived soft deletes, asset relocations, maintenance work orders, and input hardening.
