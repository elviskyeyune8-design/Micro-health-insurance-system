<?php
/**
 * attendant/receipt.php — Printable Thermal-Style Claim Receipt
 * Wired to attendant/fetch_data.php ($receipt via ?claim_id=)
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$attendantName = (string) ($attendant['full_name'] ?? ($_SESSION['full_name'] ?? 'Attendant'));

uiPortalStart([
    'pageTitle'  => 'Claim Receipt',
    'title'      => 'Claim receipt',
    'subtitle'   => $receipt ? 'Claim ' . $receipt['receipt_no'] . ' processed successfully.' : 'View and print pharmacy claim receipts.',
    'userName'   => $attendantName,
    'roleLabel'  => 'Pharmacy Attendant',
    'navItems'   => [
        ['href' => 'dashboard.php',              'icon' => 'clipboard', 'label' => 'Counter checkout', 'active' => false],
        ['href' => 'dashboard.php#recent-claims','icon' => 'search',    'label' => 'Recent claims',    'active' => false],
        ['href' => '#',                          'icon' => 'receipt',   'label' => 'Claim receipt',    'active' => true],
    ],
]);
?>

<?= uiAlert($alert) ?>

<?php if (!$receipt): ?>
    <section class="card" style="padding: 36px 24px; text-align: center; max-width: 540px; margin: 0 auto;">
        <span class="action-icon"><?= uiIcon('receipt') ?></span>
        <h2 style="font-size: 18px; margin-bottom: 8px;">No claim receipt selected</h2>
        <p style="color: var(--muted); font-size: 12px; margin-bottom: 20px;">
            Look up a client and process a prescription claim, or pick a claim from Recent Claims on the counter dashboard.
        </p>
        <a class="btn btn-primary" href="dashboard.php">
            <?= uiIcon('search') ?> Go to counter lookup
        </a>
    </section>
<?php else: ?>
    <?php
    $isPartial = $receipt['status'] === 'partial';
    $statusHeading = match ($receipt['status']) {
        'partial'  => 'PARTIAL COVERAGE · CO-PAY DUE',
        'rejected' => 'CLAIM REJECTED',
        default    => 'CLAIM APPROVED',
    };
    $subTitle = $isPartial
        ? 'Wallet covered part of the prescription; collect the cash co-pay shown below.'
        : 'The client’s health wallet has been updated.';
    ?>
    <div class="receipt-layout">
        <section class="receipt-paper">
            <div class="receipt-approved <?= $isPartial ? 'partial' : '' ?>">
                <span><?= uiIcon($isPartial ? 'clipboard' : 'check', 28) ?></span>
                <p><?= e($statusHeading) ?></p>
                <h2><?= $isPartial ? 'Co-payment required' : 'Payment successful' ?></h2>
                <small><?= e($subTitle) ?></small>
            </div>

            <div class="receipt-id">
                <span>
                    <small>CLAIM / RECEIPT ID</small>
                    <b><?= e($receipt['receipt_no']) ?> (#<?= (int) $receipt['claim_id'] ?>)</b>
                </span>
                <span>
                    <small>DATE &amp; TIME</small>
                    <b><?= uiDate($receipt['created_at']) ?></b>
                </span>
            </div>

            <div class="receipt-logo">
                <?= uiLogo('dashboard.php') ?>
            </div>

            <div class="receipt-section">
                <h3>Pharmacy details</h3>
                <div>
                    <span>Pharmacy</span>
                    <b><?= e($receipt['pharmacy_name']) ?></b>
                </div>
                <div>
                    <span>Location</span>
                    <b><?= e($receipt['pharmacy_location']) ?></b>
                </div>
                <?php if (!empty($receipt['pharmacy_license'])): ?>
                    <div>
                        <span>NDA Licence</span>
                        <b><?= e($receipt['pharmacy_license']) ?></b>
                    </div>
                <?php endif; ?>
                <div>
                    <span>Attendant</span>
                    <b><?= e($receipt['attendant_name']) ?></b>
                </div>
            </div>

            <div class="receipt-section">
                <h3>Client details</h3>
                <div>
                    <span>Client name</span>
                    <b><?= e($receipt['client_name']) ?></b>
                </div>
                <div>
                    <span>Policy number</span>
                    <b><?= e($receipt['policy_number']) ?></b>
                </div>
                <div>
                    <span>Phone</span>
                    <b><?= e($receipt['client_phone']) ?></b>
                </div>
            </div>

            <div class="receipt-section medication">
                <h3>Medication &amp; prescription notes</h3>
                <p><?= nl2br(e($receipt['prescription_notes'])) ?></p>
                <?php if (!empty($receipt['prescription_ref'])): ?>
                    <div>
                        <span>Rx Reference</span>
                        <b><?= e($receipt['prescription_ref']) ?></b>
                    </div>
                <?php endif; ?>
                <?php if (!empty($receipt['prescriber_name'])): ?>
                    <div>
                        <span>Prescriber</span>
                        <b><?= e($receipt['prescriber_name']) ?></b>
                    </div>
                <?php endif; ?>
                <?php if (!empty($receipt['diagnosis'])): ?>
                    <div>
                        <span>Diagnosis</span>
                        <b><?= e($receipt['diagnosis']) ?></b>
                    </div>
                <?php endif; ?>
            </div>

            <div class="receipt-totals">
                <div>
                    <span>Total prescription cost</span>
                    <b><?= e(formatUGX($receipt['total_cost'])) ?></b>
                </div>
                <div class="covered">
                    <span>Covered by insurance</span>
                    <b>− <?= e(formatUGX($receipt['covered_amount'])) ?></b>
                </div>
                <div class="grand">
                    <span>Client cash co-pay</span>
                    <b><?= e(formatUGX($receipt['client_copay'])) ?></b>
                </div>
            </div>

            <div class="receipt-balance">
                <span>
                    <?= uiIcon('wallet') ?>
                    <span>
                        <small>REMAINING HEALTH WALLET</small>
                        <b><?= e(formatUGX($receipt['current_balance'])) ?></b>
                    </span>
                </span>
                <?= uiIcon('shield') ?>
            </div>

            <div class="receipt-footer">
                <?= uiIcon('heart') ?>
                <b>Thank you for choosing AfyaMicro.</b>
                <p>Affordable health cover, within reach.</p>
                <small>Support: 0800 222 018 · Kampala, Uganda</small>
            </div>
        </section>

        <aside class="receipt-actions card">
            <span class="action-icon"><?= uiIcon('receipt') ?></span>
            <h3>Receipt ready</h3>
            <p>Print a copy for the client or begin a new prescription checkout.</p>
            <button type="button" class="btn btn-primary full" data-print>
                <?= uiIcon('print') ?> Print receipt
            </button>
            <a class="btn btn-secondary full" href="dashboard.php">
                <?= uiIcon('search') ?> New client lookup
            </a>
            <div class="secure-note">
                <?= uiIcon('shield') ?>
                <span>
                    <b>Securely recorded</b>
                    <small>Claim <?= e($receipt['receipt_no']) ?> is saved in the system audit log.</small>
                </span>
            </div>
        </aside>
    </div>
<?php endif; ?>

<?php uiPortalEnd(); ?>

