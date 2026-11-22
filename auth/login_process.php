<?php
/**
 * auth/login_process.php
 * POST  phone, password, csrf_token
 * ->    ../client/dashboard.php | ../attendant/dashboard.php | ../admin/dashboard.php
 * Errors -> ../auth/login.php?error=<code>
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

const LOGIN_PAGE = '../auth/login.php';

requirePost(LOGIN_PAGE);
verifyCsrf(LOGIN_PAGE);

$phone    = normalizePhone(inputText('phone', 20));
$password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

if ($phone === '' || $password === '') {
    setOld(['phone' => $phone]);
    redirectTo(LOGIN_PAGE, ['error' => 'missing_fields']);
}
if (!isValidUgPhone($phone)) {
    setOld(['phone' => $phone]);
    redirectTo(LOGIN_PAGE, ['error' => 'invalid_phone']);
}

try {
    $stmt = $pdo->prepare(
        'SELECT user_id, full_name, phone, password_hash, role, pharmacy_id, status
           FROM users
          WHERE phone = :phone
          LIMIT 1'
    );
    $stmt->execute([':phone' => $phone]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    error_log('[MHI] login_process: ' . $e->getMessage());
    redirectTo(LOGIN_PAGE, ['error' => 'server_error']);
}

// Always run password_verify (dummy hash when user not found) to keep timing uniform
$hash  = $user['password_hash'] ?? '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
$valid = password_verify($password, $hash);

if (!$user || !$valid) {
    setOld(['phone' => $phone]);
    redirectTo(LOGIN_PAGE, ['error' => 'invalid_credentials']);
}

if ($user['status'] !== 'active') {
    redirectTo(LOGIN_PAGE, ['error' => 'account_suspended']);
}

// Transparently upgrade the hash if PHP's default algorithm/cost changed
if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    try {
        $pdo->prepare('UPDATE users SET password_hash = :h WHERE user_id = :id')
            ->execute([':h' => password_hash($password, PASSWORD_DEFAULT), ':id' => $user['user_id']]);
    } catch (PDOException $e) {
        error_log('[MHI] login_process rehash: ' . $e->getMessage());
    }
}

loginUser($user);

$destination = match ($user['role']) {
    'admin'     => '../admin/dashboard.php',
    'attendant' => '../attendant/dashboard.php',
    default     => '../client/dashboard.php',
};

redirectTo($destination, ['success' => 'welcome']);

