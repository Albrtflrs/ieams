# IEAMS User Guide

IEAMS tracks income, expenses, clients, suppliers, quotations, retainers, and financial reports. The sections below describe each feature currently available in the application and its purpose.

## Operating expenses (OPEX)

There is no separate OPEX module. Use **Expenses** to record operating costs such as electricity, water, internet, rent, and other utility bills. The expense categories include **MONTHLY FIX BILLS**, with a subcategory for the bill type.

The expense form records a supplier, date, amount, category, description, receipt number, payment method, and status. For each bill, select or search for the utility provider, choose **MONTHLY FIX BILLS**, and use the description to identify the service and billing period, for example "Electricity, September 2026, account ending 1234." Set the status to **Unpaid**, **Pending**, or **Paid** as appropriate.

Use **Expenses → Monthly Fixed Bills** to create a recurring monthly schedule. Each active schedule creates an unpaid expense on its due date and continues monthly until it is paused, edited, or removed. Editing changes future generated bills; previously generated expenses remain unchanged. In months without the selected day, the bill is scheduled on the last day of that month. The application scheduler must be running for automatic generation; when the Expenses list is opened, due bills are also generated as a catch-up.

For local development, run `php artisan schedule:work` in a separate terminal alongside the app server. On a production server, configure a cron entry to run `php artisan schedule:run` every minute from the application directory. The scheduled recurring bill job runs daily at 1:00 AM.

## Main features

| Feature | Description | Purpose |
| --- | --- | --- |
| Dashboard | Shows a financial overview, charts, and recent activity. | Review current business performance and recent transactions. |
| Sign in and access control | Signs users in and limits actions according to assigned roles and policies. | Protect financial and administrative data. |
| Income | Records client income, delivery/location details, categories, gross amount, payments, deductions, royalties, receipts, and status. The list supports date, category, status, client type, and client-name filters. | Track sales and service income, paid amounts, and outstanding balances. |
| Expenses | Records supplier expenses with dates, amounts, categories, descriptions, receipt numbers, payment methods, and payment status. Monthly fixed bills can be scheduled to generate transactions. The list can be filtered by period, category, status, and search. | Track operating costs and other outgoing payments. |
| Miscellaneous transactions | Records miscellaneous income or expenses separately from the regular transaction forms. | Capture one-off or unusual financial activity. |
| Clients | Stores client name, a selectable or custom client type, contact details, and address. The list supports type filtering and search across client details. | Maintain client records and distinguish client groups. |
| Suppliers | Stores supplier name and contact details. Supplier search is available when entering or editing an expense. | Maintain payees and link expenses to the relevant supplier. |
| Municipality and barangay selection | Provides cascading municipality and barangay choices from the seeded Aklan location data in the Income form. | Record where an income transaction was delivered or served. |
| Quotations | Creates and manages quotations and their items. Accepted quotations can be converted into income transactions. | Prepare client price offers and carry accepted work into income tracking. |
| Retainers | Tracks retainer records and supports document uploads. | Keep retainer agreements and related records together. |
| Receivables & Payables | Presents outstanding income and expense balances. | See money expected from clients and amounts owed to suppliers. |
| Reports | Provides financial reports with date filters and CSV/PDF exports where available. | Review and share financial results. |
| Receivables Aging | Groups outstanding client balances by age and supports CSV/PDF export for authorized users. | Identify overdue client payments and prioritize collection. |
| Payables Aging | Groups outstanding supplier balances by age and supports CSV/PDF export for authorized users. | Identify overdue bills and prioritize payments. |
| Summary | Shows summarized financial figures and trends. | Get a compact view of income, expenses, and net results. |
| Items | Allows authorized users to manage items used in quotations. | Reuse item details when preparing quotations. |
| Users | Allows administrators to manage application user accounts. | Control who can access IEAMS. |
| Profile | Lets a signed-in user manage their own profile details. | Keep personal account information current. |
| Settings | Allows administrators to configure categories, numbering, display preferences, and other application settings. It also provides logo and database backup actions. | Adapt the application to the organization's workflow. |
| Audit Log | Shows recorded application activity to authorized administrators. | Review changes for accountability and troubleshooting. |

## Deleted records

Income, expenses, quotations, and retainers have trash pages with restore actions. Authorized administrators can permanently delete records from trash. Use restore when a record was removed by mistake.

## Searchable selectors

Income forms search clients by name, and expense forms search suppliers by name. Enter at least two characters; multi-part names can be entered in any order. Results are fetched from the server in small batches instead of loading every client or supplier into the form.

## Access and permissions

Available actions depend on the signed-in user's role and permissions. User management, application settings, selected exports, trash access, and audit logs are restricted to authorized roles.
