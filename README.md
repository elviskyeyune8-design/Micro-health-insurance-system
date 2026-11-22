# AfyaMicro Kampala — Micro Health Insurance System

A web-based micro health insurance platform designed for low-income pharmacy clients in **Kampala, Uganda**. Clients save small, flexible amounts via **MTN MoMo**, **Airtel Money**, or **Cash Agents** into a personal UGX health wallet and redeem prescribed medicines at accredited partner pharmacies with real-time eligibility verification and automatic co-pay calculation.

---

## Academic Project & Team Metadata

- **Institution:** Makerere University Business School (MUBS), Faculty of Computing and Informatics, Department of Applied Computing
- **Programme:** Bachelor of Business Computing (September 2026)
- **Project Title:** Development of a Web-Based Micro Health Insurance System for Low-Income Pharmacy Clients in Kampala (**AfyaMicro Kampala**)
- **Supervisor:** Mrs. Maria Miiro Kafuko

| Student Name | Registration Number | Phone Contact |
|---|---|---|
| Katongole Erisa | 24/U/05484/PS | +256-767269901 |
| Ekii Jesse Ryan | 24/U/0381 | +256-773190362 |
| Kyeyune Elvis | 24/U/16368/EVE | +256-705711566 |
| Ainemukama Trevor | 24/U/02969/PS | +256-753305903 |
| Lubwama Erick Mpanga | 24/U/06468/PS | +256-706108195 |

---

## Features by User Role

### Public & Authentication (`index.php`, `auth/`)
- **Landing Page (`index.php`):** Overview of the 3-step micro-insurance model, Kampala partner pharmacies strip, and portal links.
- **Client Self-Registration (`auth/register.php` → `auth/register_process.php`):** Creates a `client` account and an active `policies_wallets` record (`0.00` UGX balance, auto-generated `MHI-KLA-XXXX` policy number) in a single atomic PDO transaction, then logs the client in automatically.
- **Unified Role-Based Login (`auth/login.php` → `auth/login_process.php`):** Authenticates via phone number (`07XXXXXXXX` or `+2567XXXXXXXX`) and redirects automatically to the Client, Attendant, or Admin dashboard.

### Client Portal (`client/`)
- **Health Wallet Dashboard (`client/dashboard.php`):** Displays available coverage balance (`formatUGX()`), total contributed, total covered claims, active policy badge, and home pharmacy.
- **Quick Micro-Deposit (`client/deposit_process.php`):** Supports preset UGX pills (`2,000`–`20,000` or custom up to `5,000,000`), channel selection (`MTN_MoMo`, `Airtel_Money`, `Cash_Agent`), optional payer phone (supporting family/relative payments), `FOR UPDATE` wallet locking, and unique transaction reference generation (`MP...` / `AM...` / `CA...`).
- **Activity Ledger:** Tabbed history of deposits (`$contributions`) and prescription claims (`$claims`), plus an active partner pharmacy directory.

### Pharmacy Attendant Counter (`attendant/`)
- **Client Eligibility Lookup (`attendant/dashboard.php`):** Searches clients by phone number or policy ID (`GET ?search=`), showing policy status, available balance, and recent claims.
- **Prescription Claim & Co-Pay Processing (`attendant/claim_process.php`):** Locks the wallet row with `SELECT ... FOR UPDATE`, rejects `available_balance <= 0` with `?error=insufficient_balance`, computes `covered_amount = min(available_balance, total_cost)` and `client_copay = total_cost - covered_amount` with a live JavaScript preview, marks claims `approved` (`client_copay == 0`) or `partial`, and binds `pharmacy_id` / `attendant_id` strictly from `$_SESSION`.
- **Printable Thermal Receipt (`attendant/receipt.php`):** Generates an `80mm` print-ready receipt (`CLM-XXXXXX`) with pharmacy NDA details, prescription notes, cost/co-pay split, remaining wallet balance, and `window.print()` support.

