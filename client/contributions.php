<?php
/**
 * client/contributions.php — My Contributions Page (Content Expansion Design)
 * Wired to client/fetch_data.php & POST client/deposit_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$policyNumber  = (string) ($client['policy_number'] ?? 'Pending');
$availableBal  = (float) ($client['available_balance'] ?? 0);
$totalContrib  = (float) ($client['total_contributed'] ?? 0);
$depositsCount = (int) ($clientStats['deposits_count'] ?? 0);

$depositAmtOld = old('amount', '5000');
$methodOld     = old('payment_method', 'MTN_MoMo');
$payerPhoneOld = old('payer_phone', (string) ($client['phone'] ?? ''));

$extraDetail = '';
if ($latestDeposit) {
    $extraDetail = 'Ref: ' . $latestDeposit['transaction_ref']
        . ' (' . formatUGX($latestDeposit['amount']) . ' via ' . $latestDeposit['payment_method_label'] . ')';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Manage your AfyaMicro contributions.">
    <title>My Contributions | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/contributions.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('contributions', $client); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('Contributions', 'Track your healthcare micro-contributions', $client); ?>

        <div class="contributions-content">

            <?= uiAlert($alert, $extraDetail) ?>

            <section class="contributions-heading">
                <div>
                    <span class="contribution-eyebrow">YOUR MEMBERSHIP</span>
                    <h1>My Contributions</h1>
                    <p>Review your contribution activity and add a new micro-contribution to your policy wallet.</p>
                </div>
                <span class="contribution-demo-badge"><?= e($policyNumber) ?></span>
            </section>

            <section class="contribution-summary">

                <article class="contribution-summary-card total-card">
                    <div class="summary-card-icon">↗</div>
                    <p>Total contributions</p>
                    <h2 id="totalContributions"><?= e(formatUGX($totalContrib)) ?></h2>
                    <small>Lifetime completed contributions</small>
                </article>

                <article class="contribution-summary-card">
                    <div class="summary-card-icon">◈</div>
                    <p>Available wallet balance</p>
                    <h2 id="availableBalance"><?= e(formatUGX($availableBal)) ?></h2>
                    <small>Spendable at partner pharmacies</small>
                </article>

                <article class="contribution-summary-card">
                    <div class="summary-card-icon">▤</div>
                    <p>Contribution records</p>
                    <h2 id="contributionCount"><?= $depositsCount ?></h2>
                    <small>Completed deposit transactions</small>
                </article>

            </section>

            <section class="contribution-main-grid">

                <div class="contribution-panel">
                    <div class="contribution-panel-heading">
                        <div>
                            <span class="contribution-eyebrow">ADD A CONTRIBUTION</span>
                            <h2>Make a Contribution</h2>
                            <p>Top up your AfyaMicro health wallet balance.</p>
                        </div>
                        <span class="contribution-plus">＋</span>
                    </div>

                    <form id="contributionForm" method="POST" action="deposit_process.php">
                        <?= csrfField() ?>

                        <div class="contribution-balance-box">
                            <span>Current available balance</span>
                            <strong id="formWalletBalance"><?= e(formatUGX($availableBal)) ?></strong>
                        </div>

                        <div class="form-group">
                            <label for="deposit-amount">Contribution amount (UGX)</label>
                            <input type="number" id="deposit-amount"
                                   name="amount" min="100" max="5000000" step="100"
                                   value="<?= $depositAmtOld ?>"
                                   placeholder="Enter contribution amount" required>
                            <small class="contribution-hint">
                                Minimum UGX 100 · Maximum UGX 5,000,000.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="contribMethod">Payment channel</label>
                            <select id="contribMethod" name="payment_method" class="form-control" required>
                                <?php foreach ($paymentMethods as $code => $label): ?>
                                    <option value="<?= e($code) ?>" <?= $methodOld === $code ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="contribPhone">Payer phone number (optional)</label>
                            <input type="tel" id="contribPhone" name="payer_phone"
                                   class="form-control" maxlength="13"
                                   value="<?= $payerPhoneOld ?>"
                                   placeholder="0701555111">
                        </div>

                        <div class="contribution-confirmation">
                            <span>Credited policy</span>
                            <strong><?= e($policyNumber) ?> (<?= e($client['plan_name'] ?? 'Basic Care Plan') ?>)</strong>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="contributionButton">
                            Submit Contribution →
                        </button>
                    </form>
                </div>

                <div class="contribution-panel contribution-guide">
                    <span class="contribution-eyebrow">CONTRIBUTION GUIDE</span>
                    <h2>How contributions work</h2>

                    <div class="guide-step">
                        <span>01</span>
                        <div>
                            <h3>Flexible micro-savings</h3>
                            <p>Contribute daily, weekly, or whenever you earn—with no fixed monthly lock-in penalties.</p>
                        </div>
                    </div>

                    <div class="guide-step">
                        <span>02</span>
                        <div>
                            <h3>Mobile money &amp; agent support</h3>
                            <p>Pay from your own phone or a family member's number via MTN MoMo, Airtel Money, or Cash Agent.</p>
                        </div>
                    </div>

                    <div class="guide-step">
                        <span>03</span>
                        <div>
                            <h3>Auditable reference codes</h3>
                            <p>Every contribution generates a unique reference (MP..., AM..., or CA...) stored permanently in your ledger.</p>
                        </div>
                    </div>

                    <div class="contribution-tip">
                        <strong>Keep track of your activity</strong>
                        <p>Your recorded contributions appear immediately in the history table below.</p>
                    </div>
                </div>

            </section>

            <section class="contribution-panel contribution-history">
                <div class="contribution-panel-heading history-heading">
                    <div>
                        <span class="contribution-eyebrow">YOUR ACTIVITY</span>
                        <h2>Contribution History</h2>
                        <p>A complete record of your policy contributions.</p>
                    </div>
                    <span class="history-count" id="historyCount"><?= count($contributions) ?> records</span>
                </div>

                <div class="contribution-table-wrap">
                    <table class="contribution-table">
                        <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Date</th>
                                <th>Channel &amp; Payer</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="contributionHistory">
                            <?php if (empty($contributions)): ?>
                                <tr>
                                    <td colspan="5" class="empty-contributions">
                                        No contributions recorded yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contributions as $row): ?>
                                    <tr>
                                        <td><code><?= e($row['transaction_ref']) ?></code></td>
                                        <td><?= uiDate($row['created_at']) ?></td>
                                        <td><?= e($row['payment_method_label']) ?> · <?= e($row['payer_phone']) ?></td>
                                        <td><strong><?= e(formatUGX($row['amount'])) ?></strong></td>
                                        <td><?= uiStatusBadge($row['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="activity-extra-guide" id="contributions-extra-guide">
                <div class="activity-extra-heading">
                    <span>MEMBER CONTRIBUTION GUIDE</span>
                    <h2>Understanding your contributions</h2>
                    <p>Learn how contribution records help members follow their healthcare coverage activity.</p>
                </div>

                <div class="activity-extra-grid">
                    <article class="activity-extra-card">
                        <h3>Record your contributions</h3>
                        <p>Each contribution is displayed in your activity history with its reference, timestamp, payment channel, amount and status.</p>
                    </article>
                    <article class="activity-extra-card">
                        <h3>Review your history</h3>
                        <p>Use the history table to verify every shilling contributed toward your healthcare protection.</p>
                    </article>
                    <article class="activity-extra-card">
                        <h3>Direct wallet credit</h3>
                        <p>Every completed contribution increases both your lifetime total contributed and your spendable wallet balance.</p>
                    </article>
                    <article class="activity-extra-card">
                        <h3>Family support</h3>
                        <p>Relatives can contribute to your wallet by entering their mobile money number in the payer phone field.</p>
                    </article>
                </div>
            </section>

            <footer class="contribution-footer">
                <p>&copy; <?= date('Y') ?> AfyaMicro Kampala. All rights reserved.</p>
                <span>Policy <?= e($policyNumber) ?></span>
            </footer>

        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
