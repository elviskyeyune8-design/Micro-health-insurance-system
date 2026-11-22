<?php
/**
 * client/transactions.php — Combined Activity & Transactions Ledger (Content Expansion Design)
 * Wired to client/fetch_data.php ($contributions + $claims)
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$policyNumber = (string) ($client['policy_number'] ?? 'Pending');
$totalContrib = (float) ($client['total_contributed'] ?? 0);
$totalCovered = (float) ($clientStats['total_covered'] ?? 0);

// Merge contributions & claims into one chronological activity stream
$activities = [];
foreach ($contributions as $c) {
    $activities[] = [
        'ref'         => (string) $c['transaction_ref'],
        'type'        => 'deposit',
        'type_label'  => 'Wallet deposit',
        'description' => $c['payment_method_label'] . ' (' . $c['payer_phone'] . ')',
        'created_at'  => (string) $c['created_at'],
        'amount'      => (float) $c['amount'],
        'sign'        => '+',
        'status'      => (string) $c['status'],
        'filter_stat' => $c['status'] === 'completed' ? 'completed' : ($c['status'] === 'pending' ? 'pending' : 'rejected'),
    ];
}
foreach ($claims as $cl) {
    $activities[] = [
        'ref'         => sprintf('CLM-%06d', $cl['claim_id']),
        'type'        => 'claim',
        'type_label'  => 'Prescription claim',
        'description' => $cl['pharmacy_name'] . ' — ' . $cl['prescription_notes'],
        'created_at'  => (string) $cl['created_at'],
        'amount'      => (float) $cl['covered_amount'],
        'sign'        => '−',
        'status'      => (string) $cl['status'],
        'filter_stat' => $cl['status'] === 'approved' ? 'completed' : ($cl['status'] === 'partial' ? 'pending' : 'rejected'),
    ];
}
usort($activities, static fn (array $a, array $b): int => strcmp($b['created_at'], $a['created_at']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions | AfyaMicro Kampala</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/transactions.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('transactions', $client); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('Transactions', 'View all wallet deposits and prescription claims in one place', $client); ?>

        <div class="dashboard-content transactions-content">

            <?= uiAlert($alert) ?>

            <section class="transactions-welcome">
                <div>
                    <span class="eyebrow">ACCOUNT ACTIVITY · <?= e($policyNumber) ?></span>
                    <h2>Your activity history</h2>
                    <p>Review wallet deposits, mobile-money contributions and pharmacy claim deductions.</p>
                </div>
                <a href="wallet.php" class="btn btn-primary">＋ Add Funds</a>
            </section>

            <section class="transaction-summary">
                <article class="transaction-summary-card">
                    <div class="summary-icon deposit-icon">↓</div>
                    <div>
                        <span>Total wallet deposits</span>
                        <strong id="totalDeposits"><?= e(formatUGX($totalContrib)) ?></strong>
                        <small><?= count($contributions) ?> deposit records</small>
                    </div>
                </article>

                <article class="transaction-summary-card">
                    <div class="summary-icon contribution-icon">↗</div>
                    <div>
                        <span>Total covered claims</span>
                        <strong id="totalContributions"><?= e(formatUGX($totalCovered)) ?></strong>
                        <small>Deducted at partner pharmacies</small>
                    </div>
                </article>

                <article class="transaction-summary-card">
                    <div class="summary-icon claim-icon">＋</div>
                    <div>
                        <span>Claims processed</span>
                        <strong id="totalClaims"><?= count($claims) ?></strong>
                        <small>Prescription redemptions</small>
                    </div>
                </article>

                <article class="transaction-summary-card">
                    <div class="summary-icon activity-icon">⇄</div>
                    <div>
                        <span>Total activities</span>
                        <strong id="totalActivities"><?= count($activities) ?></strong>
                        <small>Combined ledger entries</small>
                    </div>
                </article>
            </section>

            <section class="transaction-panel">
                <div class="transaction-panel-heading">
                    <div>
                        <h3>Activity history</h3>
                        <p>Search and filter your recorded wallet and pharmacy activities.</p>
                    </div>
                    <span class="record-count" id="recordCount"><?= count($activities) ?> records</span>
                </div>

                <div class="transaction-filters">
                    <div class="transaction-search">
                        <label for="transactionSearch">Search activity</label>
                        <input type="search" id="transactionSearch"
                            placeholder="Search reference, description or pharmacy...">
                    </div>

                    <div class="transaction-filter">
                        <label for="activityFilter">Activity type</label>
                        <select id="activityFilter">
                            <option value="all">All activities</option>
                            <option value="deposit">Wallet deposits</option>
                            <option value="claim">Prescription claims</option>
                        </select>
                    </div>

                    <div class="transaction-filter">
                        <label for="statusFilter">Status</label>
                        <select id="statusFilter">
                            <option value="all">All statuses</option>
                            <option value="completed">Completed / Approved</option>
                            <option value="pending">Partial (Co-pay)</option>
                            <option value="rejected">Failed / Rejected</option>
                        </select>
                    </div>

                    <button type="button" class="btn btn-outline filter-reset" id="resetFilters">
                        Reset
                    </button>
                </div>

                <div class="transaction-table-wrap">
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Activity</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody">
                            <?php if (empty($activities)): ?>
                                <tr>
                                    <td colspan="6" class="transaction-loading">No transactions recorded yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($activities as $act): ?>
                                    <tr class="tx-row"
                                        data-type="<?= e($act['type']) ?>"
                                        data-status="<?= e($act['filter_stat']) ?>"
                                        data-search="<?= e(strtolower($act['ref'] . ' ' . $act['type_label'] . ' ' . $act['description'])) ?>">
                                        <td><code><?= e($act['ref']) ?></code></td>
                                        <td><strong><?= e($act['type_label']) ?></strong></td>
                                        <td><?= e($act['description']) ?></td>
                                        <td><?= uiDate($act['created_at']) ?></td>
                                        <td><strong><?= e($act['sign'] . ' ' . formatUGX($act['amount'])) ?></strong></td>
                                        <td><?= uiStatusBadge($act['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="transaction-empty hidden" id="transactionEmpty">
                    <div class="empty-icon">⇄</div>
                    <h3>No activity found</h3>
                    <p>There are no records matching your search or filter.</p>
                    <button type="button" class="btn btn-outline" id="emptyResetFilters">Clear filters</button>
                </div>

                <div class="transaction-footer">
                    <span id="transactionFooterText">Showing <?= count($activities) ?> records</span>
                    <span>Amounts displayed in UGX</span>
                </div>
            </section>

            <section class="activity-extra-guide" id="transactions-extra-guide">
                <div class="activity-extra-heading">
                    <span>TRANSACTION INFORMATION</span>
                    <h2>Making sense of your activity</h2>
                    <p>Understand the different records displayed in your AfyaMicro account.</p>
                </div>

                <div class="activity-extra-grid">
                    <article class="activity-extra-card">
                        <h3>Wallet deposits</h3>
                        <p>Amounts credited to your health wallet via MTN Mobile Money, Airtel Money, or Cash Agent.</p>
                    </article>
                    <article class="activity-extra-card">
                        <h3>Prescription claims</h3>
                        <p>Covered medication amounts deducted from your health wallet at accredited partner pharmacies.</p>
                    </article>
                    <article class="activity-extra-card">
                        <h3>Reference codes</h3>
                        <p>Every deposit (MP/AM/CA) and claim (CLM-XXXXXX) carries a unique reference for audit verification.</p>
                    </article>
                    <article class="activity-extra-card">
                        <h3>Search and filtering</h3>
                        <p>Use the search box and filters above to inspect records by activity type or status.</p>
                    </article>
                </div>
            </section>
        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("transactionSearch");
    const activityFilter = document.getElementById("activityFilter");
    const statusFilter = document.getElementById("statusFilter");
    const resetBtn = document.getElementById("resetFilters");
    const emptyResetBtn = document.getElementById("emptyResetFilters");
    const rows = Array.from(document.querySelectorAll(".tx-row"));
    const emptyBox = document.getElementById("transactionEmpty");
    const recordCount = document.getElementById("recordCount");
    const footerText = document.getElementById("transactionFooterText");

    function applyTxFilters() {
        if (!rows.length) return;
        const q = (searchInput ? searchInput.value : "").toLowerCase().trim();
        const type = activityFilter ? activityFilter.value : "all";
        const stat = statusFilter ? statusFilter.value : "all";
        let visible = 0;

        rows.forEach(function (row) {
            const matchType = type === "all" || row.dataset.type === type;
            const matchStat = stat === "all" || row.dataset.status === stat;
            const matchQ = !q || (row.dataset.search || "").includes(q);
            const show = matchType && matchStat && matchQ;
            row.hidden = !show;
            if (show) visible++;
        });

        if (emptyBox) emptyBox.classList.toggle("hidden", visible > 0);
        if (recordCount) recordCount.textContent = visible + " records";
        if (footerText) footerText.textContent = "Showing " + visible + " of " + rows.length + " records";
    }

    function resetAll() {
        if (searchInput) searchInput.value = "";
        if (activityFilter) activityFilter.value = "all";
        if (statusFilter) statusFilter.value = "all";
        applyTxFilters();
    }

    if (searchInput) searchInput.addEventListener("input", applyTxFilters);
    if (activityFilter) activityFilter.addEventListener("change", applyTxFilters);
    if (statusFilter) statusFilter.addEventListener("change", applyTxFilters);
    if (resetBtn) resetBtn.addEventListener("click", resetAll);
    if (emptyResetBtn) emptyResetBtn.addEventListener("click", resetAll);
});
</script>
</body>
</html>
