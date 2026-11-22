<?php
/**
 * attendant/claim_process.php      (role: attendant)
 * POST  client_id, prescription_notes, total_cost,
 *       prescription_ref (optional), prescriber_name (optional), diagnosis (optional), csrf_token
 * ->    ../attendant/receipt.php?claim_id=<id>&success=claim_approved|claim_partial
 * Errors -> ../attendant/lookup.php?search=<client phone>&error=<code>
 *
 * Co-payment model:
 *   covered_amount = min(available_balance, total_cost)   (paid from wallet)
 *   client_copay   = total_cost - covered_amount          (paid at the counter)
 *   status         = 'approved' if client_copay == 0 else 'partial'
 * The wallet row is locked with SELECT ... FOR UPDATE inside ONE transaction.
 * pharmacy_id and attendant_id come from the SESSION, never from the form.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
requireRole('attendant');

const LOOKUP_PAGE = '../attendant/lookup.php';

requirePost(LOOKUP_PAGE);
verifyCsrf(LOOKUP_PAGE);

$attendantId = (int) $_SESSION['user_id'];
$pharmacyId  = (int) ($_SESSION['pharmacy_id'] ?? 0);

$clientId          = inputId('client_id');
$prescriptionNotes = inputMultiline('prescription_notes', 2000);
$totalCostRaw      = $_POST['total_cost'] ?? '';
$prescriptionRef   = inputText('prescription_ref', 50);
$prescriberName    = inputText('prescriber_name', 120);
$diagnosis         = inputText('diagnosis', 255);

setOld([
    'prescription_notes' => $prescriptionNotes,
    'total_cost'         => is_string($totalCostRaw) ? $totalCostRaw : '',
    'prescription_ref'   => $prescriptionRef,
    'prescriber_name'    => $prescriberName,
    'diagnosis'          => $diagnosis,
]);

/** Look up the client's phone so errors return to the same lookup result. */
$clientPhone = '';
if ($clientId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT phone FROM users WHERE user_id = :id AND role = 'client'");
        $stmt->execute([':id' => $clientId]);
        $clientPhone = (string) ($stmt->fetchColumn() ?: '');
    } catch (PDOException $e) {
        error_log('[MHI] claim_process phone lookup: ' . $e->getMessage());
    }
}

$fail = static function (string $code) use ($clientPhone): never {
    $params = $clientPhone !== '' ? ['search' => $clientPhone] : [];
    redirectTo(LOOKUP_PAGE, $params + ['error' => $code]);
};

// ---------------------------- Validation -----------------------------
if ($pharmacyId <= 0) {
    $fail('no_pharmacy_assigned');
}
if ($clientId <= 0 || $clientPhone === '') {
    $fail('client_not_found');
}
if (mb_strlen($prescriptionNotes) < 3) {
    $fail('invalid_prescription');
}
$totalCents = parseAmountToCents($totalCostRaw);
if ($totalCents === null || $totalCents <= 0) {
    $fail('invalid_amount');
}
if ($totalCents > MAX_TRANSACTION_UGX * 100) {
    $fail('amount_too_large');
}

// ---------------------------- Persistence ----------------------------
try {
    $pdo->beginTransaction();

    // Lock the wallet row so concurrent claims/deposits cannot race
    $stmt = $pdo->prepare(
        "SELECT w.wallet_id, w.available_balance, w.status AS wallet_status, u.status AS user_status
           FROM policies_wallets w
           JOIN users u ON u.user_id = w.user_id
          WHERE w.user_id = :client_id AND u.role = 'client'
          LIMIT 1
          FOR UPDATE"
    );
    $stmt->execute([':client_id' => $clientId]);
    $wallet = $stmt->fetch();

    if (!$wallet || $wallet['user_status'] !== 'active') {
        $pdo->rollBack();
        $fail('client_not_found');
    }
    if ($wallet['wallet_status'] !== 'active') {
        $pdo->rollBack();
        $fail('wallet_inactive');
    }

    $balanceCents = parseAmountToCents((string) $wallet['available_balance']) ?? 0;

    if ($balanceCents <= 0) {
        $pdo->rollBack();
        $fail('insufficient_balance');
    }

    $coveredCents = min($balanceCents, $totalCents);
    $copayCents   = $totalCents - $coveredCents;
    $status       = $copayCents === 0 ? 'approved' : 'partial';
    $covered      = centsToDecimal($coveredCents);

    // Deduct the covered amount (guard keeps balance non-negative)
    $stmt = $pdo->prepare(
        'UPDATE policies_wallets
            SET available_balance = available_balance - :covered
          WHERE wallet_id = :wallet_id AND available_balance >= :covered_check'
    );
    $stmt->execute([
        ':covered'       => $covered,
        ':covered_check' => $covered,
        ':wallet_id'     => $wallet['wallet_id'],
    ]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Wallet debit failed (balance changed).');
    }

    $stmt = $pdo->prepare(
        'INSERT INTO claims
            (wallet_id, pharmacy_id, attendant_id, prescription_ref, prescriber_name, diagnosis,
             prescription_notes, total_cost, covered_amount, client_copay, status)
         VALUES
            (:wallet_id, :pharmacy_id, :attendant_id, :prescription_ref, :prescriber_name, :diagnosis,
             :prescription_notes, :total_cost, :covered_amount, :client_copay, :status)'
    );
    $stmt->execute([
        ':wallet_id'          => $wallet['wallet_id'],
        ':pharmacy_id'        => $pharmacyId,
        ':attendant_id'       => $attendantId,
        ':prescription_ref'   => $prescriptionRef !== '' ? $prescriptionRef : null,
        ':prescriber_name'    => $prescriberName !== '' ? $prescriberName : null,
        ':diagnosis'          => $diagnosis !== '' ? $diagnosis : null,
        ':prescription_notes' => $prescriptionNotes,
        ':total_cost'         => centsToDecimal($totalCents),
        ':covered_amount'     => $covered,
        ':client_copay'       => centsToDecimal($copayCents),
        ':status'             => $status,
    ]);
    $claimId = (int) $pdo->lastInsertId();

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[MHI] claim_process: ' . $e->getMessage());
    $fail('claim_failed');
}

unset($_SESSION['old']);

redirectTo('../attendant/receipt.php', [
    'claim_id' => $claimId,
    'success'  => $status === 'approved' ? 'claim_approved' : 'claim_partial',
]);

