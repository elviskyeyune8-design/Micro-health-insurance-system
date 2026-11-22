<?php
/**
 * client/deposit_process.php        (role: client)
 * POST  amount, payment_method (MTN_MoMo | Airtel_Money | Cash_Agent),
 *       payer_phone (optional, defaults to the client's phone), csrf_token
 * ->    ../client/dashboard.php?success=deposit_completed&ref=<transaction_ref>
 * Errors -> ../client/deposit.php?error=<code>
 *
 * Simulated mobile-money collection: the contribution is recorded as
 * 'completed' and the wallet is credited inside ONE PDO transaction.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
requireRole('client');

const DEPOSIT_PAGE = '../client/deposit.php';

requirePost(DEPOSIT_PAGE);
verifyCsrf(DEPOSIT_PAGE);

$userId        = (int) $_SESSION['user_id'];
$amountRaw     = $_POST['amount'] ?? '';
$paymentMethod = inputText('payment_method', 20);
$payerPhone    = normalizePhone(inputText('payer_phone', 20));

setOld(['amount' => is_string($amountRaw) ? $amountRaw : '', 'payment_method' => $paymentMethod, 'payer_phone' => $payerPhone]);

$fail = static fn (string $code) => redirectTo(DEPOSIT_PAGE, ['error' => $code]);

// ---------------------------- Validation -----------------------------
$amountCents = parseAmountToCents($amountRaw);
if ($amountCents === null || $amountCents <= 0) {
    $fail('invalid_amount');
}
if ($amountCents > MAX_TRANSACTION_UGX * 100) {
    $fail('amount_too_large');
}
if (!array_key_exists($paymentMethod, PAYMENT_METHODS)) {
    $fail('invalid_payment_method');
}
if ($payerPhone === '') {
    $payerPhone = (string) ($_SESSION['phone'] ?? '');
}
if (!isValidUgPhone($payerPhone)) {
    $fail('invalid_payer_phone');
}

/**
 * Unique transaction reference, e.g. MP261117.2158.A7F3C9
 * Prefix: MP = MTN MoMo, AM = Airtel Money, CA = Cash Agent
 */
function generateTransactionRef(PDO $pdo, string $method): string
{
    $prefix = ['MTN_MoMo' => 'MP', 'Airtel_Money' => 'AM', 'Cash_Agent' => 'CA'][$method] ?? 'TX';
    $check  = $pdo->prepare('SELECT 1 FROM contributions WHERE transaction_ref = :r LIMIT 1');

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $ref = sprintf('%s%s.%s', $prefix, date('ymd.Hi'), strtoupper(bin2hex(random_bytes(3))));
        $check->execute([':r' => $ref]);
        if (!$check->fetchColumn()) {
            return $ref;
        }
    }
    throw new RuntimeException('Could not generate a unique transaction reference.');
}

// ---------------------------- Persistence ----------------------------
try {
    $pdo->beginTransaction();

    // Lock the client's wallet row for the duration of the transaction
    $stmt = $pdo->prepare(
        'SELECT wallet_id, status FROM policies_wallets WHERE user_id = :uid LIMIT 1 FOR UPDATE'
    );
    $stmt->execute([':uid' => $userId]);
    $wallet = $stmt->fetch();

    if (!$wallet) {
        $pdo->rollBack();
        $fail('wallet_not_found');
    }
    if ($wallet['status'] !== 'active') {
        $pdo->rollBack();
        $fail('wallet_inactive');
    }

    $amount = centsToDecimal($amountCents);
    $ref    = generateTransactionRef($pdo, $paymentMethod);

    $stmt = $pdo->prepare(
        "INSERT INTO contributions (wallet_id, amount, payment_method, payer_phone, transaction_ref, status)
         VALUES (:wallet_id, :amount, :method, :payer_phone, :ref, 'completed')"
    );
    $stmt->execute([
        ':wallet_id'   => $wallet['wallet_id'],
        ':amount'      => $amount,
        ':method'      => $paymentMethod,
        ':payer_phone' => $payerPhone,
        ':ref'         => $ref,
    ]);

    $stmt = $pdo->prepare(
        'UPDATE policies_wallets
            SET total_contributed = total_contributed + :amt1,
                available_balance = available_balance + :amt2
          WHERE wallet_id = :wallet_id'
    );
    $stmt->execute([':amt1' => $amount, ':amt2' => $amount, ':wallet_id' => $wallet['wallet_id']]);

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[MHI] deposit_process: ' . $e->getMessage());
    $fail('deposit_failed');
}

unset($_SESSION['old']);

redirectTo('../client/dashboard.php', ['success' => 'deposit_completed', 'ref' => $ref]);

