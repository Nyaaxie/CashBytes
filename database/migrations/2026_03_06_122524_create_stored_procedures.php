<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateStoredProcedures extends Migration
{
    public function up(): void
    {
        // Drop all first
        DB::unprepared('DROP PROCEDURE IF EXISTS transfer_funds');
        DB::unprepared('DROP PROCEDURE IF EXISTS bank_transfer');
        DB::unprepared('DROP PROCEDURE IF EXISTS buy_load');
        DB::unprepared('DROP PROCEDURE IF EXISTS pay_bill');
        DB::unprepared('DROP PROCEDURE IF EXISTS add_to_savings');
        DB::unprepared('DROP PROCEDURE IF EXISTS withdraw_from_savings');
        DB::unprepared('DROP PROCEDURE IF EXISTS delete_savings_goal');

        // Allow receiver_wallet_id to be NULL (for bank transfers)
        DB::statement('ALTER TABLE funds_transfers MODIFY receiver_wallet_id BIGINT UNSIGNED NULL');

        // Add bank_account_id and purpose column to funds_transfers (3NF fix)
        // No FK constraint here — bank_accounts migration runs later
        // Ownership is enforced at the application level in BankTransferController
        if (!\Illuminate\Support\Facades\Schema::hasColumn('funds_transfers', 'bank_account_id')) {
            DB::statement('ALTER TABLE funds_transfers ADD COLUMN bank_account_id BIGINT UNSIGNED NULL AFTER receiver_wallet_id');
        }
        if (!\Illuminate\Support\Facades\Schema::hasColumn('funds_transfers', 'purpose')) {
            DB::statement('ALTER TABLE funds_transfers ADD COLUMN purpose VARCHAR(255) NULL AFTER bank_account_id');
        }

        // Add bank_transfer to transactions type enum
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('transfer','bank_transfer','save','load','bill_payment') NOT NULL");

        // ── 1. FUND TRANSFER ─────────────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE transfer_funds(
                IN p_sender_wallet_id   BIGINT,
                IN p_receiver_wallet_id BIGINT,
                IN p_amount             DECIMAL(15,2),
                IN p_note               VARCHAR(255)
            )
            BEGIN
                DECLARE sender_balance DECIMAL(15,2);
                DECLARE ref_no         VARCHAR(50);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT balance INTO sender_balance
                FROM wallets WHERE id = p_sender_wallet_id FOR UPDATE;

                IF sender_balance < p_amount THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Insufficient balance.";
                END IF;

                IF p_amount <= 0 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Amount must be greater than zero.";
                END IF;

                SET ref_no = CONCAT("CB-TRF-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0"));

                UPDATE wallets SET balance = balance - p_amount WHERE id = p_sender_wallet_id;
                UPDATE wallets SET balance = balance + p_amount WHERE id = p_receiver_wallet_id;

                INSERT INTO funds_transfers (sender_wallet_id, receiver_wallet_id, amount, note, created_at, updated_at)
                VALUES (p_sender_wallet_id, p_receiver_wallet_id, p_amount, p_note, NOW(), NOW());

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, transactable_id, transactable_type, created_at, updated_at)
                VALUES (p_sender_wallet_id, "transfer", "debit", p_amount, ref_no, CONCAT("Transfer to wallet #", p_receiver_wallet_id), "completed", LAST_INSERT_ID(), "App\\Models\\FundTransfer", NOW(), NOW());

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, transactable_id, transactable_type, created_at, updated_at)
                VALUES (p_receiver_wallet_id, "transfer", "credit", p_amount, CONCAT(ref_no, "-CR"), CONCAT("Received from wallet #", p_sender_wallet_id), "completed", LAST_INSERT_ID(), "App\\Models\\FundTransfer", NOW(), NOW());

                COMMIT;
            END
        ');

        // ── 2. BANK TRANSFER ─────────────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE bank_transfer(
                IN p_wallet_id       BIGINT,
                IN p_bank_account_id BIGINT,
                IN p_amount          DECIMAL(15,2),
                IN p_purpose         VARCHAR(255)
            )
            BEGIN
                DECLARE wallet_balance DECIMAL(15,2);
                DECLARE v_bank_name    VARCHAR(255);
                DECLARE v_account_name VARCHAR(255);
                DECLARE v_account_no   VARCHAR(255);
                DECLARE ref_no         VARCHAR(50);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT balance INTO wallet_balance
                FROM wallets WHERE id = p_wallet_id FOR UPDATE;

                SELECT bank_name, account_name, account_number
                INTO v_bank_name, v_account_name, v_account_no
                FROM bank_accounts WHERE id = p_bank_account_id;

                IF wallet_balance < p_amount THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Insufficient balance.";
                END IF;

                IF p_amount <= 0 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Amount must be greater than zero.";
                END IF;

                SET ref_no = CONCAT("CB-BANK-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0"));

                UPDATE wallets SET balance = balance - p_amount WHERE id = p_wallet_id;

                -- Store bank_account_id as FK (3NF compliant — no packed strings)
                INSERT INTO funds_transfers (sender_wallet_id, receiver_wallet_id, bank_account_id, purpose, amount, note, created_at, updated_at)
                VALUES (p_wallet_id, NULL, p_bank_account_id, p_purpose, p_amount, NULL, NOW(), NOW());

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, transactable_id, transactable_type, created_at, updated_at)
                VALUES (p_wallet_id, "bank_transfer", "debit", p_amount, ref_no, CONCAT("Bank Transfer to ", v_bank_name, " - ", v_account_name), "completed", LAST_INSERT_ID(), "App\\Models\\FundTransfer", NOW(), NOW());

                COMMIT;
            END
        ');

        // ── 3. BUY LOAD ───────────────────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE buy_load(
                IN p_wallet_id     BIGINT,
                IN p_mobile_number VARCHAR(15),
                IN p_network       VARCHAR(50),
                IN p_promo_code    VARCHAR(50),
                IN p_amount        DECIMAL(15,2)
            )
            BEGIN
                DECLARE wallet_balance DECIMAL(15,2);
                DECLARE ref_no         VARCHAR(50);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT balance INTO wallet_balance
                FROM wallets WHERE id = p_wallet_id FOR UPDATE;

                IF wallet_balance < p_amount THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Insufficient balance.";
                END IF;

                SET ref_no = CONCAT("CB-LOAD-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0"));

                UPDATE wallets SET balance = balance - p_amount WHERE id = p_wallet_id;

                INSERT INTO load_purchases (wallet_id, mobile_number, network, promo_code, amount, created_at, updated_at)
                VALUES (p_wallet_id, p_mobile_number, p_network, p_promo_code, p_amount, NOW(), NOW());

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, transactable_id, transactable_type, created_at, updated_at)
                VALUES (p_wallet_id, "load", "debit", p_amount, ref_no, CONCAT(p_network, " Load - ", p_mobile_number), "completed", LAST_INSERT_ID(), "App\\Models\\LoadPurchase", NOW(), NOW());

                COMMIT;
            END
        ');

        // ── 4. PAY BILL ───────────────────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE pay_bill(
                IN p_wallet_id      BIGINT,
                IN p_biller_id      BIGINT,
                IN p_account_number VARCHAR(100),
                IN p_amount         DECIMAL(15,2)
            )
            BEGIN
                DECLARE wallet_balance DECIMAL(15,2);
                DECLARE biller_name    VARCHAR(255);
                DECLARE ref_no         VARCHAR(50);
                DECLARE confirm_no     VARCHAR(50);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT balance INTO wallet_balance
                FROM wallets WHERE id = p_wallet_id FOR UPDATE;

                SELECT name INTO biller_name
                FROM billers WHERE id = p_biller_id;

                IF wallet_balance < p_amount THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Insufficient balance.";
                END IF;

                SET ref_no     = CONCAT("CB-BILL-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0"));
                SET confirm_no = CONCAT("CONF-", LPAD(FLOOR(RAND() * 9999999), 7, "0"));

                UPDATE wallets SET balance = balance - p_amount WHERE id = p_wallet_id;

                INSERT INTO bills_payments (wallet_id, biller_id, account_number, amount, confirmation_no, created_at, updated_at)
                VALUES (p_wallet_id, p_biller_id, p_account_number, p_amount, confirm_no, NOW(), NOW());

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, transactable_id, transactable_type, created_at, updated_at)
                VALUES (p_wallet_id, "bill_payment", "debit", p_amount, ref_no, CONCAT("Bill Payment - ", biller_name), "completed", LAST_INSERT_ID(), "App\\Models\\BillPayment", NOW(), NOW());

                COMMIT;
            END
        ');

        // ── 5. ADD TO SAVINGS ─────────────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE add_to_savings(
                IN p_wallet_id       BIGINT,
                IN p_savings_goal_id BIGINT,
                IN p_amount          DECIMAL(15,2)
            )
            BEGIN
                DECLARE wallet_balance   DECIMAL(15,2);
                DECLARE goal_target      DECIMAL(15,2);
                DECLARE goal_current     DECIMAL(15,2);
                DECLARE ref_no           VARCHAR(50);
                DECLARE new_txn_id       BIGINT;

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT balance INTO wallet_balance
                FROM wallets WHERE id = p_wallet_id FOR UPDATE;

                SELECT target_amount, current_amount INTO goal_target, goal_current
                FROM saving_goals WHERE id = p_savings_goal_id FOR UPDATE;

                IF wallet_balance < p_amount THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Insufficient balance.";
                END IF;

                IF (goal_current + p_amount) > goal_target THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Amount exceeds savings goal target.";
                END IF;

                SET ref_no = CONCAT("CB-SAV-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0"));

                UPDATE wallets SET balance = balance - p_amount WHERE id = p_wallet_id;
                UPDATE saving_goals SET current_amount = current_amount + p_amount WHERE id = p_savings_goal_id;
                UPDATE saving_goals SET status = "completed" WHERE id = p_savings_goal_id AND current_amount >= target_amount;

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, created_at, updated_at)
                VALUES (p_wallet_id, "save", "debit", p_amount, ref_no, CONCAT("Savings - Goal #", p_savings_goal_id), "completed", NOW(), NOW());

                SET new_txn_id = LAST_INSERT_ID();

                INSERT INTO savings_allocations (savings_goal_id, transaction_id, amount, created_at, updated_at)
                VALUES (p_savings_goal_id, new_txn_id, p_amount, NOW(), NOW());

                COMMIT;
            END
        ');

        // ── 6. WITHDRAW FROM SAVINGS ──────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE withdraw_from_savings(
                IN p_savings_goal_id BIGINT,
                IN p_wallet_id       BIGINT,
                IN p_amount          DECIMAL(15,2)
            )
            BEGIN
                DECLARE v_current_amount DECIMAL(15,2);
                DECLARE ref_no           VARCHAR(50);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT current_amount INTO v_current_amount
                FROM saving_goals WHERE id = p_savings_goal_id FOR UPDATE;

                SELECT id FROM wallets WHERE id = p_wallet_id FOR UPDATE;

                IF p_amount <= 0 THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Amount must be greater than zero.";
                END IF;

                IF v_current_amount < p_amount THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Withdrawal amount exceeds saved amount.";
                END IF;

                SET ref_no = CONCAT("CB-WDR-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0"));

                UPDATE saving_goals SET current_amount = current_amount - p_amount WHERE id = p_savings_goal_id;
                UPDATE saving_goals SET status = "active" WHERE id = p_savings_goal_id AND status = "completed";
                UPDATE wallets SET balance = balance + p_amount WHERE id = p_wallet_id;

                INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, created_at, updated_at)
                VALUES (p_wallet_id, "save", "credit", p_amount, ref_no, CONCAT("Savings Withdrawal - Goal #", p_savings_goal_id), "completed", NOW(), NOW());

                COMMIT;
            END
        ');

        // ── 7. DELETE SAVINGS GOAL ────────────────────────────────────
        DB::unprepared('
            CREATE PROCEDURE delete_savings_goal(
                IN p_savings_goal_id BIGINT,
                IN p_wallet_id       BIGINT
            )
            BEGIN
                DECLARE v_current_amount DECIMAL(15,2);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN ROLLBACK; RESIGNAL; END;

                START TRANSACTION;

                SELECT current_amount INTO v_current_amount
                FROM saving_goals WHERE id = p_savings_goal_id FOR UPDATE;

                IF v_current_amount > 0 THEN
                    UPDATE wallets SET balance = balance + v_current_amount WHERE id = p_wallet_id;

                    INSERT INTO transactions (wallet_id, type, direction, amount, reference_no, description, status, created_at, updated_at)
                    VALUES (p_wallet_id, "save", "credit", v_current_amount, CONCAT("CB-REFUND-", DATE_FORMAT(NOW(), "%Y%m%d"), "-", LPAD(FLOOR(RAND() * 999999), 6, "0")), CONCAT("Savings Refund - Goal #", p_savings_goal_id), "completed", NOW(), NOW());
                END IF;

                DELETE FROM savings_allocations WHERE savings_goal_id = p_savings_goal_id;
                DELETE FROM saving_goals WHERE id = p_savings_goal_id;

                COMMIT;
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS transfer_funds');
        DB::unprepared('DROP PROCEDURE IF EXISTS bank_transfer');
        DB::unprepared('DROP PROCEDURE IF EXISTS buy_load');
        DB::unprepared('DROP PROCEDURE IF EXISTS pay_bill');
        DB::unprepared('DROP PROCEDURE IF EXISTS add_to_savings');
        DB::unprepared('DROP PROCEDURE IF EXISTS withdraw_from_savings');
        DB::unprepared('DROP PROCEDURE IF EXISTS delete_savings_goal');
        DB::statement("ALTER TABLE transactions MODIFY COLUMN type ENUM('transfer','save','load','bill_payment') NOT NULL");
        try { DB::statement('ALTER TABLE funds_transfers DROP COLUMN bank_account_id'); } catch (\Exception $e) {}
        try { DB::statement('ALTER TABLE funds_transfers DROP COLUMN purpose'); } catch (\Exception $e) {}
    }
}