# HealthCare Plus - Pharmacy Inventory Management System (PIMS)

A complete web-based Pharmacy Inventory Management System built with **PHP 8.3 & Laravel 11/12**, implementing **FEFO (First Expiry, First Out)** batch-level inventory tracking, Point-of-Sale (POS) checkout with barcode scanning, stock inward receiving, stock adjustments, customer returns, expiry & low-stock alerts, audit logging, and Role-Based Access Control (RBAC).

---

## 🚀 Quick Start

### 1. Start the Application Server
Run the provided helper script:
```bash
./bin/serve
```
The application will be accessible at: **[http://localhost:8000](http://localhost:8000)**

### 2. Demo User Credentials

All accounts use the password: **`password123`**

| Role | Email | Capabilities |
| :--- | :--- | :--- |
| **Administrator** | `admin@pharmacy.com` | Full system access, user management, settings, reports, audits |
| **Pharmacist** | `pharmacist@pharmacy.com` | POS checkout, inventory catalog, returns, expiry alerts |
| **Pharmacy Manager**| `manager@pharmacy.com` | Purchases & inward stock, sales oversight, profit reports |
| **Cashier** | `cashier@pharmacy.com` | Fast POS terminal, customer registration, receipt printing |
| **Inventory Officer**| `inventory@pharmacy.com`| Inward receiving, batch management, stock adjustments, ledgers |
| **Auditor** | `auditor@pharmacy.com` | Read-only compliance audit trail, financial and valuation reports |

---

## 📦 Key Architectural Features

1. **FEFO Inventory & Batch Tracking Engine (`App\Services\InventoryService`)**:
   - Stock is managed through `medicine_batches` rather than a single mutable column.
   - POS checkout automatically allocates from the earliest non-expired batch (`expiry_date ASC`).
   - Batches past expiration date (`expiry_date < today`) are strictly blocked from sale.

2. **Immutable Stock Movement Ledger (`App\Models\StockMovement`)**:
   - Every inventory change (Purchase Inward, POS Sale, Count Adjustment, Damage Write-off, Sales Return) is recorded in an audit ledger.

3. **Interactive Point of Sale (POS) Terminal**:
   - Live barcode scanning and instant autocomplete search.
   - Fast cart stepper with line-item discounts.
   - Split payment support (Cash, Mobile Money, Card) with change calculator.
   - 80mm printable thermal receipt view.

4. **Multi-Item Stock Inward & Procurement**:
   - Dynamic purchase order receiving form with batch numbers, manufacture dates, and expiration dates.

5. **Expiry & Low-Stock Alert System (`App\Services\AlertService`)**:
   - Real-time dashboard badges and dedicated screens for expired items and batches expiring in `<30`, `<60`, and `<90` days.
   - Low-stock reorder warnings when available non-expired stock $\le$ reorder threshold.

6. **Automated Test Suite**:
   Run the test suite with:
   ```bash
   ./bin/artisan test
   ```
