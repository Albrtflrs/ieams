# IEAMS

IEAMS is an internal financial and business management system for recording income, expenses, clients, suppliers, quotations, retainers, miscellaneous transactions, payments, and financial reports.

## Features

### Authentication and Profiles

- Login and logout for authenticated users.
- Optional public registration controlled by Settings.
- Profile editing for name, email, and avatar.
- Role-based access control for protected business workflows.
- Case-insensitive and whitespace-tolerant role checks.

### Dashboard

- Monthly financial overview.
- Income, direct costs, operating expenses, gross profit, operating profit, and net profit.
- Rolling 12-month trends.
- Expense categories and accounting buckets.
- Recent income and expense transactions.
- Month selection with fallback to the most recent income month when the selected month has no income.

### Summary

- Filterable financial summary by period or custom date range.
- Total revenue, expenses, net profit, gross profit, operating profit, and cash balance.
- Receivables calculation.
- Income and expense category breakdowns.
- Monthly trends, margins, and top clients or suppliers.

### Clients

- Create, view, edit, search, and delete clients.
- Store contact person, phone, email, address, and related income history.
- Client revenue and transaction-count summaries.
- Client selection in income and quotation workflows.

### Suppliers

- Create, view, edit, search, and delete suppliers.
- Store contact person, phone, email, and address.
- Supplier expense and transaction-count summaries.
- Supplier selection in expense workflows.

### Income Transactions

- Create, view, edit, and soft-delete income transactions.
- Record client, category, particulars, delivery date, gross price, deductions, royalty, and amount paid.
- Track payment status, including unpaid and cash-on-hold records.
- Mark income as paid.
- Search and filter by category, status, client, and date range.
- Automatically generate receipt and invoice numbers.
- Trash bin, restore, and permanent deletion for authorized administrators.
- Staff and Viewer accounts can only view their own income records.

### Expense Transactions

- Create, view, edit, and soft-delete expenses.
- Record supplier, category, description, date, amount, status, and receipt number.
- Search and filter by category, status, supplier, period, and date range.
- Automatically generate receipt numbers.
- Trash bin, restore, and permanent deletion for authorized administrators.
- Staff and Viewer accounts can only view their own expense records.

### Miscellaneous Transactions

- Record miscellaneous income and expenses outside the primary workflows.
- Store date, amount, category, description, and reference number.
- Automatic miscellaneous reference numbering.
- Search and filter by type, category, text, and date range.
- Summary of miscellaneous income, expenses, net amount, and count.
- CSV export for Super Admin and Admin users.
- Staff and Viewer accounts can only view their own miscellaneous records.

### Item Catalog

- Admin and Super Admin users can add and delete quotation items.
- Store item name, description, cost price, markup percentage, selling price, and category.
- Items can be selected when preparing quotations.

### Quotations

- Create, view, edit, and delete quotations.
- Add multiple quotation items with quantity, cost, markup, selling price, and totals.
- Automatic quotation numbering and total calculations.
- Client selection and quotation validity dates.
- Status workflow: Draft, Sent, Accepted, Rejected, Expired, and Converted.
- Export individual quotations to PDF or CSV.
- Convert an accepted quotation into an income transaction.
- Prevent conversion when the quotation is not accepted or was already converted.
- Trash bin, restore, and permanent deletion for authorized administrators.
- Only Super Admin, Admin, and Manager users can access quotations.

### Retainers

- Create, view, edit, and delete retainers.
- Track client, reference number, total amount, used amount, remaining balance, status, dates, billing frequency, payment terms, and renewal settings.
- Support allocated hours, overage rates, rollover, SLA tier, services, and contract files.
- Automatic retainer numbering.
- Trash bin, restore, and permanent deletion for authorized administrators.
- Staff and Viewer accounts can only view their own retainers.

### Reports

- Financial report dashboard with configurable periods and date ranges.
- Total income, expenses, and net profit.
- Income and expense breakdowns by category.
- Monthly income and expense trends.
- Top clients by revenue.
- Top suppliers by expenses.
- Recent transaction details.
- Receivables aging with 0-30, 31-60, 61-90, and 90+ day buckets.
- Payables aging with the same buckets.
- CSV and PDF export for Super Admin and Admin users.

### Receivables and Payables

- Dedicated view of outstanding customer receivables and supplier payables.
- Net position calculation.
- Unpaid income and pending or unpaid expenses.
- Aging information for collection and payment follow-up.

### User Management

- Super Admin and Admin users can access user management.
- Create users with the roles Super Admin, Admin, Manager, Staff, or Viewer.
- Edit permitted user accounts and reset passwords.
- Super Admin can delete other user accounts.
- Admin cannot edit or delete a Super Admin.
- Only Super Admin can assign the Super Admin role.
- Users cannot delete their own account.

### Settings

Super Admin and Admin users can configure:

