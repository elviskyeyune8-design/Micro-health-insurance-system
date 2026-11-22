<?php
/**
 * client/claims.php — My Prescription Claims & Co-Pay Estimator (Content Expansion Design)
 * Wired to client/fetch_data.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$policyNumber = (string) ($client['policy_number'] ?? 'Pending');
$availableBal = (float) ($client['available_balance'] ?? 0);
$totalCovered = (float) ($clientStats['total_covered'] ?? 0);
$totalCopay   = (float) ($clientStats['total_copay'] ?? 0);

$approvedCount = 0;
$partialCount  = 0;
foreach ($claims as $c) {
    if (($c['status'] ?? '') === 'approved') {
        $approvedCount++;
    } elseif (($c['status'] ?? '') === 'partial') {
        $partialCount++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Track your AfyaMicro prescription claims and estimate co-payments.">
    <title>My Claims | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/claims.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-layout">

    <?php uiClientSidebar('claims', $client); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('My Claims', 'Prescription claims & co-payment records', $client); ?>

        <div class="claims-content">

            <?= uiAlert($alert) ?>

            <section class="claims-heading">
                <div>
                    <span class="claims-eyebrow">HEALTHCARE SUPPORT</span>
                    <h1>My Prescription Claims</h1>
                    <p>Review prescriptions redeemed at partner pharmacies and estimate coverage on upcoming bills.</p>
                </div>
                <span class="claims-demo-badge">BALANCE: <?= e(formatUGX($availableBal)) ?></span>
            </section>

            <section class="claims-summary">
                <article class="claim-stat-card">
                    <span class="claim-stat-icon">▤</span>
                    <p>Total claims</p>
                    <h2 id="totalClaims"><?= count($claims) ?></h2>
                    <small>Processed at partner pharmacies</small>
                </article>

                <article class="claim-stat-card">
                    <span class="claim-stat-icon approved-icon">✓</span>
                    <p>Fully covered</p>
                    <h2 id="approvedClaims"><?= $approvedCount ?></h2>
                    <small>Zero cash co-pay claims</small>
                </article>

                <article class="claim-stat-card">
                    <span class="claim-stat-icon pending-icon">◷</span>
                    <p>Partial (Co-pay)</p>
                    <h2 id="pendingClaims"><?= $partialCount ?></h2>
                    <small><?= e(formatUGX($totalCopay)) ?> total co-pay</small>
                </article>

                <article class="claim-stat-card">
                    <span class="claim-stat-icon approved-icon">◈</span>
                    <p>Total covered</p>
                    <h2 id="rejectedClaims"><?= e(formatUGX($totalCovered)) ?></h2>
                    <small>Paid from your health wallet</small>
                </article>
            </section>

            <section class="claims-main-grid">

                <div class="claims-panel">
                    <div class="claims-panel-heading">
                        <div>
                            <span class="claims-eyebrow">COVERAGE CALCULATOR</span>
                            <h2>Estimate Prescription Cover &amp; Co-Pay</h2>
                            <p>Check how much your current wallet balance (<strong><?= e(formatUGX($availableBal)) ?></strong>) will cover at a partner pharmacy counter.</p>
                        </div>
                        <span class="claim-plus">＋</span>
                    </div>

                    <form id="claim-form" data-balance="<?= e((string) $availableBal) ?>" onsubmit="return false;">

                        <div class="form-group">
                            <label for="claimPharmacy">Select partner pharmacy</label>
                            <select id="claimPharmacy" class="form-control">
                                <?php foreach ($pharmacies as $ph): ?>
                                    <option value="<?= e($ph['name']) ?>">
                                        <?= e($ph['name']) ?> — <?= e($ph['location']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="claim-total-cost">Estimated prescription bill (UGX)</label>
                            <input type="number" id="claim-total-cost" class="form-control"
                                   min="100" step="100" placeholder="e.g. 15000">
                            <small class="claim-field-hint">Type any UGX bill amount to preview your automatic co-pay split.</small>
                        </div>

                        <div class="breakdown" style="margin: 18px 0;">
                            <div>
                                <small>Insurance covers</small>
                                <strong id="preview-covered">UGX 0</strong>
                                <span>Deducted from wallet</span>
                            </div>
                            <i></i>
                            <div class="copay" id="preview-copay-box">
                                <small>Your cash co-pay</small>
                                <strong id="preview-copay">UGX 0</strong>
                                <span id="preview-copay-note">Enter estimated prescription cost</span>
                            </div>
                        </div>

                        <div class="claim-help-note">
                            <strong>Counter redemption:</strong>
                            <p id="claim-note-text">Present your Policy ID (<strong><?= e($policyNumber) ?></strong>) or phone number (<strong><?= e($client['phone'] ?? '') ?></strong>) to the pharmacy attendant at checkout.</p>
                        </div>
                    </form>
                </div>

                <div class="claims-panel claims-guide">
                    <span class="claims-eyebrow">CLAIM PROCESS</span>
                    <h2>How pharmacy claims work</h2>

                    <div class="claim-guide-step">
                        <span>01</span>
                        <div>
                            <h3>Visit a partner pharmacy</h3>
                            <p>Present your prescription and Policy ID (<?= e($policyNumber) ?>) or phone number at Kisenyi, Kawempe, or Katwe.</p>
                        </div>
                    </div>

                    <div class="claim-guide-step">
                        <span>02</span>
                        <div>
                            <h3>Automatic co-pay split</h3>
                            <p>The attendant enters the prescription cost; your available balance covers up to 100% of the bill automatically.</p>
                        </div>
                    </div>

                    <div class="claim-guide-step">
                        <span>03</span>
                        <div>
                            <h3>Receive thermal receipt</h3>
                            <p>You receive an itemized CLM-XXXXXX receipt showing covered amount, cash co-pay (if any), and remaining wallet balance.</p>
                        </div>
                    </div>

                    <div class="claim-help-note">
                        <strong>Need more cover before visiting?</strong>
                        <p><a href="wallet.php" style="color:var(--primary);font-weight:700;">Top up your health wallet →</a> anytime via MTN MoMo, Airtel Money, or Cash Agent.</p>
                    </div>
                </div>

            </section>

            <section class="claims-panel claims-history">
                <div class="claims-panel-heading history-heading">
                    <div>
                        <span class="claims-eyebrow">CLAIM ACTIVITY</span>
                        <h2>Prescription Claim History</h2>
                        <p>All claims processed against your health wallet at partner pharmacies.</p>
                    </div>
                    <span class="claims-record-count" id="claimRecordCount"><?= count($claims) ?> records</span>
                </div>

                <div class="claims-table-wrap">
                    <table class="claims-table">
                        <thead>
                            <tr>
                                <th>Claim ID</th>
                                <th>Pharmacy &amp; Attendant</th>
                                <th>Medication Notes</th>
                                <th>Date</th>
                                <th>Total Bill</th>
                                <th>Wallet Covered</th>
                                <th>Cash Co-Pay</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="claimsHistory">
                            <?php if (empty($claims)): ?>
                                <tr>
                                    <td colspan="8" class="empty-claims">
                                        No prescription claims have been made on this policy yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($claims as $row): ?>
                                    <tr>
                                        <td><code><?= e(sprintf('CLM-%06d', $row['claim_id'])) ?></code></td>
                                        <td>
                                            <strong><?= e($row['pharmacy_name']) ?></strong>
                                            <small style="display:block;color:var(--muted);"><?= e($row['pharmacy_location']) ?> · <?= e($row['attendant_name']) ?></small>
                                        </td>
                                        <td>
                                            <?= e($row['prescription_notes']) ?>
                                            <?php if (!empty($row['diagnosis'])): ?>
                                                <small style="display:block;color:var(--muted);">Dx: <?= e($row['diagnosis']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= uiDate($row['created_at']) ?></td>
                                        <td><?= e(formatUGX($row['total_cost'])) ?></td>
                                        <td style="color:var(--primary);font-weight:700;"><?= e(formatUGX($row['covered_amount'])) ?></td>
                                        <td><?= e(formatUGX($row['client_copay'])) ?></td>
                                        <td><?= uiStatusBadge($row['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="claims-extra-content" id="claims-extra-guidance">
                <div class="claims-extra-heading">
                    <span class="claims-eyebrow">MEMBER INFORMATION</span>
                    <h2>Understanding your claim records</h2>
                    <p>How AfyaMicro protects your wallet balance and calculates co-payments.</p>
                </div>

                <div class="claims-extra-grid">
                    <article class="claims-extra-card">
                        <h3>Approved (Full Cover)</h3>
                        <p>When your available wallet balance is greater than or equal to the total prescription cost, the wallet covers 100% and your cash co-pay is UGX 0.</p>
                    </article>

                    <article class="claims-extra-card">
                        <h3>Partial Cover (Co-Pay)</h3>
                        <p>When a prescription costs more than your available balance, your entire available balance is applied first and you only pay the remaining difference in cash.</p>
                    </article>

                    <article class="claims-extra-card">
                        <h3>Verified Pharmacy Counters</h3>
                        <p>Only authenticated pharmacy attendants at NDA-licensed partner pharmacies can process a prescription deduction against your wallet.</p>
                    </article>

                    <article class="claims-extra-card">
                        <h3>Atomic Balance Protection</h3>
                        <p>Every claim uses database row locking so your wallet balance can never be overdrawn or double-charged.</p>
                    </article>
                </div>
            </section>

            <footer class="claims-footer">
                <p>&copy; <?= date('Y') ?> AfyaMicro Kampala. All rights reserved.</p>
                <span>Policy <?= e($policyNumber) ?></span>
            </footer>

        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
