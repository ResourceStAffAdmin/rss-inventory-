# RSS Inventory System Documentation

## 1. Overview
RSS Inventory is a PHP/MySQL inventory management system with modules for products, stock, purchase orders, suppliers, accountability, and reporting. It uses server-side rendering with simple MVC-style controllers and views, session-based authentication, and PDO for database access.

Key entry points:
- Web entry: [public/index.php](public/index.php)
- CLI migrations: [scripts/migrate.php](scripts/migrate.php)

## 2. Tech Stack
- PHP 8.0+ (uses `str_starts_with` and typed code)
- MySQL (InnoDB, utf8mb4)
- Apache with `.htaccess` rewrites

## 3. Project Structure
```
app/
  Controllers/
  Core/
  Views/
config/
database/
  migrations/
  seeders/
public/
  index.php
  .htaccess
scripts/
  migrate.php
```

## 4. Application Flow
1. HTTP requests are routed through [public/index.php](public/index.php).
2. Environment variables are loaded from `.env` via [app/Core/Env.php](app/Core/Env.php).
3. Routes map to controller actions in [app/Controllers/UiController.php](app/Controllers/UiController.php).
4. Controllers render views using [app/Core/View.php](app/Core/View.php).
5. Database access is handled through [app/Core/Database.php](app/Core/Database.php) using PDO.

## 5. Configuration
Configuration is driven by environment variables. Template: [.env.example](.env.example).

Required variables:
```
APP_ENV=production|development
APP_DEBUG=true|false
APP_URL=https://your-domain.com
APP_TIMEZONE=Asia/Manila

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=your_db_name
DB_USER=your_db_user
DB_PASS=your_db_password
DB_CHARSET=utf8mb4
```

Config readers:
- App settings: [config/app.php](config/app.php)
- DB settings: [config/database.php](config/database.php)

## 6. Authentication and Authorization
- Login is handled by `UiController::authenticate()`.
- Users authenticate against the `employees` table.
- Access is restricted to `employees.role = 'internal'` and `employees.status = 'active'`.
- Auth state is stored in session keys:
  - `auth_employee_id`
  - `auth_employee_name`
  - `auth_employee_role`

## 7. Routes
Defined in [public/index.php](public/index.php).

### 7.1 GET Routes
- `/login`
- `/` (dashboard)
- `/products`
- `/products/new`
- `/categories`
- `/stock`
- `/purchase-orders`
- `/low-stock`
- `/suppliers`
- `/history`
- `/users`
- `/employees/search`
- `/reports`
- `/accountability`
- `/accountability/new`

Dynamic GET routes:
- `/accountability/{id}` -> show accountability record
- `/accountability/{id}/print` -> printable view
- `/users/{id}` -> employee transaction history

### 7.2 POST Routes
- `/login` -> authenticate
- `/logout`
- `/products` -> create product
- `/categories` -> create category
- `/suppliers` -> create supplier
- `/stock` -> create stock movement
- `/purchase-orders` -> create purchase order
- `/accountability` -> create accountability record

Dynamic POST routes:
- `/accountability/{id}/return`
- `/purchase-orders/{id}/send`
- `/purchase-orders/{id}/receive`
- `/purchase-orders/{id}/cancel`

## 8. Database
Migrations are stored in [database/migrations](database/migrations). Use [scripts/migrate.php](scripts/migrate.php) to apply them.

### 8.1 Core Tables (summary)
- `employees` (auth users for the UI)
- `roles`, `users` (system roles and admin users)
- `products`, `categories`, `suppliers`, `customers`
- `locations`, `inventory_balances`, `inventory_movements`
- `purchase_orders`, `purchase_order_items`
- `sales_orders`, `sales_order_items`
- `stock_transfers`, `stock_transfer_items`
- `asset_assignments`, `asset_assignment_items`

Schema definitions:
- [database/migrations/001_create_inventory_schema.sql](database/migrations/001_create_inventory_schema.sql)
- [database/migrations/002_extend_inventory_schema.sql](database/migrations/002_extend_inventory_schema.sql)
- [database/migrations/003_create_employees_table.sql](database/migrations/003_create_employees_table.sql)
- [database/migrations/004_create_asset_accountability_tables.sql](database/migrations/004_create_asset_accountability_tables.sql)

### 8.2 Seed Data
Seed scripts exist in [database/seeders](database/seeders). Use these for demo data only and avoid storing real credentials in production.

## 9. Local Development
1. Install PHP 8.0+ and MySQL.
2. Create a database and user.
3. Copy `.env.example` to `.env` and fill values.
4. Run migrations and seeders:
   ```bash
   php scripts/migrate.php
   ```
5. Serve the app with an Apache or PHP server that points to the `public/` folder.

## 10. Deployment (Hostinger / Apache)
### 10.1 Document Root (recommended)
Set the document root to the `public` directory:
```
/domains/<your-subdomain>/public_html/<your-repo>/public
```

### 10.2 Rewrite Fallback (no document root change)
If you cannot change the document root, the root [.htaccess](.htaccess) forwards static assets to `public/` and routes everything else to `index.php`.

Apache rewrite rules for the public folder are in [public/.htaccess](public/.htaccess).

### 10.3 Environment
Create a `.env` file on the server (do not commit it) and set production values. The app will fail if required values are missing.

## 11. UI Modules
- Dashboard: KPIs, recent activity, stock alerts
- Products: CRUD-like creation and listing
- Categories: category management
- Stock: stock in/out/adjustment movements
- Purchase Orders: create, send, receive, cancel
- Suppliers: supplier management
- Accountability: asset assignment and returns
- Users/Employees: employee search and transaction views
- Reports: summary reports (server-rendered)

## 12. Security Notes
- Do not expose `.env` in the web root.
- Avoid placing `app/`, `config/`, and `database/` directly in public web root when possible.
- Ensure `APP_DEBUG=false` in production.

## 13. Troubleshooting
- **HTTP 500**: Check `error_log` in the hosting file manager.
- **Login fails**: Verify DB credentials and `employees` role/status.
- **Static assets missing**: Confirm document root or `.htaccess` rewrites.

## 14. Architecture Diagram
```mermaid
flowchart TD
    A[Browser] --> B[public/index.php]
    B --> C[Env loader]
    B --> D[Route map]
    D --> E[UiController]
    E --> F[Database (PDO)]
    E --> G[View renderer]
    G --> H[HTML response]
```
