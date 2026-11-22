<?php
/**
 * auth/fetch_data.php              (public pages: auth/login.php, auth/register.php)
 * ---------------------------------------------------------------------
 * require_once at the top of the login / registration views. Exposes:
 *
 *   $alert       ?array  ['type','code','message'] from ?success= / ?error=
 *   $pharmacies  array   active pharmacies for the registration <select name="pharmacy_id">
 *
 * Users who are already logged in are redirected to their dashboard.
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {   // not a page
    http_response_code(404);
    exit;
}

if (isLoggedIn()) {
    redirectTo(dashboardUrl($_SESSION['role']));
}

$alert      = statusMessage();
$pharmacies = [];

try {
    $pharmacies = $pdo->query(
        "SELECT pharmacy_id, name, location
           FROM pharmacies
          WHERE status = 'active'
       ORDER BY name"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('[MHI] auth/fetch_data: ' . $e->getMessage());
    $alert = ['type' => 'error', 'code' => 'server_error', 'message' => STATUS_MESSAGES['error']['server_error']];
}

