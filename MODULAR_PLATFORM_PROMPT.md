# IEAMS Modular Platform Implementation Prompt

Use this prompt as the product and engineering brief for the next phase of IEAMS.

## Project Context

IEAMS is currently a Laravel 12 and Vue 3/Inertia financial management application. Accounting is the first module and is already implemented as part of the main Laravel application. The long-term goal is to turn IEAMS into a modular business platform that can be offered commercially with different modules enabled per organization.

The architecture should begin as a modular monolith. Do not split the system into microservices unless there is a proven deployment or scaling requirement.

## Product Vision

Build IEAMS as a platform with:

- A shared Core platform.
- Accounting as the first business module.
- Optional modules such as Inventory, CRM, Projects, HR, Procurement, and Document Management.
- A Platform Super Admin who manages the whole product.
- Organization-level administrators who manage only their own company.
- Module activation and subscription controls per organization.
- Strict tenant isolation so one organization can never access another organization's data.

## Current Completed Accounting Scope

Treat the existing Accounting functionality as the first module. Preserve working behavior while improving its boundaries.

Current Accounting features include:

- Dashboard and financial summaries.
- Income transactions and payment status.
- Expense transactions and payment status.
- Miscellaneous income and expenses.
- Clients and suppliers.
- Quotations with item catalog, status flow, exports, and conversion to income.
- Retainers with balances, dates, billing details, and renewal fields.
- Receivables and payables.
- Aging reports.
- CSV and PDF report exports.
- Settings for company information, financial defaults, numbering, branding, backups, registration, and IP access.
- User profiles and avatars.
- Activity/audit logging.
- Trash, restore, and permanent-delete workflows where supported.
- Role-based authorization using policies, Gates, route middleware, and controller checks.

## Required Platform Roles

Separate platform roles from organization roles.

### Platform Super Admin

The highest-level operator. Can:

- Create, edit, suspend, and restore organizations.
- Create and manage organization administrators.
- Enable or disable modules for each organization.
- Manage plans, subscriptions, trials, and module limits.
- View platform-wide operational and audit information.
- Manage global defaults and platform settings.
- Impersonate an organization administrator only through an audited workflow.
- Never bypass tenant isolation silently.

### Organization Admin

Manages one organization only. Can:

- Manage users within the organization.
- Configure organization settings.
- Use enabled modules.
- Manage organization data.
- Assign organization roles allowed by the platform.
- Never view or modify another organization.
- Never enable modules unless the platform grants that capability.

### Organization Roles

Keep these organization roles:

- `organization_admin`
- `manager`
- `staff`
- `viewer`

The existing `super_admin` role must be reviewed and migrated into the correct platform or organization role. Do not create ambiguous roles with the same name at different scopes.

## Tenant Isolation Requirements

Add organization ownership before selling the system to multiple customers.

Create an `organizations` table with fields such as:

- `id`
- `name`
- `slug`
- `status`
- `timezone`
- `currency`
- `created_at`
- `updated_at`

Add `organization_id` to organization-owned tables, including at minimum:

- `users`
- `clients`
- `suppliers`
- `income_transactions`
- `expense_transactions`
- `miscellaneous_transactions`
- `quotations`
- `quotation_items` where appropriate
- `retainers`
- `items`
- `settings`
- `activity_log`

Every organization-owned query, policy, report, export, relationship, and route-model lookup must be scoped to the authenticated user's organization.

Requirements:

- Never trust an organization ID supplied by the browser.
- Resolve the active organization from the authenticated user or an audited platform-admin context.
- Add automated cross-tenant authorization tests.
- Add database indexes and unique constraints that include `organization_id` where appropriate.
- Review soft-delete and restore workflows for tenant boundaries.
- Ensure background jobs include and validate organization context.

## Module Architecture

Create a modular monolith structure without breaking existing behavior:

```text
app/
  Modules/
    Core/
      Models/
      Policies/
      Services/
      Support/
    Accounting/
      Controllers/
      Models/
      Policies/
      Services/
      routes.php
    Inventory/
      Controllers/
      Models/
      Policies/
      Services/
      routes.php
    CRM/
      Controllers/
      Models/
      Policies/
      Services/
      routes.php
```

A gradual migration is preferred. Do not move every existing class in one risky rewrite. First introduce module boundaries, service classes, module registration, and tests. Then move code slice by slice.

Use explicit service classes for cross-module actions. For example:

```text
app/Modules/Accounting/Services/ConvertQuotationToIncome.php
```

Do not make controllers call other module controllers directly.

Use domain events for cross-module reactions, for example:

```text
QuotationAccepted
  -> Accounting creates or prepares a receivable
  -> Notifications sends an alert
  -> CRM updates the customer timeline
```

## Module Activation

Add module management tables:

- `modules`
- `organization_modules`
- `plans`
- `plan_modules`
- `subscriptions` or an equivalent licensing table

Each organization-module record should support:

- organization ID
- module key
- enabled status
- activation date
- expiration date, if applicable
- plan or subscription reference
- configuration JSON, if necessary

Implement a central module service, for example:

```php
module_enabled('accounting')
module_enabled('inventory')
```

Module checks must exist at multiple levels:

- Navigation visibility.
- Route middleware.
- Controller/service authorization.
- Background jobs and exports.

Hiding a menu item is not sufficient security.

## Modules Still Left to Create

### 1. Core Platform Module

