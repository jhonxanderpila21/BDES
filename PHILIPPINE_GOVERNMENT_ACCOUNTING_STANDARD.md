# Philippine Government Accounting Standard (COA & GAM for LGUs)
## Automated Depreciation & Salvage Value Implementation Guide

This document provides complete documentation on how the **Barangay Disaster Assets Management & Monitoring System (BDES)** implements the official accounting rules mandated by the **Commission on Audit (COA)** and the **Government Accounting Manual (GAM) for Local Government Units (LGUs)**.

---

## 1. Legal and Regulatory Framework

The financial valuation and straight-line depreciation engine in BDES is built strictly upon the following statutory issuances:

1. **COA Circular No. 2022-004 (May 31, 2022)**:
   * *Subject*: Guidelines on the Implementation of the Increase in the Capitalization Threshold for Tangible Property, Plant, and Equipment (PPE) from ₱15,000.00 to **₱50,000.00**.
   * Amends COA Circular No. 2005-002 and aligns with Section 4, Chapter 10 of GAM for LGUs Volume I.
2. **COA Circular No. 2003-007**:
   * Mandates setting aside a standard residual value of at least **ten percent (10%)** of the original acquisition cost for tangible equipment, unless an explicit appraised scrap value is established.
3. **Government Accounting Manual (GAM) for LGUs (Volume I, Chapter 10)**:
   * Defines standard straight-line amortization formulas, useful life tables for local disaster response machinery, and classification rules separating **Capitalized PPE** from **Semi-Expendable Property**.

---

## 2. Capitalization Threshold Limit: ₱50,000.00

Under Philippine government standards, equipment is categorized into two distinct classes based on unit acquisition cost:

| Criteria | Semi-Expendable Property | Capitalized Property, Plant, & Equipment (PPE) |
| :--- | :--- | :--- |
| **Unit Acquisition Cost** | **Below ₱50,000.00** (`< ₱50,000.00`) | **₱50,000.00 and above** (`≥ ₱50,000.00`) |
| **Statutory Document** | Issued via **Inventory Custodian Slip (ICS)** | Issued via **Property Acknowledgment Receipt (PAR)** |
| **Depreciation Status** | **NON-DEPRECIABLE (₱0.00)** | **SUBJECT TO DEPRECIATION** |
| **Salvage Value** | **₱0.00 (Non-salvageable)** | **10% Standard Residual** (or appraised scrap) |
| **Amortization** | Expensed upon issuance; carried at original cost | Straight-line amortization over estimated useful life |
| **Book Value** | Retained at original Acquisition Cost | `Acquisition Cost - Accumulated Depreciation` (min. salvage) |

> [!IMPORTANT]
> **Statutory Limitation Enforced**: Equipment with an acquisition cost below ₱50,000.00 **CANNOT have a depreciation schedule or salvage value**. The system automatically sets salvage value to ₱0.00, monthly/annual depreciation to ₱0.00, and rejects any manual attempt to create depreciation schedules or salvage records for semi-expendable assets.

---

## 3. Useful Life Matrix for Barangay Disaster Assets

BDES automatically assigns standard useful life estimates in years based on COA GAM for LGUs schedules:

| Asset Category / Type | Standard Useful Life | Target Equipment Examples |
| :--- | :--- | :--- |
| **Transportation & Rescue Vehicles** | **7 Years** (84 months) | Emergency Ambulances, Rescue Patrol Pickups, Fire Trucks |
| **Emergency Power & Heavy Equipment**| **7 Years** (84 months) | Heavy Diesel Generator Sets, High-Volume Water Pumps |
| **Disaster Rescue & Water Equipment** | **5 Years** (60 months) | Rubber Rescue Boats, Outboard Motors, Chainsaws, Cutters |
| **Communication Devices** | **5 Years** (60 months) | Base Radios, Repeaters, VHF/UHF Handheld Transceivers |
| **Medical & Emergency Equipment** | **5 Years** (60 months) | Automated External Defibrillators (AED), Vital Signs Monitors |
| **IT & Surveillance Equipment** | **5 Years** (60 months) | Operations Center Laptops, CCTV NVRs, Network Switches |
| **Standard Tangible Equipment** | **5 Years** (60 months) | General disaster relief tools, flood barrier deployers |

---

## 4. Mathematical Formulas (Straight-Line Depreciation)

The system automatically calculates depreciation values using the following Straight-Line formulas:

### A. Salvage Value (Residual Value)
$$\text{Salvage Value} = \begin{cases} ₱0.00, & \text{if } \text{Acquisition Cost} < ₱50,000.00 \\ \text{Acquisition Cost} \times 0.10, & \text{if } \text{Acquisition Cost} \geq ₱50,000.00 \text{ (and no custom scrap value)} \end{cases}$$

### B. Depreciable Amount
$$\text{Depreciable Amount} = \begin{cases} ₱0.00, & \text{if } \text{Acquisition Cost} < ₱50,000.00 \\ \max(0, \text{Acquisition Cost} - \text{Salvage Value}), & \text{if } \text{Acquisition Cost} \geq ₱50,000.00 \end{cases}$$

