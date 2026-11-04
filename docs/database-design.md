# Database design notes

The application uses users.role_id to assign a user's primary role. Role permissions are stored through permission_role; there is no role_user pivot in the current schema. Investor identity data is stored in investor_profiles and kyc_submissions.

Financial account balances are derived from posted ledger_entries associated with financial_accounts and financial_transactions; the frontend is not an authority for balances. Investment, deposit, and withdrawal requests have separate records and are not proof of a payment-provider settlement. Live financial execution is disabled until the required payment, custody, reconciliation, and compliance systems are implemented.

Review the migration files in timestamp order before changing this document. Laravel's migrations table tracks applied migrations; use php artisan migrate --force only after taking a backup and reviewing the pending migration list. Never use destructive migration shortcuts against production.
