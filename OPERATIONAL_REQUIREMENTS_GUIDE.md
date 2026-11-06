# Operational Requirements & Supplies — Data & Field Reference Guide

## 1. Overview & Purpose

The **Operational Requirements & Supplies** module in the Barangay Disaster and Emergency System (BDES) is designed to monitor, track, and manage all consumables, fuel reserves, lubricants, and equipment-specific replenishment needs necessary for emergency readiness.

### Core Objectives:
1. **Prevent Resource Depletion During Disasters**: Ensure generators, rescue vehicles, watercraft, power tools, and medical devices have adequate operational fuel and supplies before, during, and after response missions.
2. **Unified Inventory Monitoring**: Consolidate warehouse bulk supplies (e.g., fuel drums, bulk lubricants) and individual equipment supply allocations into a single, cohesive dashboard without confusing technical divisions.
3. **Automated Low-Stock Diagnostics**: Proactively alert logistics officers and administrators whenever stock levels reach or breach critical minimum thresholds.
4. **Accountability & Auditing**: Maintain a clear trace of restock actions, capacity limits, and operational notes.

---

## 2. Dashboard Summary KPI Cards

At the top of the page, four interactive summary cards provide high-level health indicators for all emergency supplies. Clicking any card filters the table instantly.

| KPI Card | Metric Displayed | Purpose & Action | Filter Triggered |
| :--- | :--- | :--- | :--- |
| **Total Requirements** | Total count of all operational requirements and equipment supplies. | Displays complete inventory footprint. Clicking resets filters to view all entries. | None (clears query) |
| **Adequate Reserves / In Stock** | Count of items where `Current Level > Minimum Safety Threshold`. | Identifies supplies ready for deployment without immediate replenishment. | `?status=adequate` |
| **Low Stock Alert** | Count of items where `Current Level <= Minimum Safety Threshold` or requires maintenance. | Highlights urgent logistics needs requiring procurement or restocking. | `?low=1` |
| **Equipment Monitored** | Total count of unique equipment types and registered machinery with active supply tracking. | Assesses coverage of emergency assets that are monitored. | High-level diagnostic |

---

## 3. Search Bar & Filter Controls

Located directly above the unified inventory table, these controls allow logistics personnel to find specific supplies quickly during emergency operations:

- **Search Input (`search`)**: Performs live substring matching across:
  - Requirement / Supply name (e.g., *"Diesel Fuel"*, *"Synthetic 15W-40"*)
  - Equipment name or machinery code (e.g., *"Heavy Duty Generator"*, *"GEN-001"*)
  - Associated Equipment Type (e.g., *"Chainsaw"*, *"Rescue Boat"*)
  - Operational description notes
- **Equipment / Asset Type Filter (`asset_type`)**: Filters entries assigned to a specific machinery class (e.g., *Generator Set*, *Ambulance*, *Water Pump*, *Power Tool*).
- **Unit of Measure Filter (`unit`)**: Filters by measurement standard (e.g., *Liters*, *Gallons*, *Units*, *Boxes*, *Sets*).
- **Status Filter (`status`)**:
  - `In Stock` / `Monitoring Only` — Sufficient supply level
  - `Low Stock` / `Requires Maintenance` — Urgent attention needed
  - `Under Maintenance` — Equipment/supply undergoing servicing
- **Clear Filters Button**: Appears when any filter or search query is active, allowing a single-click reset to the default view.

---

## 4. Unified Inventory Table Columns

Each record in the unified table displays the following data fields:

### A. Requirement / Supply
- **Item Name**: The common or technical name of the consumable (e.g., *Diesel Fuel High Grade*, *2-Stroke Marine Oil TC-W3*, *Bar and Chain Oil*, *Lithium Battery Pack*).
- **Item Icon**: Visual indicator representing the supply type (e.g., fuel pump, oil can, battery, supply crate).
- **Description / Operational Notes**: Instructions or specifications regarding the handling, storage, or standard usage rate of the supply.

### B. Target Equipment / Type
- **Machinery Linkage**: Identifies which emergency equipment utilizes this requirement:
  - **Specific Equipment Unit**: Displays the unit name and unique asset code (e.g., *Heavy Duty Generator [GEN-REG-01]*). Clicking opens the full Asset Dossier.
  - **Equipment Class**: Indicates general application across an entire category (e.g., *All Generator Sets*, *All Chainsaws*).
  - **General / Multi-Use**: Indicates warehouse bulk supplies available for distribution across multiple departments.

