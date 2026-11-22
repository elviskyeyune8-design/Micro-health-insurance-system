<?php
/**
 * auth/logout.php
 * GET (link) -> destroys the session -> ../index.php?success=logged_out
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

// Clear session data
$_SESSION = [];

// Expire the session cookie with the same parameters it was set with
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $p['path'],
        'domain'   => $p['domain'],
        'secure'   => $p['secure'],
        'httponly' => $p['httponly'],
        'samesite' => $p['samesite'] ?: 'Lax',
    ]);
}

if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

redirectTo('../index.php', ['success' => 'logged_out']);