Build first because all other modules depend on it:

- Organizations and tenant context.
- Platform Super Admin dashboard.
- Organization lifecycle management.
- Organization users and invitations.
- Module activation.
- Plans, subscriptions, trials, and limits.
- Shared roles and permissions.
- Shared settings.
- Audit logs with organization and platform scope.
- Notifications.
- File storage abstraction.
- System health and failed-job monitoring.

### 2. Inventory Module

- Products and services.
- SKU and barcode support.
- Categories and units.
- Warehouses and locations.
- Stock receiving and stock issuing.
- Stock adjustments and transfers.
- Reorder levels and low-stock alerts.
- Purchase receiving.
- Inventory valuation.
- Stock movement audit trail.
- Integration with Accounting expenses, suppliers, and cost of goods sold.

### 3. CRM Module

- Leads and prospects.
- Customer pipeline.
- Contacts and communication history.
- Follow-up tasks and reminders.
- Customer notes and attachments.
- Customer activity timeline.
- Lead conversion into client records.
- Integration with Accounting clients, quotations, invoices, and receivables.

### 4. Procurement Module

- Purchase requests.
- Approval workflows.
- Supplier quotations.
- Purchase orders.
- Receiving reports.
- Supplier invoices.
- Procurement budgets.
- Integration with Inventory receiving and Accounting payables.

### 5. Project Management Module

- Projects and project templates.
- Tasks and milestones.
- Assignments and deadlines.
- Project budgets.
- Project expenses.
- Billable work and project income.
- Project profitability.
- Integration with Accounting clients, expenses, retainers, and income.

### 6. Human Resources Module

- Employee profiles.
- Departments and positions.
- Attendance.
- Leave requests and approvals.
- Employee documents.
- Payroll preparation.
- Organization-level HR permissions.
- Integration with Accounting for payroll expenses.

### 7. Document Management Module

- Central document library.
- Contracts, receipts, purchase documents, and attachments.
- Document categories and tags.
- Expiration dates and reminders.
- Version history.
- Organization and role-based access.
- Secure download and audit logging.

### 8. Notifications and Workflow Module

- Email notifications.
- In-app notifications.
- Payment reminders.
- Expiring quotation alerts.
- Retainer expiration alerts.
- Low-stock alerts.
- Approval notifications.
- Scheduled reports.
- Notification preferences per user and organization.

## Accounting Refactoring Required

Before adding many modules:

1. Move quotation conversion into an Accounting service class.
2. Move numbering logic into reusable services.
3. Move report calculations into dedicated report services.
4. Centralize settings access and cache invalidation.
5. Centralize tenant and module checks.
6. Replace duplicated role arrays with permission or role helpers.
7. Add policies for every model and export endpoint.
8. Add feature tests for all critical financial workflows.
9. Keep controllers focused on request validation and response formatting.
10. Preserve existing URLs during the migration unless a versioned route is required.

## Commercial Packaging

Support product plans such as:

| Plan | Modules |
| --- | --- |
| Starter | Core + Accounting |
| Operations | Core + Accounting + Inventory |
| Business | Core + Accounting + Inventory + CRM |
| Enterprise | Core + Accounting + Inventory + CRM + Projects + HR + Procurement |

Do not hardcode plan behavior inside individual controllers. Use a central subscription and module service.

## Security Requirements

- Never ship predictable default passwords.
- Require secure password setup or password reset on first login.
- Use production `APP_DEBUG=false`.
- Use HTTPS and secure cookies in production.
- Never expose `.env` or the project root through the web server.
- Scope every query by organization.
- Audit platform-admin actions.
- Protect exports and backups with authorization.
- Validate uploaded files and store them outside public paths when appropriate.
- Add rate limiting to authentication and sensitive actions.
- Test tenant isolation with users from at least two organizations.

## Testing Requirements

Add tests for:

- Platform Super Admin access.
- Organization Admin boundaries.
- Cross-organization data access denial.
- Module enabled and disabled behavior.
- Subscription expiration behavior.
- Accounting authorization matrix.
- Inventory stock calculations.
- CRM-to-Accounting integration.
- Procurement-to-Inventory integration.
- Project profitability calculations.
- Background job organization context.
- Export and backup authorization.
- Audit-log completeness.

Every new module should ship with:

- Unit tests for domain calculations.
- Feature tests for routes and authorization.
- Tenant-isolation tests.
- Validation tests.
- Export tests where exports exist.

## Implementation Rules

- Preserve existing functionality unless a migration explicitly replaces it.
- Use Laravel conventions and existing project patterns.
- Prefer a small, testable service over a large controller.
- Do not introduce a singleton for mutable database settings.
- Use caching for read-heavy settings only with explicit invalidation.
- Do not build microservices prematurely.
- Do not allow module UI visibility to replace backend authorization.
- Do not mix platform-admin permissions with organization-admin permissions.
- Document every schema and permission change.
- Run migrations, tests, linting, and frontend builds after each module slice.

## Definition of Done

A module is ready for commercial use only when:

- It has a clear ownership boundary.
- It is enabled or disabled per organization.
- Its routes and services enforce permissions.
- Its data is tenant-scoped.
- Its critical workflows have automated tests.
- Its exports and files are protected.
- Its migrations work on a clean database.
- Its documentation is updated.
- Its frontend build passes.
- Its audit events are recorded where needed.
