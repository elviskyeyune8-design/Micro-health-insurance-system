<?php
/**
 * admin/fetch_data.php             (role: admin)
 * ---------------------------------------------------------------------
 * require_once at the top of admin views (dashboard.php, pharmacies.php,
 * reports.php ...). Runs requireRole('admin') and exposes:
 *
 *   $alert                  ?array  ['type','code','message'] from ?success= / ?error=
 *   $filters                array   sanitised report filters (re-fill the filter form):
 *                                   date_from, date_to (YYYY-MM-DD), pharmacy_id (int|0),
 *                                   payment_method (code|''), claim_status ('approved'|'partial'|'rejected'|'')
 *   $kpis                   array   system-wide totals (see keys below)
 *   $contributionsByMethod  array   [payment_method, payment_method_label, tx_count, total_amount] (filtered by date)
 *   $monthlyTrend           array   last 6 months: [month 'YYYY-MM', contributions, covered, copay]
 *   $pharmacies             array   all pharmacies + attendants_count, clients_count, claims_count, total_covered
 *   $contributionReport     array   filtered deposits (max 500)
 *   $claimReport            array   filtered claims   (max 500)
 *   $reportTotals           array   totals of the two filtered reports
 *   $clients                array   all clients + wallet (max 500)
 *   $attendants             array   all attendants + pharmacy
 *   $paymentMethods         array   code => label  (for filter dropdowns)
 *   $claimStatuses          array   code => label  (for filter dropdowns)
 *
 * Filter query string (GET):  ?date_from=2026-11-01&date_to=2026-11-30&pharmacy_id=1
 *                             &payment_method=MTN_MoMo&claim_status=partial
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {   // not a page
    http_response_code(404);
    exit;
}

requireRole('admin');

$alert          = statusMessage();
$paymentMethods = PAYMENT_METHODS;
$claimStatuses  = ['approved' => 'Approved (fully covered)', 'partial' => 'Partial (co-pay)', 'rejected' => 'Rejected'];

// ------------------------------ Filters ------------------------------
$isValidDate = static function (string $d): bool {
    $dt = DateTime::createFromFormat('!Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
};

$filters = [
    'date_from'      => inputText('date_from', 10, 'GET'),
    'date_to'        => inputText('date_to', 10, 'GET'),
    'pharmacy_id'    => inputId('pharmacy_id', 'GET'),
    'payment_method' => inputText('payment_method', 20, 'GET'),
    'claim_status'   => inputText('claim_status', 10, 'GET'),
];

$dateError = ($filters['date_from'] !== '' && !$isValidDate($filters['date_from']))
          || ($filters['date_to']   !== '' && !$isValidDate($filters['date_to']))
          || ($filters['date_from'] !== '' && $filters['date_to'] !== '' && $filters['date_from'] > $filters['date_to']);

if ($dateError) {
    $filters['date_from'] = $filters['date_to'] = '';
    $alert ??= ['type' => 'error', 'code' => 'invalid_date_range', 'message' => STATUS_MESSAGES['error']['invalid_date_range']];
}
if (!array_key_exists($filters['payment_method'], PAYMENT_METHODS)) {
    $filters['payment_method'] = '';
}
if (!array_key_exists($filters['claim_status'], $claimStatuses)) {
    $filters['claim_status'] = '';
}

/** Build "created_at" date-range SQL + params for a table alias. */
$dateWhere = static function (string $alias, string $prefix) use ($filters): array {
    $sql = [];
    $params = [];
    if ($filters['date_from'] !== '') {
        $sql[] = "{$alias}.created_at >= :{$prefix}_from";
        $params[":{$prefix}_from"] = $filters['date_from'] . ' 00:00:00';
    }
    if ($filters['date_to'] !== '') {
        $sql[] = "{$alias}.created_at < DATE_ADD(:{$prefix}_to, INTERVAL 1 DAY)";
        $params[":{$prefix}_to"] = $filters['date_to'];
    }
    return [$sql, $params];
};

// ------------------------------ Defaults -----------------------------
$kpis = [
    'total_clients'          => 0,   // registered clients
    'active_wallets'         => 0,   // wallets with status active
    'total_attendants'       => 0,
    'active_pharmacies'      => 0,
    'total_pharmacies'       => 0,
    'contributions_count'    => 0,   // completed deposits
    'total_contributions'    => '0.00',
    'contributions_today'    => '0.00',
    'pool_balance'           => '0.00',  // SUM(available_balance) across wallets
    'claims_count'           => 0,
    'partial_claims'         => 0,
    'total_claims_cost'      => '0.00',
    'total_covered'          => '0.00',
    'total_copay'            => '0.00',
    'new_clients_this_month' => 0,
];
$contributionsByMethod = [];
$monthlyTrend          = [];
$pharmacies            = [];
$contributionReport    = [];
$claimReport           = [];
$reportTotals          = [
    'contributions_count' => 0, 'contributions_amount' => '0.00',
    'claims_count' => 0, 'claims_cost' => '0.00', 'claims_covered' => '0.00', 'claims_copay' => '0.00',
];
$clients    = [];
$attendants = [];

