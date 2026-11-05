# International Investment & Asset Management Platform

Laravel 12 application in active development. The project uses server-rendered Blade views, Bootstrap 5, Chart.js, and MySQL/MariaDB. It is one Laravel MVC application; there is no separate frontend application.

## Local development

Requirements: PHP 8.2+, Composer, Node.js/npm, and XAMPP MariaDB.

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Create a database named investment_platform using phpMyAdmin or the MariaDB CLI. Use utf8mb4 with the utf8mb4_unicode_ci collation.
3. Copy .env.example to .env if this is a new checkout. Set the database username/password to match the local XAMPP configuration. The default example assumes root with no password for a local-only XAMPP setup.
4. Run these commands:

   composer install
   php artisan key:generate
   npm install
   npm run build
   php artisan migrate --seed
   php artisan serve

5. Open http://127.0.0.1:8000.

Use npm run dev during asset development. Configure a real mail transport before relying on email verification or password-reset delivery.

## Test suite

Tests are isolated on in-memory SQLite, even though the application uses MySQL/MariaDB locally:

   php artisan test

## Current implementation

- Laravel 12.70.2 on PHP 8.2.
- RBAC roles and permissions for investor, administrator, super administrator, finance manager, and support manager.
- Investor profiles, encrypted profile fields, KYC submission records, and immutable audit log model.
- Investment category taxonomy with no seeded investment offers.
- Opportunity schemas default to draft and require approved legal review plus visible documentation before public listing.
- Append-only double-entry ledger schema and posting service using decimal arithmetic, row locks, balancing by currency, and idempotency fingerprints.
- Investor registration, login, email verification, password reset, role-protected investor/admin dashboards.
- Public opportunity catalog displays only legally approved, documented, published non-demo opportunities.
- Responsive Blade site with a dark, minimal visual direction and a centralized stylesheet.

## Financial and compliance boundary

This repository is a development foundation, not a live financial service. Real money deposits, withdrawals, cryptocurrency custody, investment subscriptions, returns, and market data are not enabled. Do not publish an offer or accept funds until legal ownership/offer documents, jurisdiction eligibility, licensing/registration analysis, KYC/AML controls, provider contracts, security review, and operational approval are complete.

Never store wallet private keys, seed phrases, API secrets, or identity documents in public storage or source control. Payment credentials belong in protected environment secrets. The demo environment must use separate records and must be visibly labelled.

For a separate demo database, set APP_DEMO_MODE=true and DB_DEMO_DATABASE=investment_platform_demo in that deployment. The application switches the default MySQL connection to the demo database, and refuses to boot if live operations are enabled at the same time.

For production and pre-launch setup, follow [docs/production-readiness.md](docs/production-readiness.md). See docs/architecture.md and docs/database-design.md for system boundaries and the planned data model.
# thor
