## Project Context

You are implementing a production-grade Laravel 12 application called:

**Concrete Batching Plant Management System (CBPMS)**

Before generating ANY code, you MUST fully read:

* `CBPMS_Claude_Implementation_Spec.md`

This document is the single source of truth.

Do not make assumptions that contradict the specification.

---

# Agent Hierarchy

The implementation must be divided among specialized agents.

## 1. Solution Architect Agent

Responsibilities:

* Read entire specification
* Validate architecture
* Design module boundaries
* Design Laravel folder structure
* Review database design
* Define implementation order

Deliverables:

* Architecture decisions
* Module dependency map
* ERD validation
* Service boundaries

---

## 2. Database Agent

Responsibilities:

* Implement all migrations
* Foreign keys
* Indexes
* Constraints
* Seeders

Requirements:

* Follow exact schema from specification
* Use Laravel migration best practices
* Add cascading rules where appropriate

Deliverables:

* All migration files
* All seeders
* Database documentation

---

## 3. Model Agent

Responsibilities:

Generate:

* Eloquent Models
* Relationships
* Casts
* Accessors
* Mutators
* Scopes

Requirements:

* Strictly follow ERD
* Avoid business logic except model helpers

Deliverables:

* Complete Model Layer

---

## 4. Authentication & Authorization Agent

Responsibilities:

Implement:

* Authentication
* Session management
* RBAC
* Policies
* Gates
* Middleware

Required Roles:

* admin
* accountant
* engineer
* inventory_officer

Deliverables:

* RoleMiddleware
* Auth configuration
* Permission matrix

---

## 5. Business Logic Agent

Responsibilities:

Implement all Services.

Required Services:

* OrderService
* InventoryService
* PayrollService
* TreasuryService
* ReportService
* BackupService

Rules:

* Controllers must remain thin.
* Services contain business logic.
* Financial operations must use transactions.

Deliverables:

* Service classes
* Unit-tested business rules

---

## 6. Customer & Order Agent

Responsibilities:

Implement:

* Customer Module
* Cement Balance Logic
* Order Module
* Concrete Mix Logic
* Weekly Scheduling Integration

Critical Rule:

Operational Orders:

cement_deducted =
cement_per_m3 × quantity_m3

Must validate balance before deduction.

Deliverables:

* Controllers
* Requests
* Views
* Tests

---

## 7. Inventory Agent

Responsibilities:

Implement:

* Inventory Items
* Inventory Movements
* Supplier Purchases
* Stock In
* Stock Out

Requirements:

* Lock records using transactions
* Prevent negative inventory
* Generate alerts

Deliverables:

* Inventory Module
* Supplier Integration

---

## 8. Financial Agent

Responsibilities:

Implement:

* Treasury
* Credits
* Customer Payments
* Supplier Payments
* Expenses
* Land Rent

Requirements:

Every transaction updates:

balance_after

Must maintain auditability.

Deliverables:

* Financial modules
* Ledger integrity

---

## 9. HR Agent

Responsibilities:

Implement:

* Employees
* Attendance
* Deductions
* Payroll

Business Rules:

Normal Hours = 10

Work Day:

08:00 → 18:00

Overtime:

hours_worked - 10

Deliverables:

* HR module
* Payroll engine

---

## 10. Equipment Agent

Responsibilities:

Implement:

Owned Equipment

* Loader
* Mixer
* Service Vehicle
* Pump

Rental Equipment

* Contracts
* Maintenance
* Rent Tracking

Deliverables:

* Equipment subsystem

---

## 11. Notification & Scheduler Agent

Responsibilities:

Implement:

Commands:

* credits:notify-due
* inventory:check-alerts
* credits:mark-overdue
* db:backup

Notifications:

* CreditDueNotification
* InventoryAlertNotification
* BackupFailedNotification

Deliverables:

* Scheduled automation

---

## 12. Reporting Agent

Responsibilities:

Implement all reports.

Required Reports:

1. Customer Balance
2. Supplier Balance
3. Inventory Status
4. Treasury Transactions
5. Equipment Costs
6. Payroll
7. Due Credits
8. Weekly Schedule
9. Monthly Profit
10. Annual Profit

Requirements:

* Excel Export
* Date Filters
* Optimized Queries

Deliverables:

* Report module
* Export classes

---

## 13. UI/UX Agent

Responsibilities:

Implement:

* Arabic RTL Layout
* Tailwind CSS
* Responsive Design
* Dashboard

Requirements:

Use:

lang="ar"
dir="rtl"

Deliverables:

* Complete Blade UI

---

## 14. QA Agent

Responsibilities:

Validate:

* Business rules
* Permissions
* Financial calculations
* Inventory calculations
* Cement deduction logic
* Reports
* Notifications

Required Tests:

* Feature Tests
* Unit Tests
* Integration Tests

Coverage Target:

80%+

Deliverables:

* Automated test suite

---

# Global Rules

1. Read specification before coding.
2. Never skip requirements.
3. Never invent business rules.
4. Follow Laravel 12 best practices.
5. Use Form Requests for validation.
6. Use Services for business logic.
7. Use Policies for authorization.
8. Use database transactions for financial operations.
9. Keep controllers thin.
10. Generate production-ready code.

---

# Implementation Order

Phase 1

* Architecture
* Database
* Authentication
* RBAC

Phase 2

* Customers
* Orders
* Concrete Mixes

Phase 3

* Inventory
* Suppliers
* Purchases

Phase 4

* Treasury
* Credits
* Expenses

Phase 5

* Equipment
* Rentals

Phase 6

* Employees
* Attendance
* Payroll

Phase 7

* Scheduling
* Notifications

Phase 8

* Reports
* Excel Exports

Phase 9

* Dashboard
* UI Polish

Phase 10

* Testing
* Deployment
* Documentation

---

# Success Criteria

The project is considered complete only when:

* Every module from the specification exists.
* Every business rule is implemented.
* Every report works.
* Excel exports work.
* Notifications work.
* Scheduled jobs work.
* Audit logs work.
* Backup system works.
* RBAC works.
* Tests pass.
* Application is deployable on LAN infrastructure.
