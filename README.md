
**Benefits:**
- No manual calculations – system auto-calculates.
- Consistent pricing across all quotes.
- Accurate profit tracking.

#### Status Flow
| Status | When It Applies |
| :--- | :--- |
| **Draft** | Quote is being created – not yet sent. |
| **Sent** | Quote has been emailed or printed for client. |
| **Accepted** | Client has accepted the quote. |
| **Rejected** | Client rejected the quote. |
| **Expired** | Valid until date has passed. |
| **Converted** | Quote has been converted to Income. |

#### Convert to Income Process
1. Quote must be **Accepted**.
2. Click **"Convert to Income"**.
3. System creates an Income Transaction with:
   - Client = Quote's client
   - Particulars = "Quotation: QT-XXXX"
   - Gross Price = Quote's total amount
   - Status = Unpaid
4. Quote status changes to **Converted**.
5. User is taken to the Income Edit page to manage payment.

---

### 9. Reports

| Aspect | Description |
| :--- | :--- |
| **Purpose** | Comprehensive financial reporting and analysis. |
| **Business Value** | Understand business performance, prepare financial statements, and export data for external use. |

#### What It Shows

| Section | What It Does |
| :--- | :--- |
| **Summary Cards** | Total Income, Total Expenses, Net Profit. |
| **Income by Category** | Doughnut chart showing revenue distribution. |
| **Expense by Category** | Doughnut chart showing expense distribution. |
| **Monthly Trend** | Line chart showing income vs expenses over time. |
| **Top Clients** | List of top 5 clients by revenue. |
| **Top Suppliers** | List of top 5 suppliers by expenses. |
| **Recent Transactions** | Last 50 transactions with details. |

#### Aging Reports

| Report | Purpose |
| :--- | :--- |
| **Receivables Aging** | Shows outstanding income by age (0-30, 31-60, 61-90, 90+ days). |
| **Payables Aging** | Shows outstanding expenses by age (0-30, 31-60, 61-90, 90+ days). |

**Export Options:**
- **CSV** – For Excel/Google Sheets analysis.
- **PDF** – For printing or sharing.

---

### 10. Receivables & Payables

| Aspect | Description |
| :--- | :--- |
| **Purpose** | Dedicated page for managing what customers owe you and what you owe suppliers. |
| **Business Value** | Never miss a receivable or payable – all outstanding items in one place with aging alerts. |

#### What It Shows

| Card | What It Tells You |
| :--- | :--- |
| **Total Receivables** | All Unpaid + Cash On Hold income. |
| **Total Payables** | All Unpaid + Pending expenses. |
| **Net Position** | Receivables - Payables (your net cash position). |

#### Aging Buckets (Color-Coded)
| Bucket | Color | Risk Level |
| :--- | :--- | :--- |
| 0-30 Days | 🟢 Green | Low risk |
| 31-60 Days | 🟡 Yellow | Medium risk |
| 61-90 Days | 🟠 Orange | High risk |
| 90+ Days | 🔴 Red | Critical risk |

#### Tables
| Table | What It Shows |
| :--- | :--- |
| **Unpaid Invoices** | All income with status = Unpaid or Cash On Hold. |
| **Unpaid Expenses** | All expenses with status = Unpaid or Pending. |

**Action:** Click **"Edit"** on any row to mark it as Paid or update details.

---

### 11. Users

| Aspect | Description |
| :--- | :--- |
| **Purpose** | Manage who can access the system and what they can do. |
| **Business Value** | Ensure data security, prevent unauthorized changes, and delegate responsibilities appropriately. |

#### Roles & What They Can Do