### Scheme Administrator (`admin/`)
- **Live Scheme KPIs (`admin/dashboard.php`):** Pool balance, total contributions, active clients/wallets, partner pharmacies, and claims paid/co-pay totals.
- **Partner Pharmacy Onboarding (`admin/add_pharmacy.php`):** Adds verified Kampala pharmacies with NDA licence uniqueness validation.
- **Audit Reports & Analytics (`admin/fetch_data.php`):** Filterable contribution and claim reports by date range, pharmacy, payment channel, and claim status, plus payment channel breakdowns, 6-month trend summaries, and registered client listings.

---

## Tech Stack & Security Architecture

| Layer | Technology | Notes |
|---|---|---|
| Frontend | HTML5, CSS3, Vanilla JS | Mobile-first responsive UI converted cleanly to [assets/css/style.css](assets/css/style.css) and [assets/js/app.js](assets/js/app.js) (zero build step) |
| Backend | PHP 8 (PDO) | Real prepared statements (`EMULATE_PREPARES = false`), integer-cent UGX arithmetic, Post/Redirect/Get status codes |
| Database | MySQL / MariaDB (InnoDB) | `micro_health_db` (`utf8mb4_unicode_ci`, `+03:00` EAT) with 5 normalized tables, foreign keys, and `CHECK` constraints |
| Server | Apache + MySQL (XAMPP) | Built for low-bandwidth XAMPP deployments |

**Security Controls:**
- Passwords hashed with `password_hash(..., PASSWORD_DEFAULT)` and verified via `password_verify()`.
- Hardened `MHI_SESSID` cookies (`HttpOnly`, `SameSite=Lax`, `use_strict_mode=1`, `session_regenerate_id(true)`).
- Session-bound CSRF tokens (`csrfField()` / `verifyCsrf()`) on every `POST` form.
- Role-based access control (`requireRole()`) and HTML output escaping (`e()`).
- Read/write separation: views only include role-scoped `fetch_data.php` files (direct browser access to `fetch_data.php` returns HTTP `404`), containing zero inline SQL.

---

## Project Structure

```text
micro-health-insurance/
├── index.php                       # Public landing page
├── BACKEND_CONTRACT.md             # Form actions, field names, redirects & fetcher variables
├── admin/
│   ├── add_pharmacy.php            # POST handler: onboard partner pharmacy
│   ├── dashboard.php               # Admin KPIs, pharmacies & filtered audit reports
│   └── fetch_data.php              # Admin queries (KPIs, trends, reports)
├── assets/
│   ├── css/style.css               # Complete responsive & 80mm print stylesheet
│   └── js/app.js                   # Tabs, deposit presets, live co-pay calculator, mobile nav
├── attendant/
│   ├── claim_process.php           # POST handler: FOR UPDATE claim deduction & co-pay split
│   ├── dashboard.php               # Counter client lookup & prescription claim form
│   ├── fetch_data.php              # Attendant queries (lookup, receipt, today's stats)
│   ├── lookup.php                  # Route alias rendering dashboard.php on claim errors
│   └── receipt.php                 # Printable thermal claim receipt
├── auth/
│   ├── fetch_data.php              # Public auth data (active pharmacies, login redirect)
│   ├── login.php                   # Portal login view
│   ├── login_process.php           # POST handler: password_verify() & role routing
│   ├── logout.php                  # Session & cookie teardown
│   ├── register.php                # Client registration view
│   └── register_process.php        # POST handler: transactional signup + MHI-KLA-XXXX wallet
├── client/
│   ├── dashboard.php               # Health wallet, quick micro-deposit & history tabs
│   ├── deposit.php                 # Route alias rendering dashboard.php on deposit errors
│   ├── deposit_process.php         # POST handler: atomic mobile-money/cash contribution
│   └── fetch_data.php              # Client wallet, stats, contributions & claims queries
├── config/
│   ├── db.php                      # PDO connection, session init, CSRF, money & validation helpers
│   ├── messages.php                # ?success= / ?error= code-to-message registry
│   └── ui.php                      # Pure presentation helpers (SVG icons, logo, portal shell)
├── database/
│   ├── database.sql                # Canonical 5-table schema + Kampala seed data
│   └── migrations/
│       └── 001_backend_controllers.sql  # Upgrade script for pre-v1 schemas
└── docs/
    ├── FINAL_PROJECT_REPORT.docx     # Formatted 6-chapter MUBS Final Project Report (A4, TOC & figures)
    ├── FINAL_PROJECT_REPORT.md       # Markdown source reference of the Final Project Report
    ├── SYSTEM_DESIGN.md              # Section 3.6 architecture, ERD, data dictionary, DFD & flows
    ├── ITPD_Proposal.docx            # Approved MUBS project proposal (Sep 2026)
    ├── Format of Final Report - BBC III and BOIM III 2022-1-1 (1) (1).pdf  # Official MUBS template
    └── figures/                      # Rendered system design diagrams (fig1–6) & live UI captures (fig7–11)
```

