# Platform Architecture

## Runtime baseline

- Laravel 12 on PHP 8.2, selected because the available XAMPP PHP runtime is 8.2.12. Laravel 13 requires PHP 8.3 or later.
- MySQL/MariaDB via PDO MySQL. Local XAMPP provides MariaDB 10.4.32; production MySQL compatibility must be verified separately.
- Server rendered Laravel MVC with Blade; Bootstrap 5 and Chart.js are integrated through the Laravel asset pipeline. This remains one Laravel application, not a separate frontend project.
- Laravel authentication, policies, form requests, notifications, queues, events/listeners, and service classes are used for their intended responsibilities.

## Application boundaries

- Public site: content and published opportunities only. An opportunity is hidden until an administrator records its legal structure and publishes it.
- Investor portal: authenticated investor profile, KYC state, deposits, ledger-derived balances, investments, withdrawals, analytics, notifications, and support.
- Administrator portal: role- and permission-protected operations. Finance review, KYC review, opportunity management, provider configuration, and support permissions remain distinct.
- Payment adapters: a common provider contract isolates manual wallet verification and third-party gateways. Adapters verify provider signatures and normalize provider events; controllers do not credit accounts.
- Ledger: immutable financial transactions and balanced debit/credit entries are the source of truth. UI balances are derived from posted ledger entries. Corrections use compensating entries; posted entries are never edited or deleted.
- Demonstration mode: seeded, separately labelled sample data and test-only services. Production payment and investing services reject demo records and demo services cannot post to production ledgers.
- Compliance boundary: real offerings and money movement remain disabled until qualified legal/compliance review, approved jurisdiction configuration, provider agreements, and operational controls are in place.

## Initial role model

Roles: investor, administrator, super_administrator, finance_manager, support_manager. Permissions are stored separately from roles. Sensitive financial actions require explicit permissions and audit records; future dual-control approval can be added to deposit/withdrawal transitions.

## Core request flow

1. Route applies authentication, role/policy, rate limit, and CSRF middleware.
2. Form Request validates shape, currency precision, ownership, and permitted transition.
3. Controller delegates to a domain service.
4. Service performs state changes in a database transaction, locks affected rows, enforces idempotency, writes balanced ledger entries, and emits domain events after commit.
5. Provider webhooks are signature-checked, deduplicated, stored, and reconciled asynchronously. Webhook receipt alone never credits funds.
6. Read models calculate the portal from confirmed records and posted ledger entries.

## Folder conventions

- app/Domain: ledger, payments, investments, identity/compliance domain services and value objects.
- app/Models: Eloquent entities and relationships.
- app/Http/Controllers: thin route controllers grouped by public, investor, admin, and webhook concerns.
- app/Http/Requests: authorization and validation.
- app/Policies: object-level access rules.
- app/Events, app/Listeners, app/Notifications: domain side effects.
- database/migrations, database/seeders, database/factories: schema and safe demo fixtures.
- resources/views: public, investor, and admin Blade pages/components.
- resources/css, resources/js, public: centralized navbar stylesheet plus page-specific assets.
- tests/Feature, tests/Unit: auth, authorization, payments, ledger invariants, and demo isolation.

## Delivery sequence

1. Scaffold, local configuration, architecture/schema notes.
2. Identity, roles, investor profiles, audit foundation.
3. Opportunity catalog and public Blade pages; unpublished by default until legal metadata exists.
4. Ledger core, accounts, and balance queries.
5. Deposits and manual wallet review workflow.
6. Provider abstraction, signature-verified webhooks, reconciliation.
7. Investment subscriptions and distribution records.
8. Withdrawals and finance controls.
9. Investor/admin portals, reports, notifications, analytics, support.
10. Separate demo mode, security hardening, and test suite.

## Environment and live operations

Secrets belong only in .env or a secret manager. .env is not committed. Production payment credentials, live webhook routes, wallet addresses, and investment subscriptions remain disabled by default. Local startup instructions and readiness gates are in the root README.