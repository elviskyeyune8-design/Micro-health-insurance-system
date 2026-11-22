<?php
/**
 * attendant/dashboard.php — Pharmacy Counter Lookup & Prescription Claim Portal
 * Wired to attendant/fetch_data.php & POST attendant/claim_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$attendantName = (string) ($attendant['full_name'] ?? ($_SESSION['full_name'] ?? 'Attendant'));
$pharmacyName  = (string) ($attendant['pharmacy_name'] ?? 'No pharmacy assigned');
$hasPharmacy   = !empty($attendant['pharmacy_id']);

$notesOld      = old('prescription_notes');
$totalCostOld  = old('total_cost');
$rxRefOld      = old('prescription_ref');
$prescriberOld = old('prescriber_name');
$diagnosisOld  = old('diagnosis');

$latestClaimId = !empty($recentClaims) ? (int) $recentClaims[0]['claim_id'] : 0;

$navItems = [
    ['href' => 'dashboard.php',  'icon' => 'clipboard', 'label' => 'Counter checkout', 'active' => true],
    ['href' => '#recent-claims', 'icon' => 'search',    'label' => 'Recent claims',    'active' => false],
];
if ($latestClaimId > 0) {
    $navItems[] = ['href' => 'receipt.php?claim_id=' . $latestClaimId, 'icon' => 'receipt', 'label' => 'Latest receipt', 'active' => false];
}

uiPortalStart([
    'pageTitle'  => 'Attendant Counter',
    'title'      => 'Counter checkout',
    'subtitle'   => $attendantName . ' · ' . $pharmacyName,
    'userName'   => $attendantName,
    'roleLabel'  => 'Pharmacy Attendant',
    'navItems'   => $navItems,
]);
?>

<div class="counter-status">
    <span class="<?= $hasPharmacy ? '' : 'offline' ?>">
        <i></i>
        <?= $hasPharmacy ? 'PHARMACY ONLINE' : 'NO PHARMACY ASSIGNED' ?>
        · <?= (int) ($todayStats['claims_count'] ?? 0) ?> CLAIMS TODAY
        (<?= e(formatUGX($todayStats['total_covered'] ?? 0)) ?> COVERED)
    </span>
    <small><?= e(date('l, d F Y · h:i A')) ?></small>
</div>

<?= uiAlert($alert) ?>

<section class="lookup-card card">
    <div class="lookup-title">
        <span class="title-icon teal-bg"><?= uiIcon('search') ?></span>
        <div>
            <h2>Verify client eligibility</h2>
            <p>Enter the client’s phone number (e.g. 0701555111) or AfyaMicro policy ID (e.g. MHI-KLA-2026-00001).</p>
        </div>
    </div>
    <form method="GET" action="dashboard.php">
        <div class="lookup-input">
            <?= uiIcon('search', 18) ?>
            <input
                type="text"
                name="search"
                value="<?= e($search) ?>"
                placeholder="0701555111 or MHI-KLA-2026-00001"
                required
                autofocus
            >
        </div>
        <button class="btn btn-primary" type="submit">
            Verify client
        </button>
    </form>
</section>

<?php if ($lookupClient): ?>
    <?php
    $clientBal = (float) $lookupClient['available_balance'];
    $hasBalance = $clientBal > 0;
    ?>
    <div class="counter-grid">
        <section class="verified-card card">
            <div class="verified-head">
                <span class="client-avatar"><?= e(uiInitials($lookupClient['full_name'])) ?></span>
                <div>
                    <?php if ($canClaim): ?>
                        <span class="badge success"><?= uiIcon('check', 12) ?> ELIGIBLE</span>
                    <?php else: ?>
                        <span class="badge danger">INACTIVE</span>
                    <?php endif; ?>
                    <h2><?= e($lookupClient['full_name']) ?></h2>
                    <p><?= e($lookupClient['policy_number']) ?> · <?= e($lookupClient['plan_name']) ?></p>
                </div>
                <?= uiIcon('shield', 31) ?>
            </div>

            <div class="balance-panel <?= $hasBalance ? '' : 'empty' ?>">
                <small>AVAILABLE COVERAGE BALANCE</small>
                <strong><?= e(formatUGX($clientBal)) ?></strong>
                <p>
                    <?= uiIcon($hasBalance ? 'check' : 'close', 14) ?>
                    <?php if (!$canClaim): ?>
                        Client account or wallet is currently inactive.
                    <?php elseif ($hasBalance): ?>
                        Policy active and ready to claim
                    <?php else: ?>
                        Wallet balance is UGX 0 — client must pay full cost in cash.
                    <?php endif; ?>
                </p>
            </div>

            <div class="client-details">
                <span>
                    <small>PHONE NUMBER</small>
                    <b><?= e($lookupClient['phone']) ?></b>
                </span>
                <span>
                    <small>HOME PHARMACY</small>
                    <b><?= e($lookupClient['home_pharmacy'] ?? 'Any partner') ?></b>
                </span>
                <span>
                    <small>TOTAL CONTRIBUTED</small>
                    <b><?= e(formatUGX($lookupClient['total_contributed'])) ?></b>
                </span>
                <span>
                    <small>MEMBER SINCE</small>
                    <b><?= uiDate($lookupClient['start_date'], 'd M Y') ?></b>
                </span>
            </div>

            <?php if (!empty($lookupClaims)): ?>
                <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--line);">
                    <small style="display:block; color:#94a3b8; font-size:9px; font-weight:700; margin-bottom:8px;">RECENT CLIENT CLAIMS</small>
                    <?php foreach (array_slice($lookupClaims, 0, 3) as $lc): ?>
                        <div style="display:flex; justify-content:space-between; font-size:10px; color:#475569; margin-bottom:5px;">
                            <span><?= uiDate($lc['created_at'], 'd M') ?> · <?= e($lc['pharmacy_name']) ?></span>
                            <b><?= e(formatUGX($lc['covered_amount'])) ?> <?= uiStatusBadge($lc['status']) ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <a class="text-button" href="dashboard.php">
                Clear client &amp; start new lookup
            </a>
        </section>

        <?php if ($canClaim): ?>
            <section class="claim-card card">
                <div class="card-title">
                    <span class="title-icon amber-bg"><?= uiIcon('clipboard') ?></span>
                    <div>
                        <h2>Process prescription claim</h2>
                        <p>Enter dispensed medicines and total bill to calculate insurance cover &amp; cash co-pay.</p>
                    </div>
                </div>

                <form method="POST" action="claim_process.php" id="claim-form" data-balance="<?= e((string) $clientBal) ?>">
                    <?= csrfField() ?>
                    <input type="hidden" name="client_id" value="<?= (int) $lookupClient['client_id'] ?>">

                    <label>
                        Prescription notes <em>Required</em>
                        <textarea
                            name="prescription_notes"
                            id="claim-notes"
                            required
                            minlength="3"
                            maxlength="2000"
                            rows="3"
                            placeholder="e.g. Artemether/Lumefantrine 20/120mg x 24 tabs; Paracetamol 500mg x 18 tabs"
                        ><?= $notesOld ?></textarea>
                    </label>

                    <label>
                        Total prescription cost (UGX)
                        <div class="currency-input large">
                            <span>UGX</span>
                            <input
                                type="number"
                                id="claim-total-cost"
                                name="total_cost"
                                min="100"
                                max="5000000"
                                step="100"
                                required
                                inputmode="numeric"
                                value="<?= $totalCostOld ?>"
                                placeholder="12000"
                            >
                        </div>
                    </label>

                    <details class="optional-details" <?= ($rxRefOld !== '' || $prescriberOld !== '' || $diagnosisOld !== '') ? 'open' : '' ?>>
                        <summary>Optional clinical details (Rx ref, prescriber, diagnosis)</summary>
                        <div class="field-row">
                            <label>
                                Prescription Ref
                                <div class="input-wrap">
                                    <input type="text" name="prescription_ref" maxlength="50" value="<?= $rxRefOld ?>" placeholder="RX-KCCA-26-001">
                                </div>
                            </label>
                            <label>
                                Prescriber / Clinic
                                <div class="input-wrap">
                                    <input type="text" name="prescriber_name" maxlength="120" value="<?= $prescriberOld ?>" placeholder="Dr. Okello (HC IV)">
                                </div>
                            </label>
                        </div>
                        <label>
                            Diagnosis
                            <div class="input-wrap">
                                <input type="text" name="diagnosis" maxlength="255" value="<?= $diagnosisOld ?>" placeholder="e.g. Uncomplicated malaria">
                            </div>
                        </label>
                    </details>

                    <div class="breakdown">
                        <div>
                            <small>Insurance covers</small>
                            <strong id="preview-covered">UGX 0</strong>
                            <span>From client’s health wallet</span>
                        </div>
                        <i></i>
                        <div class="copay" id="preview-copay-box">
                            <small>Client cash co-pay</small>
                            <strong id="preview-copay">UGX 0</strong>
                            <span id="preview-copay-note">Enter prescription cost</span>
                        </div>
                    </div>

                    <div class="claim-note">
                        <?= uiIcon('shield', 17) ?>
                        <span id="claim-note-text">Claim will deduct covered amount from the client’s health wallet.</span>
                    </div>

                    <button class="btn btn-primary full" type="submit">
                        Approve &amp; deduct claim <?= uiIcon('check') ?>
                    </button>
                </form>
            </section>
        <?php endif; ?>
    </div>
<?php endif; ?>

<section class="card activity-card" id="recent-claims">
    <div class="activity-header">
        <div>
            <h3>Recent claims at <?= e($pharmacyName) ?></h3>
            <p>Latest prescriptions dispensed at this pharmacy counter.</p>
        </div>
    </div>

    <?php if (empty($recentClaims)): ?>
        <div class="empty-state">No claims processed at this pharmacy yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Receipt</th>
                        <th>Client</th>
                        <th>Policy</th>
                        <th>Total cost</th>
                        <th>Covered</th>
                        <th>Co-pay</th>
                        <th>Status</th>
                        <th class="right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentClaims as $rc): ?>
                        <tr>
                            <td data-label="Date"><?= uiDate($rc['created_at']) ?></td>
                            <td data-label="Receipt"><code><?= e(sprintf('CLM-%06d', $rc['claim_id'])) ?></code></td>
                            <td data-label="Client"><b><?= e($rc['client_name']) ?></b></td>
                            <td data-label="Policy"><?= e($rc['policy_number']) ?></td>
                            <td data-label="Total cost"><?= e(formatUGX($rc['total_cost'])) ?></td>
                            <td data-label="Covered" class="positive"><?= e(formatUGX($rc['covered_amount'])) ?></td>
                            <td data-label="Co-pay"><?= e(formatUGX($rc['client_copay'])) ?></td>
                            <td data-label="Status"><?= uiStatusBadge($rc['status']) ?></td>
                            <td data-label="Action" class="right">
                                <a class="btn btn-secondary btn-small" style="min-height:30px; padding:0 10px; font-size:10px;" href="receipt.php?claim_id=<?= (int) $rc['claim_id'] ?>">
                                    <?= uiIcon('receipt', 13) ?> Receipt
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php uiPortalEnd(); ?>

