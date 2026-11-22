-- =====================================================================
--  Migration 001 — backend-controllers
--  Target : micro_health_db (already imported from the initial database.sql)
--  Engine : MariaDB 10.4+ / MySQL 8.0.19+
--
--  RUN ONCE on an existing DB. Fresh installs do NOT need this file:
--  database/database.sql already contains the final schema.
--
--  Changes
--   * policies_wallets : balance -> available_balance, + total_contributed
--   * contributions    : payment_method values -> MTN_MoMo | Airtel_Money | Cash_Agent
--   * claims           : amount -> total_cost, + covered_amount, + client_copay,
--                        medicines -> prescription_notes, prescription_ref optional,
--                        status adds 'partial'
--   * pharmacies       : address optional
-- =====================================================================

USE micro_health_db;

-- ---------------------------------------------------------------------
-- policies_wallets
-- ---------------------------------------------------------------------
ALTER TABLE policies_wallets DROP CONSTRAINT chk_wallet_balance;

ALTER TABLE policies_wallets
    CHANGE COLUMN balance available_balance DECIMAL(12,2) NOT NULL DEFAULT 0.00;

ALTER TABLE policies_wallets
    ADD COLUMN total_contributed DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER plan_name;

UPDATE policies_wallets w
SET w.total_contributed = (
    SELECT COALESCE(SUM(c.amount), 0)
    FROM contributions c
    WHERE c.wallet_id = w.wallet_id AND c.status = 'completed'
);

ALTER TABLE policies_wallets
    ADD CONSTRAINT chk_wallet_balance     CHECK (available_balance >= 0),
    ADD CONSTRAINT chk_wallet_contributed CHECK (total_contributed >= 0);

-- ---------------------------------------------------------------------
-- contributions  (ENUM labels differ only by case, so go via VARCHAR)
-- ---------------------------------------------------------------------
ALTER TABLE contributions MODIFY payment_method VARCHAR(20) NOT NULL;

UPDATE contributions
SET payment_method = CASE UPPER(payment_method)
        WHEN 'MTN_MOMO'     THEN 'MTN_MoMo'
        WHEN 'AIRTEL_MONEY' THEN 'Airtel_Money'
        WHEN 'CASH'         THEN 'Cash_Agent'
        ELSE payment_method
    END;

ALTER TABLE contributions
    MODIFY payment_method ENUM('MTN_MoMo','Airtel_Money','Cash_Agent') NOT NULL;

-- ---------------------------------------------------------------------
-- claims
-- ---------------------------------------------------------------------
ALTER TABLE claims DROP CONSTRAINT chk_claim_amount;

ALTER TABLE claims
    CHANGE COLUMN amount    total_cost         DECIMAL(12,2) NOT NULL,
    CHANGE COLUMN medicines prescription_notes TEXT          NOT NULL,
    MODIFY prescription_ref VARCHAR(50) NULL,
    MODIFY status ENUM('approved','partial','rejected') NOT NULL DEFAULT 'approved';

ALTER TABLE claims
    ADD COLUMN covered_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER total_cost;

ALTER TABLE claims
    ADD COLUMN client_copay   DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER covered_amount;

-- Existing claims were fully covered from the wallet
UPDATE claims SET covered_amount = total_cost, client_copay = 0.00 WHERE status = 'approved';
UPDATE claims SET covered_amount = 0.00, client_copay = total_cost WHERE status = 'rejected';

ALTER TABLE claims
    ADD CONSTRAINT chk_claim_total   CHECK (total_cost > 0),
    ADD CONSTRAINT chk_claim_parts   CHECK (covered_amount >= 0 AND client_copay >= 0),
    ADD CONSTRAINT chk_claim_balance CHECK (covered_amount + client_copay = total_cost);

-- ---------------------------------------------------------------------
-- pharmacies
-- ---------------------------------------------------------------------
ALTER TABLE pharmacies MODIFY address VARCHAR(255) NULL;