### C. Available Stock (Visual Level Meter)
- **Current vs. Maximum**: Numeric value indicating currently stored volume against storage ceiling (e.g., `120.00 / 200.00 Liters`).
- **Percentage Fill Bar**: Color-coded progress bar providing instantaneous visual feedback:
  - 🟢 **Green (Above Threshold)**: Healthy reserve margin.
  - 🟡 **Amber (Approaching Minimum)**: Moderate stock; replenishment should be planned.
  - 🔴 **Red (At or Below Threshold)**: Critical stock alert; immediate replenishment required.
- **Minimum Buffer (`Min: X.XX`)**: The designated emergency reserve line below which an alert is triggered.
- **Maximum Capacity (`Max: X.XX`)**: Physical container, tank, or storage bay volume limit.

### D. Status
- **Badge Indicator**:
  - `In Stock`: Sufficient supplies available.
  - `Low Stock Alert`: Current level has dropped below the minimum reserve threshold.
  - `Monitoring Only`: Standard periodic monitoring for auxiliary requirements.
  - `Requires Maintenance` / `Under Maintenance`: Mechanical or consumable attention flagged.

### E. Actions (Role-Based Permissions)
- **Restock / Replenish Button** (`fas fa-plus-circle`): Opens a streamlined modal to add volume/quantity to current stock.
- **Edit Button** (`fas fa-edit`): Opens the modal to modify unit thresholds, target equipment, or description.
- **Delete Button** (`fas fa-trash`): Prompts a secure confirmation modal prior to soft-deleting the requirement record.
- **Row Click (View Details)**: Clicking anywhere on the row opens the **Operational Requirement Dossier Modal**.
*(Note: Viewers and Read-Only Staff can view details, but modifying actions are hidden).*

---

## 5. Modal Windows & Detailed Operations

### 1. Requirement Details Modal (Quick Dossier)
- **Visual Progress Diagnostic**: Displays percentage bar, numeric current level, minimum reserve buffer, and maximum tank/storage capacity.
- **Linked Equipment Roster**: Lists all physical asset units registered under this requirement type, displaying:
  - Asset code and asset name (with direct links to their equipment dossier)
  - Station / storage location
  - Physical condition (*Excellent*, *Good*, *Fair*, *Poor*)
  - Deployment status (*Available*, *Deployed*, *Maintenance*)
- **Shortcut Actions**: Quick restock button or filter registry by this equipment type.

### 2. Add Requirement Modal
- **Purpose**: Creates a new consumable specification or warehouse fuel/supply reserve.
- **Fields**:
  - `Equipment Type`: Target machinery category (e.g., *Generator Set*).
  - `Requirement / Supply Name`: Clear descriptive label.
  - `Unit of Measure`: Liters, Gallons, Units, Sets, etc.
  - `Current Level`: Starting inventory amount.
  - `Maximum Capacity`: Total capacity limit.
  - `Minimum Safety Level`: Alert threshold level.
  - `Description`: Usage guidelines, storage bay notes, or vendor details.

### 3. Restock / Add Quantity Modal
- **Purpose**: Records incoming shipments, fuel refuelings, or store replenishments.
- **Fields**:
  - `Quantity to Add`: Numerical volume or piece count.
  - `Supplier / Source`: Source of supplies or barangay procurement partner.
  - `Reference / Invoice #`: Tracking number for audit compliance.
  - `Notes`: Delivery receipt remarks or delivery vehicle info.

### 4. Edit Modal
- **Purpose**: Adjusts operational thresholds if equipment upgrades or container expansions occur.
- **Fields**: Requirement name, unit, maximum capacity, minimum safety level, and operational description.

---

## 6. Access Control & User Roles

| Role | Permissions on Operational Requirements Page |
| :--- | :--- |
| **Administrator** | Full access: View all data, search/filter, restock supplies, add new requirements, edit thresholds, and delete records. |
| **Staff / Viewer** | Read-Only access: View all data, search/filter, open detailed dossier modals; modification buttons (`Add`, `Edit`, `Restock`, `Delete`) are securely hidden and restricted on the backend. |

---

## 7. Database Architecture Summary

The data on this page is served primarily from two related models:

1. **`OperationalRequirement` (`operational_requirements` table)**:
   - Tracks warehouse-level consumables, fuel supplies, lubricants, and cross-equipment operational needs.
   - Linked to `AssetType` and related to physical `Asset` units via category matching.
   - Associated with `OperationalRequirementRestock` for audit logs.
2. **`AssetNeed` (`asset_needs` table)**:
   - Tracks equipment-specific consumable allocations (e.g., dedicated battery specs, specialized fluids).
   - Linked directly to specific `Asset` records.
