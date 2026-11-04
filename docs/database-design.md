# Initial Database Design

All monetary amounts use fixed precision DECIMAL, never binary floating point. The currency is stored with each financial record. The ledger is authoritative; no mutable users.balance field is used.

## Identity and authorization

- users: Laravel identity, verification state, status, password, and session security fields.
- roles: investor, administrator, super administrator, finance manager, support manager.
- permissions: named actions such as deposits.review, withdrawals.approve, opportunities.publish.
- role_user and permission_role: many-to-many grants, unique composite keys.
- investor_profiles: legal name, country, preferred currency, address and profile state; sensitive fields encrypted where appropriate.
- kyc_submissions: provider/reference, status, timestamps, review reason, encrypted minimal data; identity files stored privately.
- audit_logs: actor, action, subject, request correlation ID, IP/user agent, structured before/after metadata with secrets excluded.

## Investment catalog

- investment_categories: name, slug, description, visibility.
- investment_opportunities: category, title, description, structure type, issuer/asset identity, currency, minimum/maximum, duration, risk class, documented return terms, distribution schedule, availability/status, legal review metadata and publish timestamps.
- investment_documents: opportunity, private storage key, type, version, checksum, visibility and uploader. Never serve private documents through a public disk.
- investments: investor, opportunity, principal, currency, status, accepted agreement version/hash, start/maturity dates and ledger transaction reference.
- investment_distributions: investment, amount/currency, distribution date, source document and ledger transaction reference. No generated or guaranteed returns.

## Payment records

- supported_cryptocurrencies: asset ticker/name, precision, active flag.
- blockchain_networks: network code, chain identifier, address validation rules, confirmation policy.
- crypto_wallets: currency/network, encrypted receiving address, status, min/max, required confirmations and effective dates.
- payment_providers: provider key, enabled state and non-secret configuration. Credentials are environment secrets, not database values.
- deposit_requests: investor, unique reference, method, currency/network, requested amount, status, expires_at and lifecycle timestamps.
- payment_transactions: deposit/provider reference, transaction hash, destination, observed amount, network, confirmations, verification source/status, webhook event ID, idempotency key and raw-payload checksum. Unique indexes prevent duplicate transaction hashes within a network and replayed event IDs.
- withdrawal_requests: investor, amount/currency, approved destination reference, status, review/processing/completion metadata and ledger transaction reference.

## Accounting and operations

- financial_accounts: account code, owner (optional), account type, currency, status.
- financial_transactions: UUID, unique reference, transaction type, idempotency key, status, description, actor and posted timestamp.
- ledger_entries: transaction, account, debit/credit direction, positive DECIMAL(24,8) amount, currency, created timestamp. Entries are append-only. A domain service verifies that each posted transaction balances per currency before commit.
- support_tickets and support_messages: investor support workflow with private attachments.
- Laravel notifications table plus domain-specific delivery metadata as needed.
- platform_settings: non-secret configuration with typed keys and audit history. Secrets do not belong here.

## Integrity rules

- Foreign keys use restrictive deletion for financial, legal, and audit records.
- IDs, external references, idempotency keys, network transaction hashes, and provider event IDs have appropriate unique indexes.
- Monetary precision is explicit; currency conversion is a separate evidenced transaction with rate source/time.
- Financial actions use DB transactions and row locks on affected account, request, and opportunity capacity.
- Statuses are transitioned through domain services; controllers cannot set arbitrary terminal states.
- Sensitive identity data is encrypted or minimized. Document paths are private and access is policy checked.
- Demo investor IDs and demo ledger transactions are tagged and isolated; production reports exclude them and demo services cannot write production accounts.
- Opportunity returns and ownership descriptions require referenced legal documents and review state. No seed data represents an actual offer.