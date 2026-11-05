# Production readiness

## Current operating state

Commonwealth is a public information and pre-launch application. Live investment offers, deposits, withdrawals, custody, and subscriptions are not implemented or enabled. Keep PLATFORM_LIVE_OPERATIONS_ENABLED=false and APP_DEMO_MODE=false. Do not present ledger entries as proof of a payment-provider settlement.

Investor registration is available by default to preserve the requested account creation flow. The production owner should disable PLATFORM_INVESTOR_REGISTRATION_ENABLED until the Privacy, Terms, and AML/KYC pages are approved for the actual business and jurisdictions. A real transactional mail provider is required for verification and password reset delivery. MAIL_MAILER=log does not deliver messages. Contact form submission defaults to disabled in production.

On a fresh database, seed only the roles and permissions, then create the initial administrator with php artisan platform:create-super-admin. This prompts for a new name, email, and confirmed 16-character password; it does not use a built-in credential. When no console is available, set INITIAL_ADMIN_NAME, INITIAL_ADMIN_EMAIL, and INITIAL_ADMIN_PASSWORD as runtime-only environment variables for one deployment. The container creates the first super administrator only; later starts skip provisioning once one exists. Remove the bootstrap variables from the deployment settings after the first successful startup. The normal application seeders create roles, investment categories, and disabled payment catalog entries; they do not create customer balances, deposits, withdrawals, investment transactions, or fake accounts.

## EthioDeploy MySQL configuration

The production default is MySQL. In the EthioDeploy project settings, verify that the MySQL 8 addon belongs to this exact web project and that its injected DATABASE_URL is present. Set:

- APP_ENV=production
- APP_DEBUG=false
- APP_URL=https://commonwealth.ethiodeploy.app
- DB_CONNECTION=mysql
- APP_DEMO_MODE=false
- PLATFORM_LIVE_OPERATIONS_ENABLED=false
- SESSION_DRIVER=database
- SESSION_SECURE_COOKIE=true
- SESSION_ENCRYPT=true
- SESSION_HTTP_ONLY=true
- SESSION_SAME_SITE=lax

Use the addon's DATABASE_URL as injected by EthioDeploy. Do not copy local XAMPP DB_HOST, DB_DATABASE, username, or password into production. Production intentionally prefers DATABASE_URL over DB_URL and refuses SQLite, loopback MySQL, missing MySQL URLs, or demo database overrides. Do not put secrets in source control.

EthioDeploy documents that Laravel 11+ may start on SQLite and that this file is reset on redeploy; it also documents injecting the add-on URL as DATABASE_URL and caching Laravel config after the environment is loaded. That makes SQLite the likely explanation for the prior "migrations succeeded, MySQL editor empty" result, but the actual deployed connection still needs the CLI check below. Check the add-on and web project are the same EthioDeploy project before concluding that is the cause.

## Migrations and private database verification

The reviewed migration files are tracked by Laravel's migrations table. Do not run migrate:fresh, migrate:refresh, db:wipe, or destructive SQL in production. Before switching a live database, take a backup and review the migration output.

In EthioDeploy's Start command setting, use:

    php artisan migrate --force && php artisan platform:database-status && ethiodeploy-start

The diagnostic is a CLI command, not a public URL. It reports the active driver, configured endpoint hostname, connected database, server hostname and version, whether PDO MySQL is loaded, whether a query succeeds, the migration count, and expected tables. In production it exits nonzero unless the connected server identifies as MySQL 8. It never prints the database URL, username, password, or APP_KEY. Keep the Start command at one migration invocation; do not add a second migration command to another startup script. Each subsequent deploy applies only pending migrations through Laravel migration tracking.

After a deploy, review the private deployment logs and confirm all of the following: configured and actual driver mysql, the expected database and add-on host, successful query, a migrations table, and all expected application tables. If the command says MySQL is active but the add-on editor still has no tables, compare the reported database and hostname with the add-on attached to the project. Never infer database connectivity from the public homepage loading.

## Other production settings

- Use a unique, persistent APP_KEY; do not rotate it casually because existing encrypted data and sessions depend on it.
- Use a dedicated database account with least privilege, not the local XAMPP root account. Back up the database and test restoration.
- Production sessions default to encrypted, secure, HTTP-only database sessions. Production boot fails if these protections or persistent database/Redis sessions are disabled.
- Keep storage and bootstrap/cache writable only by the application account. EthioDeploy serves Laravel's public directory and warms configuration, route, and view caches after loading environment values.
- Build assets with npm ci and npm run build; install production PHP packages with composer install --no-dev --optimize-autoloader.
- Set a real transactional mail provider before relying on registration, verification, or password resets.
- Use a strong, unique administrator password and rotate any credentials shared during development.

## Before live financial operations

Do not enable live operations from an environment flag alone. The application currently has no production payment gateway, bank or cryptocurrency settlement integration, investor subscription flow, custody, withdrawal process, jurisdiction eligibility controls, or complete compliance program. Before accepting money or publishing an offer, the responsible business must supply and obtain qualified review of its legal entity, ownership/offer rights, licensing analysis, jurisdictions, provider contracts, AML/KYC program, investor disclosures, reconciliation and refund procedures, security review, and operational approvals. Live operations remain disabled until that readiness work is complete.
