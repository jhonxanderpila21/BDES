# Barangay Disaster Assets Management & Monitoring System (BDES)

<<<<<<< HEAD
[![Build Status](https://img.shields.io/badge/tests-164%20passed-success)](https://github.com/)
[![Laravel Framework](https://img.shields.io/badge/Laravel-v12-red.svg)](https://laravel.com)
[![Tailwind CSS](https://img.shields.io/badge/TailwindCSS-v3.4-38bdf8.svg)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

The **Barangay Disaster Assets Management & Monitoring System (BDES)** is an enterprise-grade government asset lifecycle, field deployment, preventive maintenance, financial valuation, and disaster readiness monitoring platform designed for local government units (LGUs).

---

## Key Modules & Capabilities

- **Executive & Reconciled Dashboard**: Real-time KPI counters, clickable status metric cards, Chart.js doughnut category distribution with central cutout count and percentage calculations, unified operational timelines, and recent immutable audit activities.
- **Adjustable Width Navigation Bar**: Draggable real-time sidebar resizer (200px – 420px) with double-click reset and persistent width storage in `localStorage`.
- **Asset Lifecycle & Registry**: Complete equipment dossiers tracking state transitions (*Available, Deployed, Under Maintenance, Out of Service, Lost, Retired*), documents, manuals, and accessories.
- **Operational Requirements & Supplies**: Real-time readiness monitoring for critical disaster consumables (generator fuel, emergency kits, sandbags) with refill and consume workflows.
- **Field Deployments & Returns**: Equipment dispatch tracking with responsible officer, destination, purpose, expected return date, and condition inspections upon return.
- **Maintenance & Work Orders**: Preventive maintenance schedules, technical work orders, priority triage, parts logging, and repair completion.
- **Asset Financials & Straight-Line Depreciation**: Straight-line depreciation engine, statutory residual baseline, custom appraised salvage values, and audit justification notes.
- **Tamper-Evident Audit Trail**: Complete immutable audit logging capturing actor, action, timestamp, and JSON before/after state diffs.
- **Separated Asset Locations**: Dedicated location management across Barangay storage facilities and evacuation centers, accessible across all roles.
- **Enterprise RBAC & Security**: Six specialized roles with backend Policy enforcement (`AssetPolicy`, `MaintenancePolicy`, `DeploymentPolicy`), route-level middleware, and custom HTTP 403 Forbidden protection.

---

## Quick Start & Installation

### Prerequisites
- PHP 8.2 or higher (with `sqlite3`, `mbstring`, `pdo` extensions)
- Composer
- Node.js & NPM

### Setup Instructions

```bash
# 1. Clone repository & navigate to project
cd c:\Capstone\BDES

# 2. Install PHP and JavaScript dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Run migrations and seed default roles and asset records
php artisan migrate:fresh --seed

# 5. Compile assets
npm run build

# 6. Launch development server
php artisan serve
```

Once started, open your web browser at: **`http://localhost:8000`**

---

## Role Connection Matrix (Credentials)

All seeded accounts share the default password: **`password`**

| Role Title | Account Email | Password | Role Identifier | Access Scope |
| :--- | :--- | :--- | :--- | :--- |
| **System Administrator** | `admin@bdes.gov` | `password` | `administrator` | Full system access (Users, Roles, Financials, Reports, Deletion) |
| **Hon. Barangay Captain** | `captain@bdes.gov` | `password` | `barangay_captain` | Executive Admin tier (Sole authority to officially Retire/Decommission assets) |
| **BDRRMC Officer Member** | `member@bdes.gov` | `password` | `bdrrmc_officer` | Operational Editor (Asset registry, field deployments, returns, work orders) |
| **Inventory & Logistics Officer** | `inventory@bdes.gov` | `password` | `inventory_officer` | Logistics Specialist (Consumables, fuel refill/consume, stock replenishment) |
| **Maintenance Specialist** | `maintenance@bdes.gov` | `password` | `maintenance_officer` | Technical Specialist (Preventive schedules, physical inspections, work orders) |
| **Barangay Staff / Viewer** | `staff@bdes.gov` | `password` | `viewer` | Operational Viewer (Read-only search, location directory, audit timeline) |

---

## User-Side Role Testing Guide

For comprehensive, step-by-step instructions on connecting and testing each user role, testing boundary permissions, and verifying workflows, please refer to the dedicated testing guide:

👉 **[USER_TESTING_GUIDE.md](file:///c:/Capstone/BDES/USER_TESTING_GUIDE.md)**

### Quick Testing Highlights per Role:
- **Admin (`admin@bdes.gov`)**: Test sidebar resizing (click & drag right border), manage users at `/users`, calculate depreciation at `/financials/depreciation`, export reports at `/reports`.
- **Barangay Captain (`captain@bdes.gov`)**: Open any asset detail view (`/assets/{id}`) and test the exclusive **Retire / Decommission Asset** workflow with justification reason.
- **BDRRMC Officer (`member@bdes.gov`)**: Register emergency equipment, dispatch a deployment, complete a return, and verify that navigating to `/users` or `/financials/depreciation` is blocked with a custom **HTTP 403 Access Restricted** page.
- **Inventory Officer (`inventory@bdes.gov`)**: Go to `/operational-requirements` and test refilling and consuming generator fuel and emergency supplies.
- **Maintenance Specialist (`maintenance@bdes.gov`)**: Create and complete technical work orders at `/maintenance`, conduct asset inspections, and upload service receipts.
- **Staff Viewer (`staff@bdes.gov`)**: Search and view asset dossiers, check asset locations, and verify that mutation actions are disabled.

---

## Automated Verification & Testing

Execute the automated test suite covering all feature modules, policies, role access controls, and calculation engines:

```bash
# Run all tests (164 tests, 567 assertions)
php artisan test

# Run dedicated role access and security boundary tests
php artisan test --filter=BdrrmcMemberAccessTest

# Run asset straight-line financials test
php artisan test --filter=AssetFinancialsTest
```

---

## Architecture & Security Standards

- **Laravel 12 MVC**: Clean separation of models, policies, form requests, and services.
- **Authorization**:
  - `AssetPolicy`: Controls view, create, update, delete, retire, and document permissions.
  - `MaintenancePolicy`: Controls work order creation, updates, and completion.
  - `DeploymentPolicy`: Controls field dispatches and return processing.
  - `RoleMiddleware`: Restricts route groups (`role:administrator,barangay_captain`).
- **Resilient Error UX**: Custom `resources/views/errors/403.blade.php` with direct navigation recovery.
- **Tailwind CSS & Chart.js**: Fully responsive layout with unified visual hierarchy and accessibility standards.
=======
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
- **Intelligent Autocomplete Search**: Interactive search bar with instant multi-module suggestions (Assets, Deployments, Work Orders, Locations) with prioritized direct navigation.
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