---

## Local Setup (XAMPP)

1. **Clone / place project** inside your XAMPP web root:
   ```text
   C:\xampp\htdocs\micro-health-insurance
   ```
2. **Start services** in the XAMPP Control Panel: **Apache** and **MySQL**.
3. **Check MySQL Port in `config/db.php`:**
   `config/db.php` is currently set to port **`3307`**:
   ```php
   define('DB_PORT', '3307'); // Standard XAMPP = 3306
   ```
   If your XAMPP MySQL runs on the default port **`3306`**, change `'3307'` to `'3306'`.
4. **Import the database:**
   - **phpMyAdmin:** Open `http://localhost/phpmyadmin`, go to **Import**, choose `database/database.sql`, and click **Go**.
   - **CLI (port 3307 example):**
     ```bash
     mysql -h 127.0.0.1 -P 3307 -u root < database/database.sql
     ```
5. **Open the application:**
   Visit `http://localhost/micro-health-insurance/` in your browser.

---

## Seeded Kampala Demo Data

All seeded accounts share the demo password **`password`**.

### Accounts
| Role | Phone (Login) | Name | Details |
|---|---|---|---|
| **Admin** | `0700000001` | System Administrator | Full scheme oversight |
| **Attendant** | `0772100101` | Nakato Sarah | Linked to Kisenyi Care Pharmacy |
| **Client** | `0701555111` | Mukasa John Bosco | Policy `MHI-KLA-2026-00001` · Contributed UGX 18,000 · Balance **UGX 10,500** |
| **Client** | `0782555222` | Namuli Grace | Policy `MHI-KLA-2026-00002` · Contributed UGX 15,000 · Balance **UGX 15,000** |

### Partner Pharmacies
- **Kisenyi Care Pharmacy** — Kampala Central Division (`NDA/RP/2026/0412`)
- **Kawempe Community Chemist** — Kawempe Division (`NDA/RP/2026/0587`)
- **Katwe Hope Pharmacy** — Makindye Division (`NDA/RP/2026/0633`)

---

## Additional Documentation
- [docs/FINAL_PROJECT_REPORT.docx](docs/FINAL_PROJECT_REPORT.docx) — Complete MUBS Final Project Report (Preliminary pages, Chapters 1–6, APA References, Appendices, embedded diagrams & live UI captures).
- [docs/FINAL_PROJECT_REPORT.md](docs/FINAL_PROJECT_REPORT.md) — Clean Markdown reference copy of the Final Project Report.
- [docs/SYSTEM_DESIGN.md](docs/SYSTEM_DESIGN.md) — 3-Tier Architecture, ERD, Data Dictionary, Context & Level-1 DFDs, Use Cases, and User Flows.
- [docs/ITPD_Proposal.docx](docs/ITPD_Proposal.docx) — Approved project proposal and original interview instruments.
- [BACKEND_CONTRACT.md](BACKEND_CONTRACT.md) — Form endpoints, input `name` attributes, validation rules, and `fetch_data.php` variables.
