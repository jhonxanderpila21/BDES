# Barangay Disaster Assets Management & Monitoring System (BDES)
## Complete Technical Setup & Environment Guide

---

## 📋 System Requirements & Prerequisite Stack

* **PHP**: `>= 8.2` (PHP 8.3 Recommended)
  * Extensions: `pdo_sqlite`, `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `curl`, `fileinfo`
* **Composer**: `>= 2.2`
* **Node.js & NPM**: `>= 18.0` (Optional for running; required only if modifying Tailwind CSS / JavaScript assets)
* **Database**:
  * **SQLite 3** (Default, zero-configuration out-of-the-box via pre-seeded `capstone_db`)
  * **MySQL / MariaDB** `>= 8.0` (Fully supported via `.env` configuration)

---

## 🚀 Quick Start (Zero-Configuration Onboarding)

For anyone cloning or pulling the repository on a fresh machine (Windows, Linux, or macOS):

### Option A: Automatic Setup (Recommended)

Run the automated setup pipeline from the repository root:

```bash
composer run setup
php artisan serve
```

This single command automatically:
1. Installs PHP vendor dependencies (`composer install`)
2. Creates `.env` from `.env.example` (if missing)
3. Generates the application encryption key (`php artisan key:generate`)
4. Links public storage (`php artisan storage:link`)
5. Prepares the database connection to the pre-seeded SQLite database (`capstone_db`)
6. Compiles production assets via Vite

---

### Option B: Step-by-Step Manual Setup

If you prefer executing each step manually:

```bash
# 1. Install Composer dependencies
composer install

# 2. Setup your environment configuration
# On Windows (CMD / PowerShell):
copy .env.example .env
# On Linux / macOS:
cp .env.example .env

# 3. Generate application encryption key
php artisan key:generate

# 4. Create symbolic link for uploaded documents/photos
php artisan storage:link

# 5. Database Setup (Choose One):
# Option 5A: Use the pre-seeded capstone_db included in the repo (Default - No action required)
# Option 5B: Fresh database migration and seed:
php artisan migrate:fresh --seed

# 6. Build frontend assets (Optional if using tracked public/build)
npm install
npm run build

# 7. Launch development server
php artisan serve
```

Visit **`http://127.0.0.1:8000`** in your browser.

---

## 🗄️ Database Architecture & Storage Configuration

### SQLite Configuration (Default)
In `.env`:
```ini
DB_CONNECTION=sqlite
DB_DATABASE=capstone_db
```
* **Auto-Discovery & Fallback**: `config/database.php` and `AppServiceProvider` automatically detect `capstone_db` at the repository root. If a different SQLite path is specified and does not exist, `AppServiceProvider` automatically creates or copies the database file to prevent runtime errors.
* **Storage Framework Directories**: `storage/framework/views`, `storage/framework/cache/data`, `storage/framework/sessions`, and `storage/logs` are monitored at runtime and auto-created if missing.

### MySQL Configuration (Alternative)
If your team prefers a centralized MySQL instance:
1. Update `.env`:
   ```ini
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bdes
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```
2. Create the database in MySQL:
   ```sql
   CREATE DATABASE bdes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Run migrations and seeders:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 👥 Seeded Test Roles & Credentials

All test accounts are automatically seeded with password: **`password`**

| Full Name | Role | Email | Password | Scope & Responsibilities |
| :--- | :--- | :--- | :--- | :--- |
| **System Administrator** | `administrator` | `admin@bdes.gov` | `password` | Superadmin: Full system control, user accounts & soft deletes, expenses ledger, financial valuations, centralized archived records management, audit trails, reports center |
| **Hon. Barangay Captain** | `barangay_captain` | `captain@bdes.gov` | `password` | Executive: Oversight dashboard, asset retirement approvals, high-level reports |
| **BDRRMC Inventory Officer** | `inventory_officer` | `inventory@bdes.gov` | `password` | Operational: Acquisitions, auto-SKU, asset registry, attachments & accessories, supplies & restocks, deployments & returns, location management & asset relocations, incident reporting |
| **Maintenance Officer** | `maintenance_officer` | `maintenance@bdes.gov` | `password` | Maintenance: Work orders, preventive schedules, physical inspections, spare parts inventory consumption, condition rating, repair receipts |
| **Barangay Staff / Viewer** | `viewer` | `staff@bdes.gov` | `password` | Viewer: Read-only inventory browsing, submitting defect incident reports |

---

## 🧪 Automated Testing

Execute the comprehensive unit and feature test suite:

```bash
php artisan test
```

* **Test Suite Metrics**: **310 tests, 1,706 assertions**, 100% green pass rate.
* Runs against an in-memory SQLite database (`:memory:`) configured in `phpunit.xml` without altering development data.

---

## 🛠️ Helpful Artisan Commands

* **Refresh and re-seed database**:
  ```bash
  php artisan migrate:fresh --seed
  ```
* **Clear view and config caches**:
  ```bash
  php artisan view:clear
  php artisan config:clear
  php artisan route:clear
  ```
* **Rebuild frontend assets**:
  ```bash
  npm run build
  ```
