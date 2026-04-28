# CargoMind - Store & Warehouse Management System

CargoMind is a robust Store and Warehouse Management System built with Laravel, designed to streamline inventory tracking, sales processing, and financial reporting for businesses.

## 🚀 Tech Stack

- **Framework:** Laravel 11.0 (PHP 8.2+)
- **Database:** SQLite (Simple, portable, and efficient for this scale)
- **Frontend:** Blade Templates, Vanilla CSS, JavaScript
- **Core Dependencies:**
    - `barryvdh/laravel-dompdf`: For generating professional receipts and PDF invoices.
    - `maatwebsite/excel`: For exporting revenue and inventory reports to Excel.
    - `intervention/image-laravel`: For high-quality item image processing.

---

## 👥 Roles & Permissions

The system uses a role-based access control (RBAC) mechanism:

1.  **Master:**
    - Full system configuration (App name, currency, store info).
    - User management (Create/Delete staff).
    - Access to all modules (Dashboard, Inventory, POS, Reports).
    - **God Mode:** Ability to edit or delete completed transactions with automatic stock correction.
2.  **Manager:**
    - Access to Dashboard and Revenue reports.
    - Full inventory management.
    - POS system access.
    - Cannot manage users or use God Mode.
3.  **Karyawan (Staff):**
    - Daily operations: View/Edit inventory and process sales via POS.
    - No access to financial reports or system settings.

---

## 🛠 Core Features

### 1. Dashboard
- Real-time overview of total items, sales, and revenue.
- Quick links to common operations.

### 2. Inventory Management
- **Item Tracking:** CRUD operations for items including SKU, purchase price, selling price, and stock levels.
- **Rack Location:** Organize items by Primary and Secondary rack locations for efficient warehouse picking.
- **Image Support:** Upload and process item photos for visual identification.
- **Stock Control:** Real-time stock updates synced with sales activities.

### 3. Point of Sale (POS)
- **Dynamic Cart:** Add items to cart with real-time stock validation.
- **Discounts:** Supports both per-item discounts and global transaction-level discounts (with notes).
- **Payment Processing:** Calculate change and finalize sales with unique invoice generation.
- **Outputs:**
    - **Print Receipt:** Thermal-friendly receipt view.
    - **PDF Invoice:** Formal A5 portrait invoice for digital sharing or filing.

### 4. God Mode (Exclusive to Master)
- A specialized administrative tool to correct human errors.
- **Edit Transactions:** Modify transaction dates, quantities, or prices after they are completed.
- **Void/Delete:** Permanently remove transactions.
- **Auto-Sync:** Stock levels are automatically recalculated and returned to inventory when transactions are edited or deleted.

### 5. Reporting & Analytics
- **Revenue Reports:** View sales performance and calculate gross profit (Selling Price - Purchase Price).
- **Data Export:** Download reports in `.xlsx` format for external auditing.

---

## 🔄 Data Flow Documentation

### 1. Authentication Flow
1.  **Input:** User enters email and password.
2.  **Process:** `AuthController` validates credentials against the `users` table.
3.  **Output:** Session created; user redirected to Dashboard. `ActivityLog` records the login event.

### 2. Sales Transaction Flow (POS)
1.  **Selection:** Cashier selects items and adjusts quantities/discounts in the frontend cart.
2.  **Validation:** 
    - Frontend checks if quantity > 0.
    - Backend (`TransactionController@store`) validates item existence, active status, and sufficient stock.
3.  **DB Transaction:**
    - **Decrement Stock:** `items.stock` is reduced for each item.
    - **Create Transaction:** New record in `transactions` with total, payment, and change.
    - **Create Details:** Snapshots of `item_name`, `purchase_price`, `unit_price`, and `rack_location` are stored in `transaction_details`.
4.  **Completion:** `ActivityLog` created; invoice generated; redirect to Receipt page.

### 3. Inventory Update Flow
1.  **Trigger:** Manual edit via `ItemController` or automated via POS.
2.  **Manual Edit:** Manager/Master updates prices or rack locations.
3.  **Automated:** Stock decreases on sale and increases if a transaction is voided via God Mode.

### 4. God Mode Data Correction Flow
1.  **Activation:** Master toggles "God Mode" (stored in Session).
2.  **Correction:** Master edits a completed transaction.
3.  **Reconciliation:**
    - System calculates the difference between old and new quantities.
    - Stock is adjusted in the `items` table based on this difference.
    - Transaction totals and detail snapshots are updated.

---

## 📂 Database Documentation (Schema)

### 1. `users`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint | Primary Key |
| `name` | string | Full name of the user |
| `email` | string | Unique login credential |
| `password` | string | Hashed password |
| `role` | enum | `master`, `manager`, `karyawan` |

### 2. `items`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint | Primary Key |
| `name` | string | Item name |
| `sku` | string | Unique SKU or Barcode |
| `purchase_price` | decimal | Buying price (Cost of Goods Sold) |
| `price` | decimal | Selling price |
| `stock` | integer | Current quantity available |
| `rack_primary` | string | Main storage location |
| `rack_secondary`| string | Alternative storage location |
| `image` | string | Path to item image |
| `is_active` | boolean | Visibility in POS |

### 3. `transactions`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint | Primary Key |
| `invoice_number` | string | Unique (Format: CM{YYYYMMDD}{XXXX}) |
| `cashier_id` | foreignId | Reference to `users.id` |
| `subtotal` | decimal | Sum of all line items after line discounts |
| `discount_amount`| decimal | Global transaction discount |
| `total` | decimal | Final payable amount |
| `payment_amount` | decimal | Amount paid by customer |
| `change_amount` | decimal | Change returned to customer |
| `status` | enum | `completed`, `voided` |

### 4. `transaction_details`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint | Primary Key |
| `transaction_id` | foreignId | Reference to `transactions.id` (Cascade delete) |
| `item_id` | foreignId | Reference to `items.id` (Restrict delete) |
| `item_name` | string | Snapshot of item name at time of sale |
| `purchase_price`| decimal | Snapshot of purchase price for profit calculation |
| `unit_price` | decimal | Selling price at time of sale |
| `quantity` | integer | Number of units sold |
| `discount` | decimal | Discount applied to this line item |
| `subtotal` | decimal | (Price * Qty) - Discount |

### 5. `activity_logs`
| Column | Type | Description |
| :--- | :--- | :--- |
| `id` | bigint | Primary Key |
| `user_id` | foreignId | User who performed the action |
| `action` | string | Action name (e.g., `transaction`, `login`) |
| `description` | text | Human-readable details |
| `model_type` | string | Polymorphic model reference |
| `model_id` | bigint | ID of the affected record |

---

## 🧠 System Logic Details

### Invoicing Logic
Invoices follow a strict format: `CM{YYYYMMDD}{XXXX}`.
- `CM`: Prefix (CargoMind).
- `YYYYMMDD`: Current date.
- `XXXX`: 4-digit daily sequential number.

### Profit Calculation
- **Gross Profit:** Calculated as `SUM(transaction_details.subtotal) - SUM(transaction_details.purchase_price * transaction_details.quantity)`.
- Snapshots in `transaction_details` ensure historical reports remain accurate even if item prices change in the future.

### Helpers
- `setting($key, $default)`: Globally accessible helper to retrieve system configuration from the `settings` table.
- `format_rupiah($amount)`: Standardizes currency formatting (Rp XXX.XXX) throughout the UI.
