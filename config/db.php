<?php
/**
 * config/db.php
 * ---------------------------------------------------------------------
 * Central bootstrap: PDO database connection, secure session start and
 * shared helper functions. Include at the top of every PHP page:
 *
 *     require_once __DIR__ . '/../config/db.php';
 *
 * NOTE: DB_PORT is 3307 to match this developer's local XAMPP MySQL.
 *       On a standard XAMPP install change it to 3306.
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

// ---------------------------------------------------------------------
// Application constants
// ---------------------------------------------------------------------
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3307');          // Standard XAMPP = 3306
define('DB_NAME',    'micro_health_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Micro Health Insurance');
define('BASE_URL', '/micro-health-insurance');   // URL path of the project root

/** Business rules (UGX) */
define('MAX_TRANSACTION_UGX', 5000000);          // upper bound for one deposit / claim
define('PAYMENT_METHODS', [
    'MTN_MoMo'     => 'MTN Mobile Money',
    'Airtel_Money' => 'Airtel Money',
    'Cash_Agent'   => 'Cash (Agent)',
]);

date_default_timezone_set('Africa/Kampala');

require_once __DIR__ . '/messages.php';

// ---------------------------------------------------------------------
// PDO connection
// ---------------------------------------------------------------------
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
);

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
    PDO::ATTR_STRINGIFY_FETCHES  => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
    $pdo->exec("SET time_zone = '+03:00'");
} catch (PDOException $e) {
    // Never leak connection details to the browser
    error_log('[MHI] Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Service temporarily unavailable. Please try again later.');
}

// ---------------------------------------------------------------------
// Secure session start (only once, and only if headers not yet sent)
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('MHI_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL . '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// =====================================================================
// Helper functions
// =====================================================================

// ----------------------------- Navigation ----------------------------

/**
 * Redirect and stop. $path may be relative ("../client/dashboard.php")
 * or absolute; $params are appended as a query string.
 */
function redirectTo(string $path, array $params = []): never
{
    if ($params) {
        $path .= (str_contains($path, '?') ? '&' : '?') . http_build_query($params);
    }
    header('Location: ' . $path);
    exit;
}

/** Absolute URL of the dashboard for a role. */
function dashboardUrl(string $role): string
{
    return match ($role) {
        'admin'     => BASE_URL . '/admin/dashboard.php',
        'attendant' => BASE_URL . '/attendant/dashboard.php',
        'client'    => BASE_URL . '/client/dashboard.php',
        default     => BASE_URL . '/index.php',
    };
}

/** True when a user is logged in. */
function isLoggedIn(): bool
{
    return !empty($_SESSION['user_id']) && !empty($_SESSION['role']);
}

/**
 * Restrict a page to one role (or a list of roles).
 * Session keys set at login: user_id, full_name, role, pharmacy_id, phone.
 *
 * @param string|array $role 'admin' | 'attendant' | 'client' (or array of these)
 */
function requireRole(string|array $role): void
{
    if (!isLoggedIn()) {
        redirectTo(BASE_URL . '/auth/login.php', ['error' => 'login_required']);
    }
    if (!in_array($_SESSION['role'], (array) $role, true)) {
        redirectTo(dashboardUrl($_SESSION['role']), ['error' => 'access_denied']);
    }
}

/** Only allow POST; otherwise bounce to $fallback. */
function requirePost(string $fallback): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        redirectTo($fallback, ['error' => 'invalid_request']);
    }
}

/** Log a user in (regenerates the session ID to prevent fixation). */
function loginUser(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id']     = (int) $user['user_id'];
    $_SESSION['full_name']   = $user['full_name'];
    $_SESSION['role']        = $user['role'];
    $_SESSION['pharmacy_id'] = $user['pharmacy_id'] !== null ? (int) $user['pharmacy_id'] : null;
    $_SESSION['phone']       = $user['phone'];
    $_SESSION['login_at']    = time();
    unset($_SESSION['csrf_token']);           // fresh token for the new session
}

// ------------------------------ Security -----------------------------

/** HTML-escape output (use for every user-supplied value echoed into HTML). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Return (and lazily create) the CSRF token for the current session. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to drop inside every POST <form>. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** Validate the CSRF token; on failure redirect to $redirect with ?error=session_expired. */
function verifyCsrf(string $redirect, array $extraParams = []): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        redirectTo($redirect, $extraParams + ['error' => 'session_expired']);
    }
}

// --------------------------- Input sanitising ------------------------

