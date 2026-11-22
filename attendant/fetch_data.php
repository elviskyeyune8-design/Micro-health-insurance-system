<?php
/**
 * attendant/fetch_data.php         (role: attendant)
 * ---------------------------------------------------------------------
 * require_once at the top of attendant views (dashboard.php, lookup.php,
 * receipt.php). Runs requireRole('attendant') and exposes:
 *
 *   $alert                ?array  ['type','code','message'] from ?success= / ?error=
 *   $attendant            ?array  attendant profile + pharmacy details
 *   $search               string  sanitised ?search= value (phone or policy number)
 *   $lookupClient         ?array  matched client + wallet (null if none / no search)
 *   $canClaim             bool    true when $lookupClient can receive a claim
 *   $lookupClaims         array   matched client's last 5 claims
 *   $lookupContributions  array   matched client's last 5 completed deposits
 *   $receipt              ?array  claim details for ?claim_id= (own pharmacy only)
 *   $todayStats           array   claims_count, total_cost, total_covered, total_copay (today, this pharmacy)
 *   $recentClaims         array   last 20 claims processed at this pharmacy
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {   // not a page
    http_response_code(404);
    exit;
}

requireRole('attendant');

$attendantId = (int) $_SESSION['user_id'];
$pharmacyId  = (int) ($_SESSION['pharmacy_id'] ?? 0);
$alert       = statusMessage();

$attendant           = null;
$search              = inputText('search', 30, 'GET');
if ($search === '') {
    $search = inputText('phone', 30, 'GET');
}
$lookupClient        = null;
$canClaim            = false;
$lookupClaims        = [];
$lookupContributions = [];
$receipt             = null;
$todayStats          = ['claims_count' => 0, 'total_cost' => '0.00', 'total_covered' => '0.00', 'total_copay' => '0.00'];
$recentClaims        = [];

try {
    // Attendant + pharmacy
    $stmt = $pdo->prepare(
        'SELECT u.user_id, u.full_name, u.phone,
                p.pharmacy_id, p.name AS pharmacy_name, p.location AS pharmacy_location,
                p.address AS pharmacy_address, p.phone AS pharmacy_phone, p.license_no
           FROM users u
      LEFT JOIN pharmacies p ON p.pharmacy_id = u.pharmacy_id
          WHERE u.user_id = :uid
          LIMIT 1'
    );
    $stmt->execute([':uid' => $attendantId]);
    $attendant = $stmt->fetch() ?: null;

    if ($pharmacyId <= 0 && !$alert) {
        $alert = ['type' => 'error', 'code' => 'no_pharmacy_assigned', 'message' => STATUS_MESSAGES['error']['no_pharmacy_assigned']];
    }

    // ------------------------- Client lookup -------------------------
    if ($search !== '') {
        $phone  = normalizePhone($search);
        $policy = strtoupper(preg_replace('/\s+/', '', $search) ?? '');

        $stmt = $pdo->prepare(
            "SELECT u.user_id AS client_id, u.full_name, u.phone, u.nin, u.status AS user_status,
                    w.wallet_id, w.policy_number, w.plan_name, w.total_contributed,
                    w.available_balance, w.status AS wallet_status, w.start_date,
                    p.name AS home_pharmacy
               FROM users u
               JOIN policies_wallets w ON w.user_id = u.user_id
          LEFT JOIN pharmacies p       ON p.pharmacy_id = u.pharmacy_id
              WHERE u.role = 'client'
                AND (u.phone = :phone OR w.policy_number = :policy)
              LIMIT 1"
        );
        $stmt->execute([':phone' => $phone, ':policy' => $policy]);
        $lookupClient = $stmt->fetch() ?: null;

        if ($lookupClient) {
            $canClaim = $lookupClient['user_status'] === 'active'
                     && $lookupClient['wallet_status'] === 'active'
                     && $pharmacyId > 0;

            $stmt = $pdo->prepare(
                'SELECT c.claim_id, c.prescription_ref, c.total_cost, c.covered_amount, c.client_copay,
                        c.status, c.created_at, p.name AS pharmacy_name
                   FROM claims c
                   JOIN pharmacies p ON p.pharmacy_id = c.pharmacy_id
                  WHERE c.wallet_id = :wid
               ORDER BY c.created_at DESC, c.claim_id DESC
                  LIMIT 5'
            );
            $stmt->execute([':wid' => $lookupClient['wallet_id']]);
            $lookupClaims = $stmt->fetchAll();

            $stmt = $pdo->prepare(
                "SELECT amount, payment_method, transaction_ref, created_at
                   FROM contributions
                  WHERE wallet_id = :wid AND status = 'completed'
               ORDER BY created_at DESC, contribution_id DESC
                  LIMIT 5"
            );
            $stmt->execute([':wid' => $lookupClient['wallet_id']]);
            $lookupContributions = array_map(static function (array $row): array {
                $row['payment_method_label'] = paymentMethodLabel($row['payment_method']);
                return $row;
            }, $stmt->fetchAll());
        } elseif (!$alert) {
            $alert = ['type' => 'error', 'code' => 'client_not_found', 'message' => STATUS_MESSAGES['error']['client_not_found']];
        }
    }

    // ---------------------------- Receipt ----------------------------
    $claimId = inputId('claim_id', 'GET');
    if ($claimId > 0) {
        $stmt = $pdo->prepare(
            'SELECT c.claim_id, c.prescription_ref, c.prescriber_name, c.diagnosis, c.prescription_notes,
                    c.total_cost, c.covered_amount, c.client_copay, c.status, c.created_at,
                    u.user_id AS client_id, u.full_name AS client_name, u.phone AS client_phone,
                    w.policy_number, w.plan_name, w.available_balance AS current_balance,
                    p.name AS pharmacy_name, p.location AS pharmacy_location, p.address AS pharmacy_address,
                    p.phone AS pharmacy_phone, p.license_no AS pharmacy_license,
                    a.full_name AS attendant_name
               FROM claims c
               JOIN policies_wallets w ON w.wallet_id   = c.wallet_id
               JOIN users u            ON u.user_id     = w.user_id
               JOIN pharmacies p       ON p.pharmacy_id = c.pharmacy_id
               JOIN users a            ON a.user_id     = c.attendant_id
              WHERE c.claim_id = :cid AND c.pharmacy_id = :pid
              LIMIT 1'
        );
        $stmt->execute([':cid' => $claimId, ':pid' => $pharmacyId]);
        $receipt = $stmt->fetch() ?: null;

        if ($receipt) {
            $receipt['receipt_no'] = sprintf('CLM-%06d', $receipt['claim_id']);
        } else {
            $alert = ['type' => 'error', 'code' => 'claim_not_found', 'message' => STATUS_MESSAGES['error']['claim_not_found']];
        }
    }

    // ------------------------ Pharmacy activity ----------------------
    if ($pharmacyId > 0) {
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS claims_count,
                    COALESCE(SUM(total_cost), 0)     AS total_cost,
                    COALESCE(SUM(covered_amount), 0) AS total_covered,
                    COALESCE(SUM(client_copay), 0)   AS total_copay
               FROM claims
              WHERE pharmacy_id = :pid AND created_at >= CURDATE()'
        );
        $stmt->execute([':pid' => $pharmacyId]);
        $todayStats = $stmt->fetch() ?: $todayStats;

        $stmt = $pdo->prepare(
            'SELECT c.claim_id, c.prescription_ref, c.total_cost, c.covered_amount, c.client_copay,
                    c.status, c.created_at,
                    u.full_name AS client_name, u.phone AS client_phone, w.policy_number,
                    a.full_name AS attendant_name
               FROM claims c
               JOIN policies_wallets w ON w.wallet_id = c.wallet_id
               JOIN users u            ON u.user_id   = w.user_id
               JOIN users a            ON a.user_id   = c.attendant_id
              WHERE c.pharmacy_id = :pid
           ORDER BY c.created_at DESC, c.claim_id DESC
              LIMIT 20'
        );
        $stmt->execute([':pid' => $pharmacyId]);
        $recentClaims = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log('[MHI] attendant/fetch_data: ' . $e->getMessage());
    $alert = ['type' => 'error', 'code' => 'server_error', 'message' => STATUS_MESSAGES['error']['server_error']];
}

