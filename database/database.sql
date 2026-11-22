-- =====================================================================
--  Micro Health Insurance System for Low-Income Pharmacy Clients
--  Kampala, Uganda
--  Database: micro_health_db   (MySQL 5.7+/8.x  |  MariaDB 10.4+ on XAMPP)
--
--  Import:  phpMyAdmin > Import > database.sql
--      or:  mysql -u root -P 3307 < database/database.sql
--
--  Demo login password for ALL seeded accounts:  password
--  Currency: Ugandan Shillings (UGX) stored as DECIMAL(12,2)
-- =====================================================================

SET NAMES utf8mb4;
SET time_zone = '+03:00';   -- East Africa Time (EAT)

CREATE DATABASE IF NOT EXISTS micro_health_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE micro_health_db;

-- Drop in reverse dependency order so the script can be re-imported cleanly
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS claims;
DROP TABLE IF EXISTS contributions;
DROP TABLE IF EXISTS policies_wallets;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS pharmacies;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. pharmacies : partner pharmacies where clients redeem cover
-- ---------------------------------------------------------------------
CREATE TABLE pharmacies (
    pharmacy_id   INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120)  NOT NULL,
    license_no    VARCHAR(50)   NOT NULL,                 -- NDA licence number
    location      VARCHAR(100)  NOT NULL,                 -- Division / area
    address       VARCHAR(255)  NULL,
    phone         VARCHAR(15)   NOT NULL,
    status        ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (pharmacy_id),
    UNIQUE KEY uq_pharmacies_name (name),
    UNIQUE KEY uq_pharmacies_license (license_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. users : admins, pharmacy attendants and insured clients
--    pharmacy_id -> attendant's workplace / client's home pharmacy
--                   (NULL for admins)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id        INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    full_name      VARCHAR(120)  NOT NULL,
    phone          VARCHAR(15)   NOT NULL,                -- login ID, format 07XXXXXXXX
    nin            VARCHAR(20)   NULL,                    -- Uganda National ID No.
    password_hash  VARCHAR(255)  NOT NULL,
    role           ENUM('admin','attendant','client') NOT NULL DEFAULT 'client',
    pharmacy_id    INT UNSIGNED  NULL,
    status         ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_phone (phone),
    UNIQUE KEY uq_users_nin (nin),
    KEY idx_users_role (role),
    KEY idx_users_pharmacy (pharmacy_id),
    CONSTRAINT fk_users_pharmacy
        FOREIGN KEY (pharmacy_id) REFERENCES pharmacies (pharmacy_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. policies_wallets : one insurance policy + savings wallet per client
-- ---------------------------------------------------------------------
CREATE TABLE policies_wallets (
    wallet_id      INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    user_id        INT UNSIGNED   NOT NULL,
    policy_number  VARCHAR(30)    NOT NULL,
    plan_name          VARCHAR(60)    NOT NULL DEFAULT 'Basic Care Plan',
    total_contributed  DECIMAL(12,2)  NOT NULL DEFAULT 0.00,  -- lifetime completed deposits (UGX)
    available_balance  DECIMAL(12,2)  NOT NULL DEFAULT 0.00,  -- UGX available for claims
    status         ENUM('active','suspended','closed') NOT NULL DEFAULT 'active',
    start_date     DATE           NOT NULL,
    created_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (wallet_id),
    UNIQUE KEY uq_wallets_user (user_id),
    UNIQUE KEY uq_wallets_policy (policy_number),
    CONSTRAINT chk_wallet_balance     CHECK (available_balance >= 0),
    CONSTRAINT chk_wallet_contributed CHECK (total_contributed >= 0),
    CONSTRAINT fk_wallets_user
        FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. contributions : micro-deposits via mobile money (or cash at counter)
-- ---------------------------------------------------------------------
CREATE TABLE contributions (
    contribution_id  INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    wallet_id        INT UNSIGNED   NOT NULL,
    amount           DECIMAL(12,2)  NOT NULL,
    payment_method   ENUM('MTN_MoMo','Airtel_Money','Cash_Agent') NOT NULL,
    payer_phone      VARCHAR(15)    NOT NULL,
    transaction_ref  VARCHAR(50)    NOT NULL,
    status           ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
    created_at       TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (contribution_id),
    UNIQUE KEY uq_contrib_txref (transaction_ref),
    KEY idx_contrib_wallet (wallet_id),
    KEY idx_contrib_created (created_at),
    CONSTRAINT chk_contrib_amount CHECK (amount > 0),
    CONSTRAINT fk_contrib_wallet
        FOREIGN KEY (wallet_id) REFERENCES policies_wallets (wallet_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. claims : prescription claims processed by an attendant at a pharmacy
-- ---------------------------------------------------------------------
CREATE TABLE claims (
    claim_id          INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    wallet_id         INT UNSIGNED   NOT NULL,
    pharmacy_id       INT UNSIGNED   NOT NULL,
    attendant_id      INT UNSIGNED   NOT NULL,
    prescription_ref  VARCHAR(50)    NULL,
    prescriber_name   VARCHAR(120)   NULL,
    diagnosis         VARCHAR(255)   NULL,
    prescription_notes TEXT          NOT NULL,                -- medicines dispensed
    total_cost        DECIMAL(12,2)  NOT NULL,                -- full prescription cost
    covered_amount    DECIMAL(12,2)  NOT NULL DEFAULT 0.00,   -- paid from wallet
    client_copay      DECIMAL(12,2)  NOT NULL DEFAULT 0.00,   -- paid by client at counter
    status            ENUM('approved','partial','rejected') NOT NULL DEFAULT 'approved',
    rejection_reason  VARCHAR(255)   NULL,
    created_at        TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (claim_id),
    KEY idx_claims_wallet (wallet_id),
    KEY idx_claims_pharmacy (pharmacy_id),
    KEY idx_claims_attendant (attendant_id),
    KEY idx_claims_created (created_at),
    CONSTRAINT chk_claim_total   CHECK (total_cost > 0),
    CONSTRAINT chk_claim_parts   CHECK (covered_amount >= 0 AND client_copay >= 0),
    CONSTRAINT chk_claim_balance CHECK (covered_amount + client_copay = total_cost),
    CONSTRAINT fk_claims_wallet
        FOREIGN KEY (wallet_id) REFERENCES policies_wallets (wallet_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_claims_pharmacy
        FOREIGN KEY (pharmacy_id) REFERENCES pharmacies (pharmacy_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_claims_attendant
        FOREIGN KEY (attendant_id) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
--  SEED DATA  (Kampala demo data)
--  Password for every account below = "password"
--  Hash generated with PHP 8.2 password_hash('password', PASSWORD_DEFAULT)
-- =====================================================================

-- Pharmacies -----------------------------------------------------------
INSERT INTO pharmacies (pharmacy_id, name, license_no, location, address, phone, status, created_at) VALUES
(1, 'Kisenyi Care Pharmacy',     'NDA/RP/2026/0412', 'Kampala Central Division', 'Plot 14, Kisenyi Road, near Owino Market', '0414250101', 'active', '2026-01-10 09:00:00'),
(2, 'Kawempe Community Chemist', 'NDA/RP/2026/0587', 'Kawempe Division',         'Plot 32, Bombo Road, Kawempe Trading Centre', '0414250202', 'active', '2026-01-12 09:00:00'),
(3, 'Katwe Hope Pharmacy',       'NDA/RP/2026/0633', 'Makindye Division',        'Plot 7, Katwe Main Street, Katwe',          '0414250303', 'active', '2026-01-15 09:00:00');

-- Users ----------------------------------------------------------------
INSERT INTO users (user_id, full_name, phone, nin, password_hash, role, pharmacy_id, status, created_at) VALUES
(1, 'System Administrator', '0700000001', NULL,
    '$2y$10$n8IZGGVkIlvOyKuIq7gcy.GXLN6dxA4XtyMjUKwwrUf8Sw8LUuKom', 'admin',     NULL, 'active', '2026-01-10 08:00:00'),
(2, 'Nakato Sarah',         '0772100101', 'CF90034512KXLA',
    '$2y$10$n8IZGGVkIlvOyKuIq7gcy.GXLN6dxA4XtyMjUKwwrUf8Sw8LUuKom', 'attendant', 1,    'active', '2026-01-10 10:00:00'),
(3, 'Mukasa John Bosco',    '0701555111', 'CM88021145PQRA',
    '$2y$10$n8IZGGVkIlvOyKuIq7gcy.GXLN6dxA4XtyMjUKwwrUf8Sw8LUuKom', 'client',    1,    'active', '2026-02-03 11:20:00'),
(4, 'Namuli Grace',         '0782555222', 'CF95017788MNBA',
    '$2y$10$n8IZGGVkIlvOyKuIq7gcy.GXLN6dxA4XtyMjUKwwrUf8Sw8LUuKom', 'client',    2,    'active', '2026-02-05 14:45:00');

-- Policies / Wallets ---------------------------------------------------
--   Mukasa : 10,000 + 5,000 + 3,000 contributed - 7,500 claimed = 10,500
--   Namuli : 10,000 + 5,000 contributed (2,000 failed, not counted) = 15,000
INSERT INTO policies_wallets (wallet_id, user_id, policy_number, plan_name, total_contributed, available_balance, status, start_date, created_at) VALUES
(1, 3, 'MHI-KLA-2026-00001', 'Basic Care Plan', 18000.00, 10500.00, 'active', '2026-02-03', '2026-02-03 11:20:00'),
(2, 4, 'MHI-KLA-2026-00002', 'Basic Care Plan', 15000.00, 15000.00, 'active', '2026-02-05', '2026-02-05 14:45:00');

-- Contributions (070x = Airtel line, 077x/078x = MTN line) ---------------
INSERT INTO contributions (contribution_id, wallet_id, amount, payment_method, payer_phone, transaction_ref, status, created_at) VALUES
(1, 1, 10000.00, 'Airtel_Money', '0701555111', 'AM260203.1124.C10045', 'completed', '2026-02-03 11:24:00'),
(2, 1,  5000.00, 'Airtel_Money', '0701555111', 'AM260310.0915.C20871', 'completed', '2026-03-10 09:15:00'),
(3, 1,  3000.00, 'MTN_MoMo',     '0772884410', 'MP260402.1802.A33190', 'completed', '2026-04-02 18:02:00'),  -- paid by a relative
(4, 2, 10000.00, 'MTN_MoMo',     '0782555222', 'MP260205.1450.A41522', 'completed', '2026-02-05 14:50:00'),
(5, 2,  2000.00, 'MTN_MoMo',     '0782555222', 'MP260301.0730.A50017', 'failed',    '2026-03-01 07:30:00'),
(6, 2,  5000.00, 'MTN_MoMo',     '0782555222', 'MP260301.0742.A50023', 'completed', '2026-03-01 07:42:00');

-- Claims ---------------------------------------------------------------
INSERT INTO claims (claim_id, wallet_id, pharmacy_id, attendant_id, prescription_ref, prescriber_name, diagnosis, prescription_notes, total_cost, covered_amount, client_copay, status, rejection_reason, created_at) VALUES
(1, 1, 1, 2, 'RX-KCCA-26-004517', 'Dr. Okello Patrick (Kisenyi HC IV)', 'Uncomplicated malaria',
    'Artemether/Lumefantrine 20/120mg x 24 tabs; Paracetamol 500mg x 18 tabs',
    7500.00, 7500.00, 0.00, 'approved', NULL, '2026-04-15 10:30:00');

-- =====================================================================
--  End of script
-- =====================================================================