/**
 * Read a request value as a clean single-line string:
 * trims, strips HTML tags and control characters, caps length.
 */
function inputText(string $key, int $maxLen = 255, string $source = 'POST'): string
{
    $raw = ($source === 'GET' ? $_GET : $_POST)[$key] ?? '';
    if (!is_string($raw)) {
        return '';
    }
    $clean = strip_tags($raw);
    $clean = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $clean) ?? '';
    $clean = trim(preg_replace('/\s{2,}/u', ' ', $clean) ?? '');
    return mb_substr($clean, 0, $maxLen);
}

/** Multi-line variant of inputText() (keeps line breaks). */
function inputMultiline(string $key, int $maxLen = 2000, string $source = 'POST'): string
{
    $raw = ($source === 'GET' ? $_GET : $_POST)[$key] ?? '';
    if (!is_string($raw)) {
        return '';
    }
    $clean = strip_tags(str_replace("\r\n", "\n", $raw));
    $clean = preg_replace('/[\x00-\x09\x0B-\x1F\x7F]+/u', ' ', $clean) ?? '';
    return mb_substr(trim($clean), 0, $maxLen);
}

/** Read a positive integer ID (returns 0 if missing/invalid). */
function inputId(string $key, string $source = 'POST'): int
{
    $raw = ($source === 'GET' ? $_GET : $_POST)[$key] ?? '';
    $id  = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? 0 : (int) $id;
}

/** Normalise a phone number: digits only, +256/256 prefix -> 0, 9-digit 7XXXXXXXX -> 07XXXXXXXX. */
function normalizePhone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone) ?? '';
    if (str_starts_with($digits, '256') && strlen($digits) === 12) {
        $digits = '0' . substr($digits, 3);
    } elseif (str_starts_with($digits, '7') && strlen($digits) === 9) {
        $digits = '0' . $digits;
    }
    return $digits;
}

/** Fetch active partner pharmacies for public directory views. */
function publicPharmacies(): array
{
    global $pdo;
    try {
        return $pdo->query(
            "SELECT pharmacy_id, name, license_no, location, address, phone, status
               FROM pharmacies
              WHERE status = 'active'
           ORDER BY name"
        )->fetchAll();
    } catch (PDOException $e) {
        error_log('[MHI] publicPharmacies: ' . $e->getMessage());
        return [];
    }
}

/** Validate a Ugandan mobile number in local format 07XXXXXXXX. */
function isValidUgPhone(string $phone): bool
{
    return (bool) preg_match('/^07[0-9]{8}$/', $phone);
}

// ------------------------------- Money -------------------------------

/**
 * Parse a UGX amount string ("10,500", "10500.50") into integer cents.
 * Returns null when the value is not a valid non-negative amount.
 * Working in integer cents avoids floating-point rounding errors.
 */
function parseAmountToCents(mixed $raw): ?int
{
    if (!is_string($raw) && !is_int($raw) && !is_float($raw)) {
        return null;
    }
    $s = str_replace([',', ' '], '', trim((string) $raw));
    if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/', $s)) {
        return null;
    }
    [$whole, $frac] = array_pad(explode('.', $s), 2, '0');
    return ((int) $whole) * 100 + (int) str_pad($frac, 2, '0');
}

/** Integer cents -> DECIMAL string for SQL ("1050000" -> "10500.00"). */
function centsToDecimal(int $cents): string
{
    return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
}

/**
 * Format an amount as Ugandan Shillings, e.g. 10500 -> "UGX 10,500".
 * UGX has no minor unit in everyday use, so decimals are dropped.
 */
function formatUGX(float|int|string|null $amount): string
{
    return 'UGX ' . number_format((float) ($amount ?? 0), 0, '.', ',');
}

/** Human label for a payment method code. */
function paymentMethodLabel(string $code): string
{
    return PAYMENT_METHODS[$code] ?? $code;
}

// ------------------------- Sticky form values ------------------------

/** Remember non-sensitive form values for one redirect (never passwords). */
function setOld(array $values): void
{
    $_SESSION['old'] = $values;
}

/**
 * Read (escaped) a remembered value: <input value="<?= old('full_name') ?>">.
 * Values are consumed on first use, so they disappear after one page view.
 */
function old(string $key, string $default = ''): string
{
    static $values = null;
    if ($values === null) {
        $values = $_SESSION['old'] ?? [];
        unset($_SESSION['old']);
    }
    return e($values[$key] ?? $default);
}