try {
    // ------------------------------ KPIs ------------------------------
    $kpis = $pdo->query(
        "SELECT
            (SELECT COUNT(*) FROM users WHERE role = 'client')                                    AS total_clients,
            (SELECT COUNT(*) FROM policies_wallets WHERE status = 'active')                       AS active_wallets,
            (SELECT COUNT(*) FROM users WHERE role = 'attendant')                                 AS total_attendants,
            (SELECT COUNT(*) FROM pharmacies WHERE status = 'active')                             AS active_pharmacies,
            (SELECT COUNT(*) FROM pharmacies)                                                     AS total_pharmacies,
            (SELECT COUNT(*) FROM contributions WHERE status = 'completed')                       AS contributions_count,
            (SELECT COALESCE(SUM(amount), 0) FROM contributions WHERE status = 'completed')       AS total_contributions,
            (SELECT COALESCE(SUM(amount), 0) FROM contributions
              WHERE status = 'completed' AND created_at >= CURDATE())                             AS contributions_today,
            (SELECT COALESCE(SUM(available_balance), 0) FROM policies_wallets)                    AS pool_balance,
            (SELECT COUNT(*) FROM claims)                                                         AS claims_count,
            (SELECT COUNT(*) FROM claims WHERE status = 'partial')                                AS partial_claims,
            (SELECT COALESCE(SUM(total_cost), 0) FROM claims)                                     AS total_claims_cost,
            (SELECT COALESCE(SUM(covered_amount), 0) FROM claims)                                 AS total_covered,
            (SELECT COALESCE(SUM(client_copay), 0) FROM claims)                                   AS total_copay,
            (SELECT COUNT(*) FROM users WHERE role = 'client'
              AND created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01'))                               AS new_clients_this_month"
    )->fetch() ?: $kpis;

    // -------------------- Contributions by method ---------------------
    [$dw, $dp] = $dateWhere('c', 'cbm');
    $where = array_merge(["c.status = 'completed'"], $dw);
    $stmt = $pdo->prepare(
        'SELECT c.payment_method, COUNT(*) AS tx_count, COALESCE(SUM(c.amount), 0) AS total_amount
           FROM contributions c
          WHERE ' . implode(' AND ', $where) . '
       GROUP BY c.payment_method
       ORDER BY total_amount DESC'
    );
    $stmt->execute($dp);
    $contributionsByMethod = array_map(static function (array $row): array {
        $row['payment_method_label'] = paymentMethodLabel($row['payment_method']);
        return $row;
    }, $stmt->fetchAll());

    // --------------------- Monthly trend (6 months) -------------------
    $months = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = date('Y-m', strtotime("first day of -{$i} month"));
        $months[$m] = ['month' => $m, 'contributions' => '0.00', 'covered' => '0.00', 'copay' => '0.00'];
    }
    $since = array_key_first($months) . '-01';

    $stmt = $pdo->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(amount) AS total
           FROM contributions
          WHERE status = 'completed' AND created_at >= :since
       GROUP BY month"
    );
    $stmt->execute([':since' => $since]);
    foreach ($stmt->fetchAll() as $row) {
        if (isset($months[$row['month']])) {
            $months[$row['month']]['contributions'] = $row['total'];
        }
    }
    $stmt = $pdo->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, SUM(covered_amount) AS covered, SUM(client_copay) AS copay
           FROM claims
          WHERE created_at >= :since
       GROUP BY month"
    );
    $stmt->execute([':since' => $since]);
    foreach ($stmt->fetchAll() as $row) {
        if (isset($months[$row['month']])) {
            $months[$row['month']]['covered'] = $row['covered'];
            $months[$row['month']]['copay']   = $row['copay'];
        }
    }
    $monthlyTrend = array_values($months);

    // ------------------------ Pharmacies + stats ----------------------
    $pharmacies = $pdo->query(
        "SELECT p.pharmacy_id, p.name, p.license_no, p.location, p.address, p.phone, p.status, p.created_at,
                (SELECT COUNT(*) FROM users u WHERE u.pharmacy_id = p.pharmacy_id AND u.role = 'attendant') AS attendants_count,
                (SELECT COUNT(*) FROM users u WHERE u.pharmacy_id = p.pharmacy_id AND u.role = 'client')    AS clients_count,
                (SELECT COUNT(*) FROM claims c WHERE c.pharmacy_id = p.pharmacy_id)                         AS claims_count,
                (SELECT COALESCE(SUM(c.covered_amount), 0) FROM claims c WHERE c.pharmacy_id = p.pharmacy_id) AS total_covered
           FROM pharmacies p
       ORDER BY p.name"
    )->fetchAll();

    // ------------------------ Contribution report ---------------------
    [$dw, $dp] = $dateWhere('c', 'cr');
    $where  = $dw;
    $params = $dp;
    if ($filters['payment_method'] !== '') {
        $where[] = 'c.payment_method = :cr_method';
        $params[':cr_method'] = $filters['payment_method'];
    }
    if ($filters['pharmacy_id'] > 0) {                 // client's home pharmacy
        $where[] = 'u.pharmacy_id = :cr_pharmacy';
        $params[':cr_pharmacy'] = $filters['pharmacy_id'];
    }
    $stmt = $pdo->prepare(
        'SELECT c.contribution_id, c.amount, c.payment_method, c.payer_phone, c.transaction_ref, c.status, c.created_at,
                u.full_name AS client_name, u.phone AS client_phone, w.policy_number,
                p.name AS home_pharmacy
           FROM contributions c
           JOIN policies_wallets w ON w.wallet_id = c.wallet_id
           JOIN users u            ON u.user_id   = w.user_id
      LEFT JOIN pharmacies p       ON p.pharmacy_id = u.pharmacy_id
          ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
       ORDER BY c.created_at DESC, c.contribution_id DESC
          LIMIT 500'
    );
    $stmt->execute($params);
    $contributionReport = array_map(static function (array $row): array {
        $row['payment_method_label'] = paymentMethodLabel($row['payment_method']);
        return $row;
    }, $stmt->fetchAll());

    // --------------------------- Claim report -------------------------
    [$dw, $dp] = $dateWhere('c', 'cl');
    $where  = $dw;
    $params = $dp;
    if ($filters['pharmacy_id'] > 0) {                 // pharmacy where the claim was processed
        $where[] = 'c.pharmacy_id = :cl_pharmacy';
        $params[':cl_pharmacy'] = $filters['pharmacy_id'];
    }
    if ($filters['claim_status'] !== '') {
        $where[] = 'c.status = :cl_status';
        $params[':cl_status'] = $filters['claim_status'];
    }
    $stmt = $pdo->prepare(
        'SELECT c.claim_id, c.prescription_ref, c.diagnosis, c.prescription_notes,
                c.total_cost, c.covered_amount, c.client_copay, c.status, c.created_at,
                u.full_name AS client_name, u.phone AS client_phone, w.policy_number,
                p.name AS pharmacy_name, a.full_name AS attendant_name
           FROM claims c
           JOIN policies_wallets w ON w.wallet_id   = c.wallet_id
           JOIN users u            ON u.user_id     = w.user_id
           JOIN pharmacies p       ON p.pharmacy_id = c.pharmacy_id
           JOIN users a            ON a.user_id     = c.attendant_id
          ' . ($where ? 'WHERE ' . implode(' AND ', $where) : '') . '
       ORDER BY c.created_at DESC, c.claim_id DESC
          LIMIT 500'
    );
    $stmt->execute($params);
    $claimReport = $stmt->fetchAll();

    // ------------------------- Report totals --------------------------
    $completed = array_filter($contributionReport, static fn ($r) => $r['status'] === 'completed');
    $sum = static fn (array $rows, string $col): string =>
        centsToDecimal(array_sum(array_map(static fn ($r) => parseAmountToCents((string) $r[$col]) ?? 0, $rows)));

    $reportTotals = [
        'contributions_count'  => count($completed),
        'contributions_amount' => $sum($completed, 'amount'),
        'claims_count'         => count($claimReport),
        'claims_cost'          => $sum($claimReport, 'total_cost'),
        'claims_covered'       => $sum($claimReport, 'covered_amount'),
        'claims_copay'         => $sum($claimReport, 'client_copay'),
    ];

    // ---------------------------- Clients -----------------------------
    $clients = $pdo->query(
        "SELECT u.user_id, u.full_name, u.phone, u.nin, u.status, u.created_at,
                w.policy_number, w.plan_name, w.total_contributed, w.available_balance,
                w.status AS wallet_status, p.name AS home_pharmacy
           FROM users u
      LEFT JOIN policies_wallets w ON w.user_id = u.user_id
      LEFT JOIN pharmacies p       ON p.pharmacy_id = u.pharmacy_id
          WHERE u.role = 'client'
       ORDER BY u.created_at DESC
          LIMIT 500"
    )->fetchAll();

    // --------------------------- Attendants ---------------------------
    $attendants = $pdo->query(
        "SELECT u.user_id, u.full_name, u.phone, u.status, u.created_at,
                p.pharmacy_id, p.name AS pharmacy_name,
                (SELECT COUNT(*) FROM claims c WHERE c.attendant_id = u.user_id) AS claims_processed
           FROM users u
      LEFT JOIN pharmacies p ON p.pharmacy_id = u.pharmacy_id
          WHERE u.role = 'attendant'
       ORDER BY u.full_name"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('[MHI] admin/fetch_data: ' . $e->getMessage());
    $alert = ['type' => 'error', 'code' => 'server_error', 'message' => STATUS_MESSAGES['error']['server_error']];
}