### C. Annual Depreciation
$$\text{Annual Depreciation} = \begin{cases} ₱0.00, & \text{if } \text{Acquisition Cost} < ₱50,000.00 \\ \frac{\text{Depreciable Amount}}{\text{Useful Life (Years)}}, & \text{if } \text{Acquisition Cost} \geq ₱50,000.00 \end{cases}$$

### D. Monthly Depreciation
$$\text{Monthly Depreciation} = \frac{\text{Annual Depreciation}}{12}$$

### E. Accumulated Depreciation
$$\text{Accumulated Depreciation} = \min(\text{Depreciable Amount}, \min(\text{Useful Life}, \text{Age in Years}) \times \text{Annual Depreciation})$$

### F. Current Carrying Book Value
$$\text{Current Book Value} = \begin{cases} \text{Acquisition Cost}, & \text{if Semi-Expendable } (< ₱50,000) \\ \max(\text{Salvage Value}, \text{Acquisition Cost} - \text{Accumulated Depreciation}), & \text{if Capitalized PPE } (\geq ₱50,000) \end{cases}$$

---

## 5. How Automation Works in BDES

### 1. Equipment Registration (`AssetController@store`)
When an officer or administrator registers equipment:
1. Input normalization strips currency symbols (`₱`, `PHP`, commas, whitespace) from `acquisition_cost`.
2. The system checks the acquisition cost against the ₱50,000 statutory limit:
   * **If `< ₱50,000.00`**:
     * `salvage_value` is automatically set to `0.00`.
     * `salvage_notes` is populated with: *"Semi-Expendable Property (< ₱50,000.00 statutory threshold under COA Circular No. 2022-004 / GAM for LGUs). Non-depreciable; residual value set to ₱0.00."*
     * Useful life accessors return 0 for annual/monthly depreciation.
   * **If `≥ ₱50,000.00`**:
     * If `salvage_value` is left blank, the system automatically computes `acquisition_cost * 0.10` (10% standard residual).
     * `salvage_notes` is populated with: *"Standard 10% residual value applied per COA Circular No. 2003-007 / GAM for LGUs."*
     * If `useful_life_years` is left blank, the system automatically assigns the COA schedule (7 years for vehicles/heavy machinery, 5 years for rescue/communication/medical tools).

### 2. Updating Equipment Acquisition Cost (`AssetController@update`)
* If an asset cost is updated from below ₱50,000 to ₱50,000 or above, BDES automatically promotes it to **Capitalized PPE** and applies standard 10% residual value and useful life schedules.
* If an asset cost is reduced below ₱50,000, BDES automatically reclassifies it to **Semi-Expendable Property**, zeroes out the salvage value, and discontinues depreciation.

### 3. Financials & Valuation Modules
* **Depreciation Schedule (`/financials/depreciation`)**:
  * Displays COA Classification badges: `PPE` (green) vs `Semi-Expendable` (amber).
  * Semi-expendable assets display `Non-Depreciable` indicators and carry original cost.
  * Form submissions attempting to add depreciation parameters to semi-expendable assets are rejected with a flash error citing COA Circular No. 2022-004.
* **Salvage Value Records (`/financials/salvage`)**:
  * Displays salvage determination method: `COA Standard 10% Residual`, `Custom Appraised Valuation`, or `COA Semi-Expendable (₱0.00 Non-Salvageable)`.
  * Manual edits on semi-expendable items are restricted.
* **CSV Audit Export**:
  * Both export sheets include the official `COA Classification` column for government audit and compliance.

---

## 6. Calculation Example Walkthrough

### Example 1: Emergency Ambulance (Capitalized PPE)
* **Acquisition Cost**: `₱1,200,000.00` (≥ ₱50,000)
* **Category**: Transportation & Emergency Vehicles
* **Classification**: **Capitalized PPE (₱50,000+)**
* **Statutory Useful Life**: `7 Years` (84 months)
* **Standard Salvage Value (10%)**: `₱120,000.00` (`1,200,000 * 0.10`)
* **Depreciable Amount**: `₱1,080,000.00` (`1,200,000 - 120,000`)
* **Annual Depreciation**: `₱154,285.71` (`1,080,000 / 7`)
* **Monthly Depreciation**: `₱12,857.14` (`154,285.71 / 12`)
* **After 3 Years**:
  * Accumulated Depreciation: `₱462,857.13`
  * Current Book Value: `₱737,142.87`
* **After 7+ Years (End of Life)**:
  * Current Book Value: `₱120,000.00` (Guaranteed residual floor; never drops below salvage value)

### Example 2: Portable Chainsaw (Semi-Expendable Property)
* **Acquisition Cost**: `₱18,500.00` (< ₱50,000)
* **Category**: Rescue Equipment
* **Classification**: **Semi-Expendable Property (< ₱50,000)**
* **Salvage Value**: `₱0.00` (Legally non-salvageable)
* **Depreciable Amount**: `₱0.00`
* **Annual Depreciation**: `₱0.00`
* **Monthly Depreciation**: `₱0.00`
* **Current Book Value**: `₱18,500.00` (Retained at acquisition cost for inventory control)

