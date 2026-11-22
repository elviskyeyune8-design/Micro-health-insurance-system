# Backend Contract — Micro Health Insurance System (Kampala)

This document is the single source of truth for wiring frontend HTML/PHP views to the PHP backend on branch `backend-controllers`.
**Every `action` path, field `name`, query parameter, and PHP variable below must be used exactly as written.**

- Stack: PHP 8 · MySQL/MariaDB (PDO) · HTML5 · CSS3 · Vanilla JS · XAMPP
- Base URL (local): `http://localhost/micro-health-insurance`
- All relative form `action` examples below assume the view sits inside its own role subfolder (`auth/`, `client/`, `attendant/`, `admin/`). In any template you may also prefix paths with `<?= BASE_URL ?>/...`.

> [!IMPORTANT]
> **Database port:** `config/db.php` connects to MySQL on **port 3307** (this developer's local XAMPP).
> On a **standard XAMPP** install MySQL listens on **3306** — change `define('DB_PORT', '3307');` to `'3306'` in `config/db.php`.

---

## 1. Global Conventions & Helpers

Every PHP view includes the corresponding `fetch_data.php` (which already calls `require_once __DIR__ . '/../config/db.php';` and `requireRole(...)`), or `config/db.php` directly.

| Item | Rule / Helper |
|---|---|
| Bootstrap | Views in `auth/`, `client/`, `attendant/`, `admin/` start with `require_once __DIR__ . '/fetch_data.php';` (no raw SQL needed in views). |
| CSRF | **Every POST form** must include `<?= csrfField() ?>` (renders `<input type="hidden" name="csrf_token">`). All POST handlers run `verifyCsrf()`. GET forms do not need it. |
| Status Alerts | Handlers use Post/Redirect/Get with `?success=<code>` or `?error=<code>`. Render `$alert` (already computed by every `fetch_data.php`, or call `statusMessage()`):<br>`<?php if ($alert): ?><div class="alert alert-<?= e($alert['type']) ?>"><?= e($alert['message']) ?></div><?php endif; ?>` |
| Sticky Inputs | On validation errors, handlers store non-sensitive inputs in `$_SESSION['old']`. Repopulate inputs using `old('field_name')` (already HTML-escaped, cleared after first page render). |
| Output Escaping | Always echo DB/user values with `e($value)`. |
| Money Format | Display UGX amounts with `formatUGX($amount)` → e.g. `UGX 10,500`. In `<input type="number">` use `min="100"` / `step="100"`. Max per transaction: `MAX_TRANSACTION_UGX` (`5,000,000`). |
| Phone Format | Ugandan local mobile format `07XXXXXXXX` (`+2567XXXXXXXX` / `2567XXXXXXXX` is also normalized automatically). HTML: `pattern="07[0-9]{8}" maxlength="10" inputmode="numeric"`. |
| Session Keys | Set after login/registration: `user_id`, `full_name`, `role` (`admin` / `attendant` / `client`), `pharmacy_id` (`int` or `null` for admin), `phone`, `login_at`. |
| Access Control | Protected pages/handlers enforce `requireRole('client')`, `requireRole('attendant')`, or `requireRole('admin')`. |

---

## 2. Authentication (`auth/`)

### 2.1 Login
- **View:** `auth/login.php` (include `require_once __DIR__ . '/fetch_data.php';` — automatically redirects already-logged-in users to their dashboard and sets `$alert`).
- **Form:** `<form method="POST" action="login_process.php">` (or `<?= BASE_URL ?>/auth/login_process.php`)

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `phone` | `tel` | ✔ | `07XXXXXXXX` (or `+2567XXXXXXXX`) |
| `password` | `password` | ✔ | User password |
| `csrf_token` | `hidden` | ✔ | `<?= csrfField() ?>` |

- **Success redirects (by role):**
  - `client` → `../client/dashboard.php?success=welcome`
  - `attendant` → `../attendant/dashboard.php?success=welcome`
  - `admin` → `../admin/dashboard.php?success=welcome`
- **Failure redirect:** `../auth/login.php?error=<code>` (`missing_fields`, `invalid_phone`, `invalid_credentials`, `account_suspended`, `session_expired`, `invalid_request`, `server_error`).

### 2.2 Client Registration
- **View:** `auth/register.php` (include `require_once __DIR__ . '/fetch_data.php';` — sets `$alert` and `$pharmacies` for the `<select>`).
- **Form:** `<form method="POST" action="register_process.php">` (or `<?= BASE_URL ?>/auth/register_process.php`)

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `full_name` | `text` | ✔ | 3–120 chars, letters/spaces/apostrophes/hyphens/dots |
| `phone` | `tel` | ✔ | `07XXXXXXXX`, unique across `users` |
| `nin` | `text` | ✖ | Uganda NIN, 14 alphanumeric chars (`[A-Za-z0-9]{14}`), unique if provided |
| `pharmacy_id` | `select` | ✔ | Active `pharmacy_id` from `$pharmacies` (`value="<?= (int)$p['pharmacy_id'] ?>"`) |
| `password` | `password` | ✔ | Min 6 characters |
| `confirm_password` | `password` | ✔ | Must match `password` |
| `csrf_token` | `hidden` | ✔ | `<?= csrfField() ?>` |

- **Behavior:** Inside a single PDO transaction, creates `users` (`role = 'client'`) and `policies_wallets` (`policy_number = 'MHI-KLA-XXXX'`, `total_contributed = 0.00`, `available_balance = 0.00`), then **auto-logs the client in**.
- **Success redirect:** `../client/dashboard.php?success=registered`
- **Failure redirect:** `../auth/register.php?error=<code>` (`missing_fields`, `invalid_name`, `invalid_phone`, `invalid_nin`, `invalid_pharmacy`, `weak_password`, `password_mismatch`, `phone_taken`, `nin_taken`, `registration_failed`).

### 2.3 Logout
- **Link:** `<a href="<?= BASE_URL ?>/auth/logout.php">` (`GET`)
- **Behavior:** Clears `$_SESSION`, expires cookie, calls `session_destroy()`.
- **Redirect:** `../index.php?success=logged_out`

---

## 3. Client Portal (`client/`)

All client views include `require_once __DIR__ . '/fetch_data.php';` (enforces `requireRole('client')`).

### 3.1 Exposed Variables (`client/fetch_data.php`)

| Variable | Type | Contents |
|---|---|---|
| `$alert` | `?array` | `['type', 'code', 'message']` from `?success=` / `?error=` |
| `$client` | `?array` | `user_id`, `full_name`, `phone`, `nin`, `member_since`, `wallet_id`, `policy_number`, `plan_name`, `total_contributed`, `available_balance`, `wallet_status`, `start_date`, `home_pharmacy_id`, `home_pharmacy`, `home_pharmacy_location`, `home_pharmacy_phone` |
| `$clientStats` | `array` | `deposits_count`, `failed_deposits`, `last_deposit_at`, `claims_count`, `total_covered`, `total_copay` |
| `$contributions` | `array` | Up to 100 deposits (`contribution_id`, `amount`, `payment_method`, `payment_method_label`, `payer_phone`, `transaction_ref`, `status`, `created_at`) |
| `$claims` | `array` | Up to 100 claims (`claim_id`, `prescription_ref`, `prescriber_name`, `diagnosis`, `prescription_notes`, `total_cost`, `covered_amount`, `client_copay`, `status`, `created_at`, `pharmacy_name`, `pharmacy_location`, `attendant_name`) |
| `$latestDeposit` | `?array` | Set when `?ref=<transaction_ref>` is in URL (after a deposit); same columns as `$contributions` rows |
| `$paymentMethods` | `array` | `['MTN_MoMo' => 'MTN Mobile Money', 'Airtel_Money' => 'Airtel Money', 'Cash_Agent' => 'Cash (Agent)']` |
| `$pharmacies` | `array` | Active partner pharmacies (`pharmacy_id`, `name`, `location`, `address`, `phone`) |

### 3.2 Micro-Deposit
- **View:** `client/deposit.php` (or embedded in `client/dashboard.php`)
- **Form:** `<form method="POST" action="deposit_process.php">` (or `<?= BASE_URL ?>/client/deposit_process.php`)

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `amount` | `number` | ✔ | `> 0` and `<= 5,000,000` UGX (decimals allowed up to 2 places; recommended `min="500" step="100"`) |
| `payment_method` | `radio` / `select` | ✔ | Exactly one of `MTN_MoMo`, `Airtel_Money`, `Cash_Agent` (iterate `$paymentMethods`) |
| `payer_phone` | `tel` | ✖ | `07XXXXXXXX` — mobile money / payer number. If omitted or left blank, defaults to `$_SESSION['phone']` |
| `csrf_token` | `hidden` | ✔ | `<?= csrfField() ?>` |

- **Behavior:** Inside a PDO transaction with `FOR UPDATE` lock on `policies_wallets`, generates a unique `transaction_ref` (`MP...` / `AM...` / `CA...`), inserts `contributions` (`status = 'completed'`), and increments both `total_contributed` and `available_balance`. `wallet_id` is resolved from `$_SESSION['user_id']`, never from the form.
- **Success redirect:** `../client/dashboard.php?success=deposit_completed&ref=<transaction_ref>`
- **Failure redirect:** `../client/deposit.php?error=<code>` (`invalid_amount`, `amount_too_large`, `invalid_payment_method`, `invalid_payer_phone`, `wallet_not_found`, `wallet_inactive`, `deposit_failed`).

---

## 4. Pharmacy Attendant Portal (`attendant/`)

All attendant views include `require_once __DIR__ . '/fetch_data.php';` (enforces `requireRole('attendant')`).

### 4.1 Exposed Variables (`attendant/fetch_data.php`)

| Variable | Type | Contents |
|---|---|---|
| `$alert` | `?array` | `['type', 'code', 'message']` from `?success=` / `?error=` (or `client_not_found` / `claim_not_found` / `no_pharmacy_assigned`) |
| `$attendant` | `?array` | `user_id`, `full_name`, `phone`, `pharmacy_id`, `pharmacy_name`, `pharmacy_location`, `pharmacy_address`, `pharmacy_phone`, `license_no` |
| `$search` | `string` | Sanitised `?search=` value (also falls back to `?phone=`) |
| `$lookupClient` | `?array` | Matched client + wallet: `client_id`, `full_name`, `phone`, `nin`, `user_status`, `wallet_id`, `policy_number`, `plan_name`, `total_contributed`, `available_balance`, `wallet_status`, `start_date`, `home_pharmacy` |
| `$canClaim` | `bool` | `true` when `$lookupClient` is active, wallet is active, and attendant has an assigned pharmacy |
| `$lookupClaims` | `array` | Matched client's last 5 claims (`claim_id`, `prescription_ref`, `total_cost`, `covered_amount`, `client_copay`, `status`, `created_at`, `pharmacy_name`) |
| `$lookupContributions` | `array` | Matched client's last 5 completed deposits |
| `$receipt` | `?array` | Populated when `?claim_id=<id>` is present (scoped to attendant's own pharmacy): `claim_id`, `receipt_no` (`CLM-000001`), `prescription_ref`, `prescriber_name`, `diagnosis`, `prescription_notes`, `total_cost`, `covered_amount`, `client_copay`, `status`, `created_at`, `client_id`, `client_name`, `client_phone`, `policy_number`, `plan_name`, `current_balance`, `pharmacy_name`, `pharmacy_location`, `pharmacy_address`, `pharmacy_phone`, `pharmacy_license`, `attendant_name` |
| `$todayStats` | `array` | Today's totals for this pharmacy: `claims_count`, `total_cost`, `total_covered`, `total_copay` |
| `$recentClaims` | `array` | Last 20 claims processed at this pharmacy |

### 4.2 Client Lookup
- **View:** `attendant/lookup.php`
- **Form:** `<form method="GET" action="lookup.php">` (no CSRF token — read-only)

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `search` | `text` | ✔ | Client phone (`07XXXXXXXX`) or policy number (`MHI-KLA-...`, case-insensitive). (`?phone=` is also accepted as a fallback.) |

- Example URL: `attendant/lookup.php?search=0701555111` or `attendant/lookup.php?search=MHI-KLA-2026-00001`.

### 4.3 Prescription Claim Processing
- **Form location:** Rendered inside `attendant/lookup.php` when `$canClaim === true`.
- **Form:** `<form method="POST" action="claim_process.php">` (or `<?= BASE_URL ?>/attendant/claim_process.php`)

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `client_id` | `hidden` | ✔ | `<?= (int) $lookupClient['client_id'] ?>` |
| `prescription_notes` | `textarea` | ✔ | Min 3 chars (up to 2000 chars); medicines & quantities dispensed |
| `total_cost` | `number` | ✔ | `> 0` and `<= 5,000,000` UGX (`min="100" step="100"`) |
| `prescription_ref` | `text` | ✖ | Optional prescription / Rx ref (max 50 chars) |
| `prescriber_name` | `text` | ✖ | Optional prescriber / clinic name (max 120 chars) |
| `diagnosis` | `text` | ✖ | Optional diagnosis (max 255 chars) |
| `csrf_token` | `hidden` | ✔ | `<?= csrfField() ?>` |

- **Co-Payment & Locking Behavior:**
  - `pharmacy_id` and `attendant_id` come strictly from `$_SESSION`.
  - Locks the client's `policies_wallets` row via `SELECT ... FOR UPDATE`.
  - If `available_balance <= 0` → rolls back and redirects to `../attendant/lookup.php?search=<client_phone>&error=insufficient_balance`.
  - Computes `covered_amount = min($available_balance, $total_cost)` and `client_copay = $total_cost - $covered_amount`.
  - Sets claim `status = 'approved'` if `client_copay == 0`, else `'partial'`.
  - Deducts `covered_amount` from `available_balance` and inserts the `claims` record in the same transaction.
- **Success redirect:** `../attendant/receipt.php?claim_id=<new_id>&success=claim_approved` (or `claim_partial`)
- **Failure redirect:** `../attendant/lookup.php?search=<client_phone>&error=<code>` (`no_pharmacy_assigned`, `client_not_found`, `wallet_inactive`, `invalid_prescription`, `invalid_amount`, `amount_too_large`, `insufficient_balance`, `claim_failed`).

---

## 5. Admin Portal (`admin/`)

All admin views include `require_once __DIR__ . '/fetch_data.php';` (enforces `requireRole('admin')`).

### 5.1 Exposed Variables & Report Filters (`admin/fetch_data.php`)

Optional `GET` query filters (for `admin/dashboard.php` or `admin/reports.php`):
`?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD&pharmacy_id=1&payment_method=MTN_MoMo&claim_status=partial`

| Variable | Type | Contents |
|---|---|---|
| `$alert` | `?array` | `['type', 'code', 'message']` from `?success=` / `?error=` |
| `$filters` | `array` | Sanitised `date_from`, `date_to`, `pharmacy_id` (`int\|0`), `payment_method`, `claim_status` |
| `$kpis` | `array` | `total_clients`, `active_wallets`, `total_attendants`, `active_pharmacies`, `total_pharmacies`, `contributions_count`, `total_contributions`, `contributions_today`, `pool_balance`, `claims_count`, `partial_claims`, `total_claims_cost`, `total_covered`, `total_copay`, `new_clients_this_month` |
| `$contributionsByMethod` | `array` | Rows of `payment_method`, `payment_method_label`, `tx_count`, `total_amount` (filtered by date) |
| `$monthlyTrend` | `array` | Last 6 months (`YYYY-MM`): `month`, `contributions`, `covered`, `copay` |
| `$pharmacies` | `array` | All pharmacies + `attendants_count`, `clients_count`, `claims_count`, `total_covered` |
| `$contributionReport` | `array` | Up to 500 filtered deposits (`contribution_id`, `amount`, `payment_method`, `payment_method_label`, `payer_phone`, `transaction_ref`, `status`, `created_at`, `client_name`, `client_phone`, `policy_number`, `home_pharmacy`) |
| `$claimReport` | `array` | Up to 500 filtered claims (`claim_id`, `prescription_ref`, `diagnosis`, `prescription_notes`, `total_cost`, `covered_amount`, `client_copay`, `status`, `created_at`, `client_name`, `client_phone`, `policy_number`, `pharmacy_name`, `attendant_name`) |
| `$reportTotals` | `array` | Totals of filtered reports: `contributions_count`, `contributions_amount`, `claims_count`, `claims_cost`, `claims_covered`, `claims_copay` |
| `$clients` | `array` | Up to 500 clients + wallet & home pharmacy details |
| `$attendants` | `array` | All attendants + pharmacy name & `claims_processed` |
| `$paymentMethods` | `array` | Code => label map for filter dropdowns |
| `$claimStatuses` | `array` | `'approved'`, `'partial'`, `'rejected'` => labels for filter dropdowns |

### 5.2 Add Pharmacy
- **Form:** `<form method="POST" action="add_pharmacy.php">` (or `<?= BASE_URL ?>/admin/add_pharmacy.php`)

| Field `name` | Type | Required | Rules |
|---|---|---|---|
| `pharmacy_name` | `text` | ✔ | 3–120 chars, unique |
| `location_area` | `select` / `text` | ✔ | 2–100 chars (e.g. `Kampala Central Division`, `Kawempe Division`, `Makindye Division`, `Nakawa Division`, `Rubaga Division`) |
| `license_no` | `text` | ✔ | 3–50 chars (`[A-Za-z0-9/\-. ]+`), unique (stored uppercase, e.g. `NDA/RP/2026/0701`) |
| `contact_phone` | `tel` | ✔ | 10 digits starting with `0` (`pattern="0[0-9]{9}"`, landline `0414...` or mobile `07...`) |
| `address` | `text` | ✖ | Optional street/plot address (max 255 chars) |
| `csrf_token` | `hidden` | ✔ | `<?= csrfField() ?>` |

- **Success redirect:** `../admin/dashboard.php?success=pharmacy_added`
- **Failure redirect:** `../admin/dashboard.php?error=<code>` (`missing_fields`, `invalid_pharmacy_name`, `invalid_location`, `invalid_license`, `invalid_contact_phone`, `duplicate_pharmacy`, `pharmacy_failed`).

---

## 6. Quick Reference

| Feature | Method | Action | Fields |
|---|---|---|---|
| Login | POST | `auth/login_process.php` | `phone`, `password`, `csrf_token` |
| Client Registration | POST | `auth/register_process.php` | `full_name`, `phone`, `nin` *(opt)*, `pharmacy_id`, `password`, `confirm_password`, `csrf_token` |
| Logout | GET | `auth/logout.php` | — |
| Micro-Deposit | POST | `client/deposit_process.php` | `amount`, `payment_method`, `payer_phone` *(opt)*, `csrf_token` |
| Client Lookup | GET | `attendant/lookup.php` | `search` *(phone or policy_number)* |
| Prescription Claim | POST | `attendant/claim_process.php` | `client_id`, `prescription_notes`, `total_cost`, `prescription_ref` *(opt)*, `prescriber_name` *(opt)*, `diagnosis` *(opt)*, `csrf_token` |
| Add Pharmacy | POST | `admin/add_pharmacy.php` | `pharmacy_name`, `location_area`, `license_no`, `contact_phone`, `address` *(opt)*, `csrf_token` |

## 7. Demo Accounts (password for all: `password`)

| Role | Phone | Name | Notes |
|---|---|---|---|
| Admin | `0700000001` | System Administrator | — |
| Attendant | `0772100101` | Nakato Sarah | Kisenyi Care Pharmacy |
| Client | `0701555111` | Mukasa John Bosco | Policy `MHI-KLA-2026-00001`, contributed UGX 18,000, balance UGX 10,500 |
| Client | `0782555222` | Namuli Grace | Policy `MHI-KLA-2026-00002`, contributed UGX 15,000, balance UGX 15,000 |
