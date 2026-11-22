<?php
/**
 * client/wallet.php — My Health Wallet & Micro-Deposit Page (Content Expansion Design)
 * Wired to client/fetch_data.php & POST client/deposit_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$fullName      = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'Member'));
$policyNumber  = (string) ($client['policy_number'] ?? 'Pending');
$availableBal  = (float) ($client['available_balance'] ?? 0);
$totalContrib  = (float) ($client['total_contributed'] ?? 0);
$depositsCount = (int) ($clientStats['deposits_count'] ?? 0);

$requestedMethod = inputText('method', 30, 'GET');
$methodMap = [
    'MTN_MoMo'         => 'MTN_MoMo',
    'MTN Mobile Money' => 'MTN_MoMo',
    'Airtel_Money'     => 'Airtel_Money',
    'Airtel Money'     => 'Airtel_Money',
    'Cash_Agent'       => 'Cash_Agent',
    'Pesapal'          => 'Cash_Agent',
    'Visa Card'        => 'MTN_MoMo',
];
$defaultMethod = $methodMap[$requestedMethod] ?? 'MTN_MoMo';

$depositAmtOld = old('amount', '5000');
$methodOld     = old('payment_method', $defaultMethod);
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
    <meta name="description" content="Manage your AfyaMicro health wallet and make micro-deposits.">
    <title>My Wallet | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/wallet.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('wallet', $client); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('My Wallet', 'Manage your AfyaMicro health wallet (' . $policyNumber . ')', $client); ?>

        <div class="wallet-content">

            <?= uiAlert($alert, $extraDetail) ?>

            <section class="wallet-heading">
                <div>
                    <span class="wallet-eyebrow">YOUR FINANCIAL SPACE</span>
                    <h1>My Health Wallet</h1>
                    <p>View your available coverage balance and make instant mobile-money or cash-agent deposits.</p>
                </div>
                <span class="demo-label">POLICY <?= e($policyNumber) ?></span>
            </section>

            <section class="wallet-overview">

                <div class="wallet-balance-card">
                    <div class="wallet-card-top">
                        <span>Available wallet balance</span>
                        <span class="wallet-card-icon">◈</span>
                    </div>

                    <h2 id="walletBalance"><?= e(formatUGX($availableBal)) ?></h2>
                    <p><?= e($client['plan_name'] ?? 'Basic Care Plan') ?> · Active coverage</p>

                    <div class="wallet-card-footer">
                        <span>AfyaMicro Member Wallet</span>
                        <span id="walletMemberName"><?= e($fullName) ?></span>
                    </div>
                </div>

                <div class="wallet-stat-card">
                    <span class="wallet-stat-icon">↗</span>
                    <p>Total contributed</p>
                    <h3 id="totalDeposits"><?= e(formatUGX($totalContrib)) ?></h3>
                    <small>Lifetime wallet deposits</small>
                </div>

                <div class="wallet-stat-card">
                    <span class="wallet-stat-icon">⇄</span>
                    <p>Completed deposits</p>
                    <h3 id="transactionCount"><?= $depositsCount ?></h3>
                    <small>Verified contribution records</small>
                </div>

            </section>

            <section class="wallet-main-grid" id="deposit-panel">

                <div class="wallet-panel deposit-panel">
                    <div class="wallet-panel-heading">
                        <div>
                            <span class="wallet-eyebrow">ADD FUNDS</span>
                            <h2>Make a Deposit</h2>
                            <p>Enter the UGX amount to credit directly to your health wallet.</p>
                        </div>
                        <span class="deposit-icon">＋</span>
                    </div>

                    <form id="depositForm" method="POST" action="deposit_process.php">
                        <?= csrfField() ?>

                        <div class="form-group">
                            <label for="deposit-amount">Deposit amount (UGX)</label>
                            <div class="amount-input-wrap">
                                <span>UGX</span>
                                <input type="number" id="deposit-amount"
                                       name="amount" min="100" max="5000000"
                                       step="100" value="<?= $depositAmtOld ?>"
                                       placeholder="Enter amount"
                                       required>
                            </div>
                            <small class="field-hint">
                                Minimum UGX 100 · Maximum UGX 5,000,000
                            </small>
                        </div>

                        <div class="amount-pills" role="group" aria-label="Preset deposit amounts" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px;">
                            <?php foreach ([2000, 5000, 10000, 20000] as $preset): ?>
                                <button type="button" class="btn btn-outline" style="padding:8px 14px;font-size:12px;" data-deposit-amount="<?= $preset ?>">
                                    <?= e(formatUGX($preset)) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-group">
                            <label for="paymentMethod">Choose payment method</label>

                            <select id="paymentMethod" name="payment_method" required>
                                <?php foreach ($paymentMethods as $code => $label): ?>
                                    <option value="<?= e($code) ?>" <?= $methodOld === $code ? 'selected' : '' ?>>
                                        <?= e($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="field-hint">
                                Generates a unique MP (MTN), AM (Airtel), or CA (Cash Agent) reference.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="paymentPhone">Payment phone number (optional — defaults to <?= e($client['phone'] ?? '') ?>)</label>
                            <input type="tel" id="paymentPhone"
                                   name="payer_phone"
                                   value="<?= $payerPhoneOld ?>"
                                   placeholder="e.g. 0701555111"
                                   maxlength="13">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="depositButton">
                            Deposit Micro-Premium →
                        </button>
                    </form>
                </div>

                <div class="wallet-panel information-panel">
                    <span class="wallet-eyebrow">WALLET INFORMATION</span>
                    <h2>How your wallet works</h2>

                    <div class="wallet-info-item">
                        <span class="info-number">01</span>
                        <div>
                            <h3>Choose an amount</h3>
                            <p>Select a quick preset pill (UGX 2,000–20,000) or enter any custom UGX amount.</p>
                        </div>
                    </div>

                    <div class="wallet-info-item">
                        <span class="info-number">02</span>
                        <div>
                            <h3>Select a payment channel</h3>
                            <p>Pay via MTN Mobile Money, Airtel Money, or an accredited Kampala Cash Agent.</p>
                        </div>
                    </div>

                    <div class="wallet-info-item">
                        <span class="info-number">03</span>
                        <div>
                            <h3>Instant wallet credit</h3>
                            <p>Your available balance and contribution history update atomically inside a locked transaction.</p>
                        </div>
                    </div>

                    <div class="wallet-security-note">
                        <strong>Coverage guarantee</strong>
                        <p>100% of your available balance can be redeemed for prescribed medication at any partner pharmacy.</p>
                    </div>
                </div>

            </section>

            <section class="wallet-panel transactions-panel">
                <div class="wallet-panel-heading transaction-heading">
                    <div>
                        <span class="wallet-eyebrow">WALLET ACTIVITY</span>
                        <h2>Recent Wallet Deposits</h2>
                        <p>Your recorded wallet deposit transactions.</p>
                    </div>
                    <span class="transaction-count" id="transactionLabel"><?= count($contributions) ?> records</span>
                </div>

                <div class="wallet-table-wrap">
                    <table class="wallet-table">
                        <thead>
                            <tr>
                                <th>Transaction Ref</th>
                                <th>Payment method</th>
                                <th>Payer phone</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="walletTransactions">
                            <?php if (empty($contributions)): ?>
                                <tr>
                                    <td colspan="6" class="empty-transactions">
                                        No deposits recorded yet. Use the form above to make your first deposit.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($contributions as $row): ?>
                                    <tr>
                                        <td><code><?= e($row['transaction_ref']) ?></code></td>
                                        <td><?= e($row['payment_method_label']) ?></td>
                                        <td><?= e($row['payer_phone']) ?></td>
                                        <td><?= uiDate($row['created_at']) ?></td>
                                        <td><strong><?= e(formatUGX($row['amount'])) ?></strong></td>
                                        <td><?= uiStatusBadge($row['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="wallet-footer">
                <p>&copy; <?= date('Y') ?> AfyaMicro Kampala. All rights reserved.</p>
                <span>Policy <?= e($policyNumber) ?></span>
            </footer>

        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
