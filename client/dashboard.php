<?php
/**
 * client/dashboard.php — Client Overview & Health Wallet Dashboard (Content Expansion Design)
 * Wired to client/fetch_data.php & POST client/deposit_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$fullName      = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'Member'));
$firstName     = explode(' ', trim($fullName))[0] ?: 'Member';
$policyNumber  = (string) ($client['policy_number'] ?? 'Pending');
$planName      = (string) ($client['plan_name'] ?? 'Basic Care Plan');
$walletStatus  = (string) ($client['wallet_status'] ?? 'active');
$availableBal  = (float) ($client['available_balance'] ?? 0);
$totalContrib  = (float) ($client['total_contributed'] ?? 0);
$totalCovered  = (float) ($clientStats['total_covered'] ?? 0);
$depositsCount = (int) ($clientStats['deposits_count'] ?? 0);
$claimsCount   = (int) ($clientStats['claims_count'] ?? 0);
$memberSince   = uiDate($client['start_date'] ?? ($client['member_since'] ?? null), 'd M Y');

$depositAmtOld = old('amount', '2000');
$methodOld     = old('payment_method', 'MTN_MoMo');
$payerPhoneOld = old('payer_phone', (string) ($client['phone'] ?? ''));

$extraDetail = '';
if ($latestDeposit) {
    $extraDetail = 'Ref: ' . $latestDeposit['transaction_ref']
        . ' (' . formatUGX($latestDeposit['amount']) . ' via ' . $latestDeposit['payment_method_label'] . ')';
}

$notifCount = 1 + min(5, count($contributions)) + min(5, count($claims));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AfyaMicro Kampala client dashboard.">
    <title>My Dashboard | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('dashboard', $client, $notifCount); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('My Dashboard', 'Policy ' . $policyNumber . ' · ' . $planName, $client); ?>

        <?= uiAlert($alert, $extraDetail) ?>

        <!-- WELCOME -->
        <section class="dashboard-welcome">
            <div>
                <h2>Welcome back, <span id="welcomeName"><?= e($firstName) ?></span>!</h2>
                <p>
                    Here is a live overview of your health wallet, mobile-money contributions,
                    and prescription claim activity.
                </p>
            </div>

            <span class="demo-label">
                <span>●</span> POLICY <?= e($policyNumber) ?> · <?= e(strtoupper($walletStatus)) ?>
            </span>
        </section>

        <!-- SUMMARY CARDS -->
        <section class="dashboard-stats">

            <article class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-icon">▣</span>
                </div>
                <div class="stat-label">Health Wallet Balance</div>
                <div class="stat-value" id="summaryBalance"><?= e(formatUGX($availableBal)) ?></div>
                <div class="stat-description">Available prescription cover</div>
            </article>

            <article class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-icon">↗</span>
                </div>
                <div class="stat-label">Total Contributions</div>
                <div class="stat-value" id="summaryContributions"><?= e(formatUGX($totalContrib)) ?></div>
                <div class="stat-description"><?= $depositsCount ?> completed deposit<?= $depositsCount === 1 ? '' : 's' ?></div>
            </article>

            <article class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-icon">♡</span>
                </div>
                <div class="stat-label">Total Claims</div>
                <div class="stat-value" id="summaryClaims"><?= $claimsCount ?></div>
                <div class="stat-description"><?= e(formatUGX($totalCovered)) ?> covered by wallet</div>
            </article>

            <article class="stat-card">
                <div class="stat-card-top">
                    <span class="stat-icon">✓</span>
                </div>
                <div class="stat-label">Account Status</div>
                <div class="stat-value" id="summaryStatus"><?= e(ucfirst($walletStatus)) ?></div>
                <div class="stat-description"><?= e($planName) ?></div>
            </article>

        </section>

        <!-- WALLET AND QUICK MICRO-DEPOSIT -->
        <section class="dashboard-content-grid" id="deposit-section">

            <article class="dashboard-wallet">

                <div class="wallet-label">MY HEALTH WALLET · <?= e($policyNumber) ?></div>

                <div class="wallet-balance" id="walletBalance">
                    <?= e(formatUGX($availableBal)) ?>
                </div>

                <div class="wallet-details">
                    <div>
                        <span>Total contributions</span>
                        <strong id="walletContributions"><?= e(formatUGX($totalContrib)) ?></strong>
                    </div>

                    <div>
                        <span>Member since</span>
                        <strong id="memberSince"><?= e($memberSince) ?></strong>
                    </div>
                </div>

                <div class="wallet-actions">
                    <a href="wallet.php" class="wallet-button primary">
                        + Open Full Wallet
                    </a>

                    <a href="transactions.php" class="wallet-button">
                        View Transactions
                    </a>
                </div>

            </article>

            <article class="dashboard-panel">

                <div class="panel-heading">
                    <h3>Quick Micro-Deposit</h3>
                    <a href="wallet.php">Wallet page →</a>
                </div>

                <form method="POST" action="deposit_process.php">
                    <?= csrfField() ?>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="deposit-amount">Amount to deposit (UGX)</label>
                        <input
                            type="number"
                            id="deposit-amount"
                            name="amount"
                            class="form-control"
                            min="100"
                            max="5000000"
                            step="100"
                            required
                            inputmode="numeric"
                            value="<?= $depositAmtOld ?>"
                            placeholder="2000"
                        >
                    </div>

                    <div class="amount-pills" role="group" aria-label="Preset deposit amounts" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;">
                        <?php foreach ([2000, 5000, 10000, 20000] as $preset): ?>
                            <button type="button" class="btn btn-outline" style="padding:7px 12px;font-size:12px;" data-deposit-amount="<?= $preset ?>">
                                <?= e(formatUGX($preset)) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="quick-method">Payment channel</label>
                        <select id="quick-method" name="payment_method" class="form-control" required>
                            <?php foreach ($paymentMethods as $code => $label): ?>
                                <option value="<?= e($code) ?>" <?= $methodOld === $code ? 'selected' : '' ?>>
                                    <?= e($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 14px;">
                        <label for="quick-phone">Payer phone <em>(optional — defaults to <?= e($client['phone'] ?? '') ?>)</em></label>
                        <input
                            type="tel"
                            id="quick-phone"
                            name="payer_phone"
                            class="form-control"
                            maxlength="13"
                            inputmode="numeric"
                            value="<?= $payerPhoneOld ?>"
                            placeholder="0701555111"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Deposit Micro-Premium →
                    </button>
                </form>

            </article>

        </section>

        <!-- RECENT ACTIVITY AND NOTIFICATIONS -->
        <section class="dashboard-content-grid" id="activity-section">

            <article class="dashboard-panel">

                <div class="panel-heading">
                    <h3>Recent Contributions</h3>
                    <a href="contributions.php">View all (<?= count($contributions) ?>) →</a>
                </div>

                <div class="dashboard-table-wrap">

                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>TRANSACTION</th>
                                <th>DATE</th>
                                <th>AMOUNT</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>

                        <tbody id="recentTransactions">
                            <?php if (empty($contributions)): ?>
                                <tr>
                                    <td colspan="4">No contributions recorded yet. Use Quick Micro-Deposit above to top up.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($contributions, 0, 5) as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="table-primary-text">
                                                <?= e($row['payment_method_label']) ?>
                                            </span>
                                            <span class="table-secondary-text">
                                                Ref: <?= e($row['transaction_ref']) ?> · <?= e($row['payer_phone']) ?>
                                            </span>
                                        </td>
                                        <td><?= uiDate($row['created_at'], 'd M Y') ?></td>
                                        <td><strong><?= e(formatUGX($row['amount'])) ?></strong></td>
                                        <td><?= uiStatusBadge($row['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>

                </div>

            </article>

            <article class="dashboard-panel">

                <div class="panel-heading">
                    <h3>Recent Notifications</h3>
                    <a href="notifications.php">View all →</a>
                </div>

                <div class="dashboard-notice-list" id="dashboardNotifications">

                    <?php if (!empty($contributions)): ?>
                        <?php $latestC = $contributions[0]; ?>
                        <div class="dashboard-notice">
                            <span class="notice-icon">↗</span>
                            <div>
                                <strong>Deposit confirmed (<?= e(formatUGX($latestC['amount'])) ?>)</strong>
                                <p><?= e($latestC['payment_method_label']) ?> · Ref <?= e($latestC['transaction_ref']) ?></p>
                                <time><?= uiDate($latestC['created_at']) ?></time>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($claims)): ?>
                        <?php $latestCl = $claims[0]; ?>
                        <div class="dashboard-notice">
                            <span class="notice-icon">♡</span>
                            <div>
                                <strong>Prescription claim at <?= e($latestCl['pharmacy_name']) ?></strong>
                                <p>Covered <?= e(formatUGX($latestCl['covered_amount'])) ?> · Co-pay <?= e(formatUGX($latestCl['client_copay'])) ?></p>
                                <time><?= uiDate($latestCl['created_at']) ?></time>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="dashboard-notice">
                        <span class="notice-icon">✓</span>
                        <div>
                            <strong>Policy <?= e($policyNumber) ?> active</strong>
                            <p>Present your policy ID or phone number at any partner pharmacy.</p>
                            <time>Member since <?= e($memberSince) ?></time>
                        </div>
                    </div>

                </div>

            </article>

        </section>

        <!-- CLAIMS AND ACCOUNT INFORMATION -->
        <section class="dashboard-content-grid">

            <article class="dashboard-panel">

                <div class="panel-heading">
                    <h3>Claims Overview</h3>
                    <a href="claims.php">View claims (<?= count($claims) ?>) →</a>
                </div>

                <div class="dashboard-table-wrap">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>PHARMACY &amp; NOTES</th>
                                <th>DATE</th>
                                <th>COVERED / CO-PAY</th>
                                <th>STATUS</th>
                            </tr>
                        </thead>

                        <tbody id="dashboardClaims">
                            <?php if (empty($claims)): ?>
                                <tr>
                                    <td colspan="4">No prescription claims recorded on this policy yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (array_slice($claims, 0, 5) as $cl): ?>
                                    <tr>
                                        <td>
                                            <span class="table-primary-text">
                                                <?= e($cl['pharmacy_name']) ?> (<?= e(sprintf('CLM-%06d', $cl['claim_id'])) ?>)
                                            </span>
                                            <span class="table-secondary-text">
                                                <?= e($cl['prescription_notes']) ?>
                                            </span>
                                        </td>
                                        <td><?= uiDate($cl['created_at'], 'd M Y') ?></td>
                                        <td>
                                            <strong><?= e(formatUGX($cl['covered_amount'])) ?></strong>
                                            <small style="display:block;color:var(--dash-muted);">Co-pay: <?= e(formatUGX($cl['client_copay'])) ?></small>
                                        </td>
                                        <td><?= uiStatusBadge($cl['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </article>

            <article class="dashboard-panel">

                <div class="panel-heading">
                    <h3>Account Information</h3>
                    <a href="profile.php">View profile →</a>
                </div>

                <div class="dashboard-notice-list">

                    <div class="dashboard-notice">
                        <span class="notice-icon">♙</span>
                        <div>
                            <strong>Member name &amp; policy</strong>
                            <p id="accountFullName"><?= e($fullName) ?> (<?= e($policyNumber) ?>)</p>
                        </div>
                    </div>

                    <div class="dashboard-notice">
                        <span class="notice-icon">☎</span>
                        <div>
                            <strong>Registered phone</strong>
                            <p id="accountPhone"><?= e($client['phone'] ?? 'Not available') ?></p>
                        </div>
                    </div>

                    <div class="dashboard-notice">
                        <span class="notice-icon">✚</span>
                        <div>
                            <strong>Home pharmacy</strong>
                            <p id="accountPharmacy"><?= e($client['home_pharmacy'] ?? 'Any partner pharmacy') ?><?= !empty($client['home_pharmacy_location']) ? ' · ' . e($client['home_pharmacy_location']) : '' ?></p>
                        </div>
                    </div>

                </div>

                <div class="dashboard-info-card">
                    <h4>Using your cover at partner pharmacies</h4>
                    <p>
                        Present your policy number (<strong><?= e($policyNumber) ?></strong>) or phone number (<strong><?= e($client['phone'] ?? '') ?></strong>) at any partner pharmacy counter in Kampala to redeem prescribed medicines.
                    </p>
                </div>

            </article>

        </section>

        <section class="dashboard-extra-guide" id="dashboard-extra-guide">

            <div class="dashboard-extra-heading">
                <span>MEMBER RESOURCE CENTRE</span>
                <h2>Getting the most from your dashboard</h2>
                <p>Explore and manage every area of your AfyaMicro member account.</p>
            </div>

            <div class="dashboard-extra-grid">

                <article class="dashboard-extra-card">
                    <span>01</span>
                    <h3>Manage your wallet</h3>
                    <p>View your real-time balance, top up via Mobile Money or Cash Agent, and inspect your deposit records.</p>
                    <a href="wallet.php">Explore wallet →</a>
                </article>

                <article class="dashboard-extra-card">
                    <span>02</span>
                    <h3>Track contributions</h3>
                    <p>Review all mobile-money and cash-agent deposits credited to your policy wallet.</p>
                    <a href="contributions.php">View contributions →</a>
                </article>

                <article class="dashboard-extra-card">
                    <span>03</span>
                    <h3>Follow claim activity</h3>
                    <p>Inspect prescription claims processed at partner pharmacies along with covered amounts and cash co-payments.</p>
                    <a href="claims.php">Explore claims →</a>
                </article>

                <article class="dashboard-extra-card">
                    <span>04</span>
                    <h3>Stay informed</h3>
                    <p>Check notifications for deposit confirmations, prescription claim updates, and policy notices.</p>
                    <a href="notifications.php">View notifications →</a>
                </article>

                <article class="dashboard-extra-card">
                    <span>05</span>
                    <h3>Keep your profile updated</h3>
                    <p>Review your member profile, registered phone number, NIN, and preferred home pharmacy.</p>
                    <a href="profile.php">Review profile →</a>
                </article>

                <article class="dashboard-extra-card">
                    <span>06</span>
                    <h3>Find partner pharmacies</h3>
                    <p>Browse accredited partner pharmacies across Kampala divisions where your policy is accepted.</p>
                    <a href="../pharmacies.php">Explore pharmacies →</a>
                </article>

            </div>

        </section>

        <footer style="padding:30px 0 5px;text-align:center;color:#8A9997;font-size:12px;">
            &copy; <span id="dashboardYear"><?= date('Y') ?></span> AfyaMicro Kampala. All rights reserved.
        </footer>

    </main>

</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
