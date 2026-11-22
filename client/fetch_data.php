<?php
/**
 * client/fetch_data.php            (role: client)
 * ---------------------------------------------------------------------
 * require_once at the top of any client view (dashboard.php, deposit.php,
 * history.php ...). Runs requireRole('client') and exposes:
 *
 *   $alert           ?array  ['type','code','message'] from ?success= / ?error=
 *   $client          ?array  profile + policy/wallet (see SELECT below)
 *   $clientStats     array   deposits_count, failed_deposits, last_deposit_at,
 *                            claims_count, total_covered, total_copay
 *   $contributions   array   deposit history (newest first, max 100)
 *   $claims          array   prescription claim history (newest first, max 100)
 *   $latestDeposit   ?array  the deposit just made (when ?ref= is present)
 *   $paymentMethods  array   code => label  (for the deposit form)
 *   $pharmacies      array   active partner pharmacies
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {   // not a page
    http_response_code(404);
    exit;
}

requireRole('client');

$userId         = (int) $_SESSION['user_id'];
$alert          = statusMessage();
$paymentMethods = PAYMENT_METHODS;

$client        = null;
$clientStats   = [
    'deposits_count'  => 0,
    'failed_deposits' => 0,
    'last_deposit_at' => null,
    'claims_count'    => 0,
    'total_covered'   => '0.00',
    'total_copay'     => '0.00',
];
$contributions = [];
$claims        = [];
$latestDeposit = null;
$pharmacies    = [];

try {
    // Profile + wallet + home pharmacy
    $stmt = $pdo->prepare(
        'SELECT u.user_id, u.full_name, u.phone, u.nin, u.created_at AS member_since,
                w.wallet_id, w.policy_number, w.plan_name, w.total_contributed,
                w.available_balance, w.status AS wallet_status, w.start_date,
                p.pharmacy_id AS home_pharmacy_id, p.name AS home_pharmacy,
                p.location AS home_pharmacy_location, p.phone AS home_pharmacy_phone
           FROM users u
      LEFT JOIN policies_wallets w ON w.user_id = u.user_id
      LEFT JOIN pharmacies p       ON p.pharmacy_id = u.pharmacy_id
          WHERE u.user_id = :uid
          LIMIT 1'
    );
    $stmt->execute([':uid' => $userId]);
    $client = $stmt->fetch() ?: null;

    $walletId = (int) ($client['wallet_id'] ?? 0);

    if ($walletId > 0) {
        // Deposit history
        $stmt = $pdo->prepare(
            'SELECT contribution_id, amount, payment_method, payer_phone, transaction_ref, status, created_at
               FROM contributions
              WHERE wallet_id = :wid
           ORDER BY created_at DESC, contribution_id DESC
              LIMIT 100'
        );
        $stmt->execute([':wid' => $walletId]);
        $contributions = array_map(static function (array $row): array {
            $row['payment_method_label'] = paymentMethodLabel($row['payment_method']);
            return $row;
        }, $stmt->fetchAll());

        // Claim history
        $stmt = $pdo->prepare(
            'SELECT c.claim_id, c.prescription_ref, c.prescriber_name, c.diagnosis, c.prescription_notes,
                    c.total_cost, c.covered_amount, c.client_copay, c.status, c.created_at,
                    p.name AS pharmacy_name, p.location AS pharmacy_location,
                    a.full_name AS attendant_name
               FROM claims c
               JOIN pharmacies p ON p.pharmacy_id = c.pharmacy_id
               JOIN users a      ON a.user_id     = c.attendant_id
              WHERE c.wallet_id = :wid
           ORDER BY c.created_at DESC, c.claim_id DESC
              LIMIT 100'
        );
        $stmt->execute([':wid' => $walletId]);
        $claims = $stmt->fetchAll();

        // Summary stats
        $stmt = $pdo->prepare(
            "SELECT
                (SELECT COUNT(*) FROM contributions WHERE wallet_id = :w1 AND status = 'completed') AS deposits_count,
                (SELECT COUNT(*) FROM contributions WHERE wallet_id = :w2 AND status = 'failed')    AS failed_deposits,
                (SELECT MAX(created_at) FROM contributions WHERE wallet_id = :w3 AND status = 'completed') AS last_deposit_at,
                (SELECT COUNT(*) FROM claims WHERE wallet_id = :w4)                                  AS claims_count,
                (SELECT COALESCE(SUM(covered_amount), 0) FROM claims WHERE wallet_id = :w5)         AS total_covered,
                (SELECT COALESCE(SUM(client_copay), 0)   FROM claims WHERE wallet_id = :w6)         AS total_copay"
        );
        $stmt->execute([':w1' => $walletId, ':w2' => $walletId, ':w3' => $walletId,
                        ':w4' => $walletId, ':w5' => $walletId, ':w6' => $walletId]);
        $clientStats = $stmt->fetch() ?: $clientStats;

        // Deposit just completed (?ref=...)
        $ref = inputText('ref', 50, 'GET');
        if ($ref !== '' && preg_match('/^[A-Z]{2}[0-9.]+[A-F0-9]+$/', $ref)) {
            $stmt = $pdo->prepare(
                'SELECT contribution_id, amount, payment_method, payer_phone, transaction_ref, status, created_at
                   FROM contributions
                  WHERE wallet_id = :wid AND transaction_ref = :ref
                  LIMIT 1'
            );
            $stmt->execute([':wid' => $walletId, ':ref' => $ref]);
            $latestDeposit = $stmt->fetch() ?: null;
            if ($latestDeposit) {
                $latestDeposit['payment_method_label'] = paymentMethodLabel($latestDeposit['payment_method']);
            }
        }
    }

    // Partner pharmacies (where the cover can be used)
    $pharmacies = $pdo->query(
        "SELECT pharmacy_id, name, location, address, phone
           FROM pharmacies
          WHERE status = 'active'
       ORDER BY name"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('[MHI] client/fetch_data: ' . $e->getMessage());
    $alert = ['type' => 'error', 'code' => 'server_error', 'message' => STATUS_MESSAGES['error']['server_error']];
}

