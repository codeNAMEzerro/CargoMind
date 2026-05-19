# Database Entity Relationship Diagram (ERD)

This document describes the database structure for the CargoMind project.

![Database ERD](erd.png)

## Mermaid Diagram

```mermaid
erDiagram
    USERS ||--o{ TRANSACTIONS : "processes as cashier"
    USERS ||--o{ ACTIVITY_LOGS : "performs"
    USERS ||--o{ SESSIONS : "owns"
    
    TRANSACTIONS ||--|{ TRANSACTION_DETAILS : "contains"
    ITEMS ||--o{ TRANSACTION_DETAILS : "is sold in"
    
    ACTIVITY_LOGS }o--o| ITEMS : "logs (polymorphic)"
    ACTIVITY_LOGS }o--o| TRANSACTIONS : "logs (polymorphic)"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "master, manager, karyawan"
        timestamp email_verified_at
        string remember_token
        timestamps timestamps
    }

    ITEMS {
        bigint id PK
        string name
        string sku UK
        text description
        decimal price
        decimal purchase_price
        integer stock
        string rack_primary
        string rack_secondary
        string image
        boolean is_active
        timestamps timestamps
    }

    TRANSACTIONS {
        bigint id PK
        string invoice_number UK
        bigint cashier_id FK
        decimal subtotal
        decimal discount_amount
        string discount_note
        decimal total
        decimal payment_amount
        decimal change_amount
        enum status "completed, voided"
        timestamps timestamps
    }

    TRANSACTION_DETAILS {
        bigint id PK
        bigint transaction_id FK
        bigint item_id FK
        string item_name "Snapshot"
        string rack_location "Snapshot"
        integer quantity
        decimal unit_price
        decimal purchase_price
        decimal discount
        decimal subtotal
        timestamps timestamps
    }

    ACTIVITY_LOGS {
        bigint id PK
        bigint user_id FK
        string action
        text description
        string ip_address
        string model_type
        bigint model_id
        timestamps timestamps
    }

    SETTINGS {
        bigint id PK
        string key UK
        text value
        string label
        string type
        timestamps timestamps
    }

    SESSIONS {
        string id PK
        bigint user_id FK
        string ip_address
        text user_agent
        longtext payload
        integer last_activity
    }

    PASSWORD_RESET_TOKENS {
        string email PK
        string token
        timestamp created_at
    }
```

## Entity Descriptions

### USERS
Stores application users and their roles. Roles include `master` (owner), `manager`, and `karyawan` (staff).

### ITEMS
Contains inventory items, including their pricing (`price` and `purchase_price`), stock levels, and physical storage locations (`rack_primary`, `rack_secondary`).

### TRANSACTIONS
Records sales transactions. Each transaction is linked to a `cashier_id` (User) and includes financial totals and status.

### TRANSACTION_DETAILS
Stores line items for each transaction. It denormalizes data like `item_name` and `purchase_price` at the time of sale to ensure historical accuracy.

### ACTIVITY_LOGS
Audit trail for system actions. Uses `model_type` and `model_id` for polymorphic tracking of changes to items, transactions, etc.

### SETTINGS
Key-value store for application configuration and metadata.

### System Tables
*   **SESSIONS**: Manages user session state.
*   **PASSWORD_RESET_TOKENS**: Handles password recovery.
