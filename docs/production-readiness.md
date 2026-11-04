# Production readiness

## What can be hosted now

Commonwealth can be hosted as a public information and pre-launch site. Live investment offers, deposits, withdrawals, custody, and subscriptions are not implemented or enabled. Keep `PLATFORM_LIVE_OPERATIONS_ENABLED=false`. Do not present ledger entries as proof of a payment-provider settlement.

In production, investor registration and contact-form submission default to disabled. This avoids collecting account or inquiry data while the published privacy notice is still marked as a draft. The public contact page continues to provide the Telegram contact supplied for this project. On a fresh database, seed the roles and permissions, then create the initial administrator with `php artisan platform:create-super-admin`. This prompts for a new name, email, and confirmed 16-character password; it does not use a built-in credential.

## Required server configuration

- Set `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, and the HTTPS `APP_URL`. Never deploy the local `.env` file or commit production secrets.
- Use a dedicated production database account with least privilege, not the local XAMPP root account. Back up the database and test restoration.
- Configure a real transactional mail provider before enabling account registration, email verification, or password resets. `MAIL_MAILER=log` does not deliver messages.
- Set `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`, `SESSION_HTTP_ONLY=true`, and `SESSION_SAME_SITE=lax` behind HTTPS. Keep Laravel's writable `storage` and `bootstrap/cache` directories private to the application.
- Serve only the Laravel `public` directory; enable HTTPS; build assets with `npm ci` and `npm run build`; install PHP packages with `composer install --no-dev --optimize-autoloader`; run reviewed database migrations with `php artisan migrate --force`; run `php artisan db:seed --class=RolePermissionSeeder --force`; create the first super administrator with the command above; then run `php artisan optimize`.
- Use a strong, unique administrator password and rotate any credentials shared during development.

## Before collecting user data

Complete and approve the Terms, Privacy, and AML/KYC pages for the actual company and operating jurisdictions. They are explicitly marked as drafts and excluded from search indexing. Only then set `PLATFORM_INVESTOR_REGISTRATION_ENABLED=true` and/or `PLATFORM_CONTACT_FORM_ENABLED=true` in production, after configuring mail, retention, access controls, and incident handling.

## Before live financial operations

Do not enable live operations from an environment flag alone. The application currently has no production payment gateway, bank or cryptocurrency settlement integration, investor subscription flow, custody, withdrawal process, jurisdiction eligibility controls, or complete compliance program. Before accepting money or publishing an offer, the responsible business must supply and obtain qualified review of its legal entity, ownership/offer rights, licensing analysis, jurisdictions, provider contracts, AML/KYC program, investor disclosures, reconciliation and refund procedures, security review, and operational approvals. Live operations remain disabled until that readiness work is complete.