- Company name, address, and tax ID.
- Currency symbol and fiscal year start.
- Default royalty rate and payment terms.
- Income, expense, and miscellaneous categories.
- Invoice, receipt, quotation, and retainer numbering.
- Logo and report branding.
- Database backup path and monthly backup option.
- Allowed IP addresses or wildcard IP ranges.
- Date format and rows per page.
- Public registration.
- Automatic conversion of accepted quotations, where enabled.

### Database Backups

- Download a database backup from Settings.
- Scheduled backup command support is included in the application.
- Backup location is configurable through Settings.

### Audit Logging

- Activity logging is enabled for tracked models.
- Super Admin users can view the audit log.
- Logs include event, description, subject, acting user, timestamps, and changed values.

## Role-Based Access Control

The application uses Laravel policies, Gates, route middleware, and controller checks. Super Admin has a global authorization bypass.

| Permission | Super Admin | Admin | Manager | Staff | Viewer |
| --- | --- | --- | --- | --- | --- |
| Dashboard | Yes | Yes | Yes | Yes | Yes |
| Summary | Yes | Yes | Yes | Yes | Yes |
| View clients and suppliers | All | All | All | All | All |
| Create/edit clients and suppliers | Yes | Yes | Yes | No | No |
| Delete clients and suppliers | Yes | Yes | No | No | No |
| View income and expenses | All | All | All | Own records | Own records |
| Create/edit income and expenses | Yes | Yes | No | No | No |
| Delete income and expenses | Yes | Yes | No | No | No |
| View miscellaneous transactions | All | All | All | Own records | Own records |
| Create/edit miscellaneous transactions | Yes | Yes | Yes | No | No |
| Delete miscellaneous transactions | Yes | Yes | No | No | No |
| View retainers | All | All | All | Own records | Own records |
| Create/edit retainers | Yes | Yes | Yes | No | No |
| Delete retainers | Yes | Yes | No | No | No |
| Quotations | Yes | Yes | Yes | No | No |
| Convert quotation to income | Yes | Yes | Yes | No | No |
| Manage item catalog | Yes | Yes | No | No | No |
| View reports | Yes | Yes | Yes | Yes | Yes |
| Export reports | Yes | Yes | No | No | No |
| View receivables/payables | Yes | Yes | Yes | Yes | Yes |
| Manage users | Yes | Yes, with limits | No | No | No |
| Configure settings | Yes | Yes | No | No | No |
| View audit log | Yes | No | No | No | No |

## Technology Stack

| Layer | Technology |
| --- | --- |
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js |
| Styling | Tailwind CSS |
| Database | MySQL/MariaDB in production; SQLite is configured for tests |
| Charts | Chart.js |
| PDF | DomPDF and Snappy/wkhtmltopdf |
| Routing helpers | Ziggy |
| Image handling | Intervention Image |
| Activity logs | Spatie Laravel Activitylog |
| Build tool | Vite |

## Requirements

- PHP 8.2 or newer
- Composer 2
- Node.js 18 or newer
- npm 9 or newer
- MySQL 8/MariaDB 10.4 or newer for production
- wkhtmltopdf for Snappy PDF exports on supported environments

## Installation

```bash
git clone <repository-url>
cd ieams
composer install
copy .env.example .env
php artisan key:generate
```

Configure the database and application values in `.env`, then run:

```bash
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000` or the URL configured by `APP_URL`.

For local development with Vite and the Laravel server:

```bash
composer run dev
```

## Environment Variables

Important variables include:

```dotenv
APP_NAME=IEAMS
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ieams
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_SECURE_COOKIE=false
```

Set `APP_DEBUG=false`, use HTTPS, configure a secure database account, and review the allowed IP list before production deployment.

## Testing and Quality Checks

Run the backend test suite:

```bash
php artisan test
```

Build the frontend:

```bash
npm run build
```

Run PHP formatting checks:

```bash
vendor/bin/pint --test
```

The test environment uses an in-memory SQLite database and refreshes migrations between tests.

## Important Routes

| Area | Route |
| --- | --- |
| Login | `/login` |
| Dashboard | `/dashboard` |
| Summary | `/summary` |
| Clients | `/clients` |
| Suppliers | `/suppliers` |
| Income | `/income` |
| Expenses | `/expenses` |
| Miscellaneous | `/misc` |
| Quotations | `/quotations` |
| Retainers | `/retainers` |
| Reports | `/reports` |
| Receivables/payables | `/receivables-payables` |
| Users | `/users` |
| Settings | `/settings` |
| Profile | `/profile` |
| Audit log | `/admin/audit-log` |

## Troubleshooting

Clear application caches after changing configuration, routes, or authorization:

```bash
php artisan optimize:clear
php artisan route:clear
php artisan config:clear
```

If a database table is missing:

```bash
php artisan migrate
```

If the frontend is not updating, start Vite with:

```bash
npm run dev
```

Check `storage/logs/laravel.log` for server-side errors and confirm that the configured database credentials are valid.

## License and Support

This is proprietary software for internal client use. Unauthorized copying, distribution, or modification is prohibited.

For support, contact the project maintainer or use the internal development tracker.
