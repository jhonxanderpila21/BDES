# Barangay Disaster Assets Management & Monitoring System (BDES)

A modern, robust, and secure Laravel-based enterprise asset management and disaster preparedness logistics system tailored for Barangay Disaster Risk Reduction and Management Committees (BDRRMC) and Local Government Units (LGUs).

---

## 🚀 Key Features

- **Master Asset Registry & 360° Dossier**: Comprehensive lifecycle tracking (available, deployed, under maintenance, out of service, retired, lost) with audit trails, document attachments, and specification history.
- **Field Deployments & Return Check-Ins**: Real-time tracking of emergency equipment dispatch, custody assignments, condition checks on return, and automatic status transitions.
- **Preventive Maintenance & Inspection Hub**: Work order management, maintenance logging, automated schedule recurring intervals, and physical condition inspection ratings.
- **Operational Requirements & Consumables**: Tracking fuel, two-stroke oil, and emergency consumables with automated threshold alerts for low-stock scenarios.
- **Attachments & Accessories**: Fine-grained tracking of hoses, nozzles, blades, harnesses, and safety accessories linked to parent assets.
- **Financial Valuation & Residual Accounting**: Straight-line depreciation tracking, dynamic current book value computation, statutory 10% salvage value estimation, and CSV export capabilities.
- **Enterprise Role-Based Access Control (RBAC)**: Enforced across 6 roles with backend authorization policies, gates, form requests, and branded HTTP 403 Forbidden protection.
- **Security Hardening**: Strict MIME-type document validation, path sanitization, rate-limiting on sensitive export endpoints, and intelligent input normalization.

---

## 👥 Default Test Roles & Credentials

All test accounts are automatically seeded with password: **`password`**

| Role | Username | Email | Permissions Scope |
| :--- | :--- | :--- | :--- |
| **System Administrator** | `admin` | `admin@bdes.gov` | Full access, user management, financial valuations, reports center |
| **Barangay Captain** | `captain` | `captain@bdes.gov` | Executive oversight, asset retirement approvals, financial audits |
| **BDRRMC Officer Member** | `member` | `member@bdes.gov` | Operational editor: deployments, returns, maintenance, reported assets |
| **Inventory & Logistics Officer** | `inventory` | `inventory@bdes.gov` | Operational editor: asset acquisition, accessories, stock management |
| **Maintenance Specialist** | `maintenance` | `maintenance@bdes.gov` | Operational editor: work orders, preventive tasks, inspection ratings |
| **Barangay Staff Member** | `staff` | `staff@bdes.gov` | Read-only inventory browsing, submitting asset deficiency reports |

---

## 📖 Testing & Operational Manual

For complete, step-by-step testing instructions for every role, please refer to:

👉 **[USER_TESTING_GUIDE.md](file:///c:/Capstone/BDES/USER_TESTING_GUIDE.md)**

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
