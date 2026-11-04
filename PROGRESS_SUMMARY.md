# BDES — Comprehensive Progress & Architecture Summary

**Project:** Barangay Disaster Assets Management & Monitoring System (BDES)  
**Stack:** Laravel 13, Blade, Tailwind CSS, Alpine.js, SQLite / MySQL, Vite  
**Status:** Feature-Complete, Enterprise-Hardened, Production-Ready  
**Current Date:** November 2026  

---

## 1. Executive Summary

BDES is a specialized enterprise disaster preparedness logistics and asset management system tailored for Barangay Disaster Risk Reduction and Management Committees (BDRRMC) and Local Government Units (LGUs). The application features 5-tier Role-Based Access Control (RBAC), multi-document proof uploads, automated Philippine Government accounting (COA Circulars No. 2022-004 & 2003-007), integrated spare parts inventory, centralized soft-delete archiving, in-place asset relocations, and real-time autocomplete search.

---

## 2. Core Functional Modules & Architecture

### 2.1. Master Asset Registry & Dossier (`/assets`)
* **Lifecycle State Machine**: Available, Deployed, Under Maintenance, Out of Service, Retired, and Lost.
* **Batch Quantity & Automated SKU**: Prefix derivation based on asset category and classification (`FE-001`, `GEN-002`, `BOT-001`) with collision prevention and multi-record batch creation.
* **360° Dossier View**: Specifications, maintenance log history, deployment logs, attached attachments/accessories, receipt proofs, and scoped audit trail.
* **Executive Retirement Workflow**: Requires justification and authorization by Barangay Captain or System Administrator.

### 2.2. Deployments & Return Check-Ins (`/deployment`)
* **Borrower Dual-Mode**: Barangay Personnel vs. Resident Borrowers (captures valid ID type/number, residential address, contact number).
* **Validation Hardening**: Chronological return validation strictly enforces `return_date >= deployment_date`.
* **Tamper Prevention**: Deployment status cannot be arbitrarily bypassed on edits; returns must flow through the verified return inspection protocol.
* **Condition Grading on Return**: Categorizes condition (`good`, `fair`, `poor`) and automatically transitions asset status back to available or flags for repair.

### 2.3. Preventive Maintenance & Work Orders Hub (`/maintenance`)
* **Record Classification**: Unified table clearly distinguishing Work Orders (`WO`) and Recurring Schedules (`SCHED`) with dedicated `record_type` filtering.
* **Active Orders Filtering**: Dynamic KPI card and dropdown filtering for in-progress, scheduled, and pending work orders while cleanly excluding recurring monitoring schedules.
* **Spare Parts Inventory Integration**: Integrates directly with spare parts (attachments and accessories), automatically deducting consumed stock and calculating total maintenance expenditure.
* **Service Dates Precision**: Distinguishes Last Serviced & Next Due dates (schedules) from Created, Scheduled, Started, and Completed timestamps (work orders).
* **Condition Verification on Completion**: Mandates operational inspection verification before returning equipment to active service.
* **Inspection-to-Work Order Conversion**: One-click generation of work orders directly from failed inspection findings.

### 2.4. Centralized Archived Records (`/archived-records`)
* **Centralized Soft Deletes**: Covers 10 system domains (Assets, Deployments, Returns, Maintenance, Schedules, Inspections, Incident Reports, Operational Requirements, Attachments, Accessories).
* **Multi-Filter Engine**: Filter by source module, record type, deleting actor, and date range.
* **Safe Restoration**: Restores records back to active standing with foreign keys and relationships intact.
* **Permanent Purge**: Administrator-guarded permanent delete with confirmation modal and `force_deleted` audit logging.

### 2.5. Asset Locations & In-Place Relocation (`/locations`)
* **Facility Capacity & Inventory**: Real-time asset counting per warehouse, staging depot, or evacuation facility.
* **In-Place Asset Relocation**: Allows changing an asset's location directly from the location view with instant movement history recording.

### 2.6. Reported Assets & Defect Management (`/reported-assets`)
* **Staff Reporting**: Barangay staff and viewers can submit defect reports with photo evidence.
* **Confirmation & Multi-Personnel Assignment**: Supervisory officers can confirm reported incidents and assign multiple technicians to collaborate on repairs.

### 2.7. Asset Financials & COA Accounting (`/financials/*`)
* **Consolidated Expenses Ledger**: Aggregates procurement, accessories, attachments, consumable restocks, and maintenance outlays into a unified financial ledger.
* **COA Capitalization Threshold**: ₱50,000 threshold enforces Capitalized PPE (10% standard residual value, straight-line depreciation) vs. Semi-Expendable Property (non-depreciable, carried at cost).
* **Proof-of-Cost Receipts**: Secure document dropzone for invoices, quotations, and delivery receipts.

### 2.8. Global Autocomplete Search Bar
* Real-time AJAX suggestion dropdown in the top navigation bar with direct jump navigation across Assets, Deployments, Work Orders, and Physical Locations.

---

## 3. Technical Implementation & Data Layer

* **Database Migrations**: 42 migration files ensuring clean schema progression.
* **Database Seeders**: 17 seeders organized in foreign-key dependency order for zero-friction setup.
* **Domain Models**: 18 Eloquent models with typed relationships, soft deletes, and query scopes.
* **Service Layer**:
  * `AssetLifecycleService`: State transitions and status locks.
  * `AuditTrailService`: Scoped audit logging and event tracking.
  * `DepreciationCalculationService`: COA-compliant depreciation calculations.
  * `MaintenanceScheduleService`: Preventive schedule intervals and overdue derivations.
  * `NotificationService`: Real-time administrative notifications.
  * `ReceiptService`: Multi-document validation and storage.
  * `SkuGenerationService`: Prefix derivation and collision handling.
  * `GeneralSearchService`: Multi-entity autocomplete search engine.
* **Authorization & Policies**: `AssetPolicy`, `DeploymentPolicy`, `MaintenancePolicy`, and `RoleMiddleware` guarding 5 user roles.
* **Zero-Configuration Onboarding**: Default SQLite `capstone_db` at root with auto-discovery, auto-creation of missing framework directories, and pre-compiled frontend assets in `public/build`.

---

## 4. Test Suite Coverage & Quality Assurance

* **Framework**: PHPUnit 12 & Laravel Testing Suite
* **Execution**: In-memory SQLite (`:memory:`)
* **Total Tests**: **310 tests**
* **Total Assertions**: **1,706 assertions**
* **Pass Rate**: **100% Green (0 failures, 0 errors)**
