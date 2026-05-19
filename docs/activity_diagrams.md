# Activity Diagrams

This document represents the process flows for the most significant use cases in the CargoMind system.

## 1. Sales Transaction (POS) Process

![Sales Transaction Flow](pos_flow.png)

The sales transaction is the most complex process, involving stock validation and atomic database operations.

```mermaid
stateDiagram-v2
    [*] --> OpenPOS: Kasir opens POS page
    OpenPOS --> InputItems: Kasir selects items & quantities
    InputItems --> ValidateStock: System checks item stock
    
    ValidateStock --> StockError: Stock insufficient
    StockError --> InputItems: Adjust quantity
    
    ValidateStock --> CalculateTotal: Stock OK
    CalculateTotal --> InputPayment: Input Payment Amount & Discounts
    InputPayment --> ProcessTransaction: Kasir clicks "Process"
    
    state ProcessTransaction {
        [*] --> StartDBTransaction
        StartDBTransaction --> UpdateStock: Decrement Item Stock
        UpdateStock --> SaveTransaction: Create Transaction Record
        SaveTransaction --> SaveDetails: Save Line Items (Denormalized)
        SaveDetails --> LogActivity: Create Audit Log
        LogActivity --> Commit: Finalize DB Changes
    }
    
    ProcessTransaction --> SuccessResponse: Transaction Complete
    SuccessResponse --> ShowReceipt: Display Receipt/Print
    ShowReceipt --> [*]
```

## 2. Authentication and Authorization Flow
Ensures that only authorized users can access specific parts of the system based on their assigned roles.

```mermaid
stateDiagram-v2
    [*] --> LoginPage: User visits application
    LoginPage --> AttemptLogin: Enter Email & Password
    AttemptLogin --> VerifyCredentials: System check
    
    VerifyCredentials --> LoginFailed: Invalid credentials
    LoginFailed --> LoginPage: Show error message
    
    VerifyCredentials --> LoginSuccess: Valid credentials
    LoginSuccess --> CheckRole: Redirect to Dashboard
    
    state CheckRole {
        [*] --> RoleIdentified
        RoleIdentified --> Karyawan: Access POS & Inventory
        RoleIdentified --> Manager: Access Reports + Karyawan Permissions
        RoleIdentified --> Master: Access All + Settings + God Mode
    }
    
    CheckRole --> [*]
```

## 3. Master God Mode (Transaction Editing)
A specialized flow for the 'Master' user to correct transaction mistakes while maintaining stock integrity.

```mermaid
stateDiagram-v2
    [*] --> TransactionHistory: Master views history
    TransactionHistory --> ToggleGodMode: Request Edit Permissions
    ToggleGodMode --> VerifyMaster: System checks Role
    
    VerifyMaster --> EditForm: God Mode Active
    EditForm --> ModifyData: Master adjusts prices/quantities
    
    ModifyData --> ProcessUpdate: Click Update
    
    state ProcessUpdate {
        [*] --> CalculateStockDiff: New Qty vs Old Qty
        CalculateStockDiff --> UpdateItemStock: Increment/Decrement Stock
        UpdateItemStock --> UpdateRecords: Save Changes
    }
    
    ProcessUpdate --> Success: Update Complete
    Success --> TransactionHistory
```
