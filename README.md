# Barangay Disaster Assets Management & Monitoring System (BDES)

A modern, robust, and secure Laravel-based enterprise asset management and disaster preparedness logistics system tailored for Barangay Disaster Risk Reduction and Management Committees (BDRRMC) and Local Government Units (LGUs).

---

## 🚀 Key Features

- **Master Asset Registry & 360° Dossier**: Comprehensive lifecycle tracking (available, deployed, under maintenance, out of service, retired, lost) with audit trails, document attachments, and specification history.
- **Field Deployments & Return Check-Ins**: Real-time tracking of emergency equipment dispatch, custody assignments, condition checks on return, and automatic status transitions.
- **Preventive Maintenance & Inspection Hub**: Work order management, maintenance logging, automated schedule recurring intervals, and physical condition inspection ratings.
- **Operational Requirements & Consumables**: Tracking fuel, two-stroke oil, and emergency consumables with automated threshold alerts for low-stock scenarios.
- **Attachments & Accessories**: Fine-grained tracking of hoses, nozzles, blades, harnesses, and safety accessories linked to parent assets.
- **Automated Philippine Government Standard Accounting (COA Circular No. 2022-004 & 2003-007)**:
  - **₱50,000.00 Capitalization Threshold**: Equipment `< ₱50,000.00` is recognized as **Semi-Expendable Property** (issued via Inventory Custodian Slip / ICS) and is statutorily non-depreciable (`salvage = ₱0.00`, `depreciation = ₱0.00`).
  - **Capitalized PPE Amortization**: Equipment `≥ ₱50,000.00` is capitalized as **Property, Plant, and Equipment (PPE)** and automatically calculates a **10% standard residual value** and straight-line depreciation based on standard COA useful life schedules.
- **Master Asset Registry & 360° Dossier**: Comprehensive lifecycle tracking (available, deployed, under maintenance, out of service, retired, lost) with audit trails, document attachments, and specification history.
- **Field Deployments & Return Check-Ins**: Real-time tracking of emergency equipment dispatch, custody assignments, condition checks on return, and automatic status transitions.
- **Preventive Maintenance & Inspection Hub**: Work order management, maintenance logging, automated schedule recurring intervals, and physical condition inspection ratings.
- **Operational Requirements & Consumables**: Tracking fuel, two-stroke oil, and emergency consumables with automated threshold alerts for low-stock scenarios.
- **Attachments & Accessories**: Fine-grained tracking of hoses, nozzles, blades, harnesses, and safety accessories linked to parent assets.
- **Financial Valuation & Residual Accounting**: Straight-line depreciation tracking, dynamic current book value computation, statutory 10% salvage value estimation, and CSV export capabilities with official COA Classification tags.
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

## ⚙️ Quick Start Installation

```bash
# 1. Install PHP dependencies
composer install

# 2. Install Node dependencies & compile assets
npm install
npm run build

# 3. Setup environment and database
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# 4. Start local development server
php artisan serve
```

Visit `http://127.0.0.1:8000` to log in with any of the seeded accounts above.

---

## 🧪 Automated Testing

Execute the complete feature and unit test suite:

```bash
php artisan test
```
