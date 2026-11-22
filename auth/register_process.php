<?php
/**
 * auth/register_process.php
 * POST  full_name, phone, nin (optional), pharmacy_id, password, confirm_password, csrf_token
 * ->    ../client/dashboard.php?success=registered   (auto-login)
 * Errors -> ../auth/register.php?error=<code>
 *
 * Creates the client user AND the policies_wallets record (0.00 balance)
 * inside ONE PDO transaction.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

const REGISTER_PAGE = '../auth/register.php';

requirePost(REGISTER_PAGE);
verifyCsrf(REGISTER_PAGE);

$fullName   = inputText('full_name', 120);
$phone      = normalizePhone(inputText('phone', 20));
$nin        = strtoupper(preg_replace('/\s+/', '', inputText('nin', 20)) ?? '');
$pharmacyId = inputId('pharmacy_id');
$password   = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
$confirm    = is_string($_POST['confirm_password'] ?? null) ? $_POST['confirm_password'] : '';

// Remember safe values so the form can be re-filled on error
setOld(['full_name' => $fullName, 'phone' => $phone, 'nin' => $nin, 'pharmacy_id' => (string) $pharmacyId]);

$fail = static fn (string $code) => redirectTo(REGISTER_PAGE, ['error' => $code]);

// ---------------------------- Validation -----------------------------
if ($fullName === '' || $phone === '' || $pharmacyId === 0 || $password === '' || $confirm === '') {
    $fail('missing_fields');
}
if (mb_strlen($fullName) < 3 || !preg_match("/^[\p{L}][\p{L} .'-]+$/u", $fullName)) {
    $fail('invalid_name');
}
if (!isValidUgPhone($phone)) {
    $fail('invalid_phone');
}
if ($nin !== '' && !preg_match('/^[A-Z0-9]{14}$/', $nin)) {
    $fail('invalid_nin');
}
if (strlen($password) < 6) {
    $fail('weak_password');
}
if (!hash_equals($password, $confirm)) {
    $fail('password_mismatch');
}

/** Generate a policy number MHI-KLA-XXXX (unambiguous uppercase letters/digits). */
function generatePolicyNumber(PDO $pdo): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';   // no 0/O, 1/I
    $check    = $pdo->prepare('SELECT 1 FROM policies_wallets WHERE policy_number = :p LIMIT 1');

    for ($attempt = 0; $attempt < 20; $attempt++) {
        $code = '';
        for ($i = 0; $i < 4; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $policy = 'MHI-KLA-' . $code;
        $check->execute([':p' => $policy]);
        if (!$check->fetchColumn()) {
            return $policy;
        }
    }
    throw new RuntimeException('Could not generate a unique policy number.');
}

// ---------------------------- Persistence ----------------------------
try {
    // Pharmacy must exist and be active
    $stmt = $pdo->prepare("SELECT pharmacy_id FROM pharmacies WHERE pharmacy_id = :id AND status = 'active'");
    $stmt->execute([':id' => $pharmacyId]);
    if (!$stmt->fetchColumn()) {
        $fail('invalid_pharmacy');
    }

    // Friendly duplicate checks (unique keys still enforce this at DB level)
    $stmt = $pdo->prepare('SELECT 1 FROM users WHERE phone = :phone');
    $stmt->execute([':phone' => $phone]);
    if ($stmt->fetchColumn()) {
        $fail('phone_taken');
    }
    if ($nin !== '') {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE nin = :nin');
        $stmt->execute([':nin' => $nin]);
        if ($stmt->fetchColumn()) {
            $fail('nin_taken');
        }
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO users (full_name, phone, nin, password_hash, role, pharmacy_id, status)
         VALUES (:full_name, :phone, :nin, :hash, 'client', :pharmacy_id, 'active')"
    );
    $stmt->execute([
        ':full_name'   => $fullName,
        ':phone'       => $phone,
        ':nin'         => $nin !== '' ? $nin : null,
        ':hash'        => password_hash($password, PASSWORD_DEFAULT),
        ':pharmacy_id' => $pharmacyId,
    ]);
    $userId = (int) $pdo->lastInsertId();

    $policyNumber = generatePolicyNumber($pdo);

    $stmt = $pdo->prepare(
        "INSERT INTO policies_wallets
            (user_id, policy_number, plan_name, total_contributed, available_balance, status, start_date)
         VALUES (:user_id, :policy, 'Basic Care Plan', 0.00, 0.00, 'active', CURDATE())"
    );
    $stmt->execute([':user_id' => $userId, ':policy' => $policyNumber]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[MHI] register_process: ' . $e->getMessage());
    // 23000 = integrity constraint violation (race on a unique key)
    if ($e->getCode() === '23000') {
        $fail(str_contains($e->getMessage(), 'uq_users_nin') ? 'nin_taken' : 'phone_taken');
    }
    $fail('registration_failed');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[MHI] register_process: ' . $e->getMessage());
    $fail('registration_failed');
}

unset($_SESSION['old']);

// Auto-login the new client
loginUser([
    'user_id'     => $userId,
    'full_name'   => $fullName,
    'role'        => 'client',
    'pharmacy_id' => $pharmacyId,
    'phone'       => $phone,
]);

redirectTo('../client/dashboard.php', ['success' => 'registered']);

