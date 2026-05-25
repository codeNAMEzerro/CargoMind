# Backend Documentation: Cashier POS & Storage

## Introduction
This documentation provides a technical overview of the backend components powering the CargoMind application, specifically focusing on the Cashier Point of Sale (POS) system and the Storage (Inventory) system. The backend is built using the Laravel framework.

---

## 1. Cashier POS Backend (`TransactionController`)

The Point of Sale system is entirely managed by the `TransactionController.php`, which handles the creation, processing, and management of sales transactions, ensuring data integrity during checkouts.

### Key Features & Workflows

- **POS Interface Initialization (`create`)**
  Fetches all active items with available stock (stock > 0) ordered alphabetically, preparing the data for the frontend cashier view.

- **Transaction Processing (`store`)**
  Handles the core payment logic, heavily relying on Laravel's Database Transactions (`DB::transaction`) to ensure atomicity. 
  - Validates cart items, quantities, and stock availability.
  - Calculates subtotals, item-level discounts, global transaction discounts, and change amounts.
  - Dynamically decrements item stock (`$item->decrement()`) in real-time.
  - Generates a unique invoice number via `Transaction::generateInvoiceNumber()`.
  - Creates the main `Transaction` record and its related `TransactionDetail` records.
  - Logs the activity using the `ActivityLog` model.

- **Transaction History (`index`)**
  Retrieves a paginated list of transactions, eager-loading the cashier details. It supports dynamic querying (searching by invoice number and filtering by specific dates).

- **Receipts & Invoices (`receipt`, `downloadPdf`)**
  - Provides a web-based print preview (`receipt`) for customers.
  - Generates downloadable A5-sized PDF invoices using the `Barryvdh\DomPDF` package (`downloadPdf`).

- **"God Mode" / Master Controls (`toggleGodMode`, `edit`, `update`, `destroy`)**
  A special feature restricted strictly to users with the "Master" role. It allows bypassing standard immutable POS rules:
  - **Toggle**: Stores a session variable (`master_god_mode`) to temporarily enable dangerous actions.
  - **Update**: Allows modifying past transactions. Crucially, it dynamically recalculates stock discrepancies (reverting the old stock amount and deducting the newly specified amount) before recalculating transaction totals.
  - **Destroy**: Safely deletes a transaction and iterates through its details to restore the stock of all associated items back to the inventory.

---

## 2. Storage & Inventory Backend (`ItemController`)

The inventory, item pricing, and storage locations are managed by the `ItemController.php`. It oversees the entire lifecycle of products within the store.

### Key Features & Workflows

- **Item Listing & Grid Views (`index`, `inventory`)**
  Displays active items in a standard list (`index`) or visual grid mode (`inventory`). 
  - Implements powerful search filtering across multiple columns (name, SKU, primary rack, secondary rack).
  - Includes a quick `low_stock` filter to identify items with 10 or fewer units remaining.

- **Item Analytics & Details (`show`)**
  An API endpoint returning JSON data for the inventory modal. Restricted to Master and Manager roles.
  - Fetches the exact date and time the item was last sold.
  - Aggregates the last 30 days of sales data for the specific item to render a sales chart.
  - Formats monetary values (price, purchase price) into Rupiah.

- **Item Management (`create`, `store`, `edit`, `update`)**
  - Validates item properties including SKU uniqueness, stock, pricing, and rack placements.
  - **Role-Based Pricing**: Business logic ensures that only "Master" users can view or update the `purchase_price` (harga beli).
  - **Image Processing**: Integrates `Intervention\Image` to compress uploaded item images. Images are automatically scaled to a maximum width of 800px and saved with 75% quality to save storage space. If compression fails, it falls back to direct storage. 
  - Automatically deletes old image files from the public disk when a new image is uploaded during an update.

- **Soft Deletion (`destroy`)**
  To maintain relational data integrity (preventing errors on past transaction receipts), items are never hard-deleted from the database. Instead, the `destroy` method performs a "soft delete" by setting `is_active = false`, hiding the item from the POS and inventory lists.

---

## Conclusion
The backend architecture strongly emphasizes data integrity (using DB transactions for sales), security (role-based access to cost prices and destructive actions), and storage efficiency (image compression). The separation of concerns between `TransactionController` and `ItemController` ensures a clean, maintainable codebase for CargoMind.