| Role | Permissions |
| :--- | :--- |
| **Super Admin** | Full system access. Can create/delete users, manage settings, view all data. |
| **Admin** | Full access except user management (can't delete Super Admin). |
| **Manager** | Can view all data, create/edit income/expenses, view reports. Can create quotations. Cannot delete. |
| **Staff** | Can view own data, create quotations (using existing items only). Cannot delete or manage users. |
| **Viewer** | View-only access to own data. |

#### Business Value
- **Super Admin** – Maintains system integrity.
- **Admin** – Handles day-to-day operations.
- **Manager** – Oversees teams without full admin access.
- **Staff** – Inputs data but can't modify critical settings.
- **Viewer** – External stakeholders who need to see financials only.

---

### 12. Settings

| Aspect | Description |
| :--- | :--- |
| **Purpose** | Configure the system to match your business needs. |
| **Business Value** | Tailor the system without touching code. |

#### Settings Groups

| Group | What You Can Configure |
| :--- | :--- |
| **General** | Company Name, Address, Tax ID. |
| **Branding** | Upload Logo (custom branding on reports and quotes). |
| **Financial** | Currency Symbol, Fiscal Year Start, Default Royalty Rate, Default Payment Terms. |
| **Default Categories** | Income Categories, Expense Categories, Miscellaneous Categories. |
| **Invoice Numbering** | Invoice Prefix, Next Invoice Number. |
| **Backup** | Backup Path, Enable Monthly Backup, Run Backup Now. |
| **IP Whitelist** | Restrict access to specific IPs (security). |
| **Appearance** | Date Format, Rows per Page, Enable Public Registration. |

#### Business Value
- **Customize branding** – Make the system look like your company.
- **Defaults** – Reduce data entry errors with pre-filled values.
- **Security** – IP whitelist adds an extra layer of security.
- **Backup** – Never lose data with automated backups.

---

## 👥 User Roles & Permissions

| Permission | Super Admin | Admin | Manager | Staff | Viewer |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Dashboard** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Summary** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Income** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Create/Edit Income** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Delete Income** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Expenses** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Create/Edit Expenses** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Delete Expenses** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Clients** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Create/Edit Clients** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Delete Clients** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Suppliers** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Create/Edit Suppliers** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Delete Suppliers** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Retainers** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Create/Edit Retainers** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Delete Retainers** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Quotations** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Create/Edit Quotations** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Delete Quotations** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Convert to Income** | ✅ | ✅ | ✅ | ❌ | ❌ |
| **Reports** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Export Reports** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Receivables/Payables** | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Users Management** | ✅ | ✅ | ❌ | ❌ | ❌ |
| **Settings** | ✅ | ❌ | ❌ | ❌ | ❌ |

---

## 🛠️ Technology Stack

| Layer | Technology | Version |
| :--- | :--- | :--- |
| **Backend Framework** | Laravel | 12.x |
| **Frontend Framework** | Vue 3 | 3.4+ |
| **Routing** | Inertia.js | 1.0+ |
| **Styling** | Tailwind CSS | 3.4+ |
| **Database** | MySQL / MariaDB | 8.0+ / 10.4+ |
| **Charts** | Chart.js | 4.4+ |
| **PDF Generation** | DomPDF | 3.0+ |
| **PHP** | PHP | 8.2+ |
| **Node.js** | Node | 18+ |

---

## 🚀 Installation Guide

### Prerequisites
- **PHP** 8.2 or higher
- **Composer** 2.x
- **Node.js** 18+ and **npm** 9+
- **MySQL** 8.0+ or **MariaDB** 10.4+
- **Web Server** (Apache / Nginx) or use Laravel's built-in server

### Step 1: Clone the Repository

```bash
git clone https://github.com/yourusername/ieams.git
cd ieams

Step 2: Install PHP Dependencies
bash
composer install
Step 3: Set Up Environment
bash
cp .env.example .env
php artisan key:generate
Step 4: Configure Database
Edit .env file:

env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ieams
DB_USERNAME=root
DB_PASSWORD=your_password_here
Step 5: Install Frontend Dependencies
bash
npm install
Step 6: Run Migrations & Seeders
bash
php artisan migrate --seed
Step 7: Generate Ziggy Routes (for frontend routing)
bash
php artisan ziggy:generate
Step 8: Build Frontend Assets
bash
npm run build  # For production
# OR
npm run dev    # For development (with hot reload)
Step 9: Start the Application
bash
php artisan serve
Step 10: Access the Application
URL: http://localhost:8000

Default Super Admin Credentials: (check your seeder)

🔧 Environment Configuration
Key .env Settings
Variable	Purpose	Example
APP_NAME	Application name shown in browser tab	"IEAMS"
APP_ENV	Environment mode (local/production)	"local"
APP_DEBUG	Show/hide debug errors	"true" (local), "false" (prod)
APP_URL	Application base URL	"http://localhost:8000"
DB_HOST	Database server address	"127.0.0.1"
DB_PORT	Database port	"3306"
DB_DATABASE	Database name	"ieams"
DB_USERNAME	Database username	"root"
DB_PASSWORD	Database password	""
SESSION_DOMAIN	Session cookie domain (for subdomains)	".localhost"
SESSION_SECURE_COOKIE	HTTPS only (production)	"true" in prod
🐛 Troubleshooting
Error: Table 'ieams.expense_transactions' doesn't exist
Fix: Run php artisan migrate

Error: Class 'App\Providers\AppServiceProvider' not found
Fix: Run composer dump-autoload

Error: Page not found: ./Pages/Quotations/Index.vue
Fix: Ensure all Vue pages exist in resources/js/Pages/ and restart npm run dev

Error: Ziggy route 'receivables-payables.index' not in route list
Fix: Run php artisan route:clear && php artisan ziggy:generate

Error: 500 Internal Server Error
Check: Laravel logs in storage/logs/laravel.log

Common fixes: Clear cache (php artisan optimize:clear), check .env database credentials.

Error: Vite development server not running
Fix: Run npm run dev in a separate terminal.

📄 License
Proprietary & Confidential
Unauthorized copying, distribution, modification, or use of this software is strictly prohibited. This system is licensed to the client for internal use only.

📞 Support
Email: daboy.itexpert@gmail.com

Internal: Open an issue in the development tracker.

This README is now **comprehensive, detailed, and explains the purpose behind every feature** – making it clear why each module exists and how it adds business value. 🚀