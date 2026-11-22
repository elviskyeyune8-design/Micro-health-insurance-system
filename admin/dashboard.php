<?php
/**
 * admin/dashboard.php — Scheme Administrator Overview, Pharmacies & Audit Reports
 * Wired to admin/fetch_data.php & POST admin/add_pharmacy.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$adminName = (string) ($_SESSION['full_name'] ?? 'System Administrator');

$phNameOld     = old('pharmacy_name');
$phLocOld      = old('location_area');
$phLicOld      = old('license_no');
$phPhoneOld    = old('contact_phone');
$phAddrOld     = old('address');

$pharmacyErrors = [
    'missing_fields', 'invalid_pharmacy_name', 'invalid_location',
    'invalid_license', 'invalid_contact_phone', 'duplicate_pharmacy', 'pharmacy_failed',
];
$showPharmacyForm = $phNameOld !== '' || ($alert && in_array($alert['code'], $pharmacyErrors, true));

$hasActiveFilters = $filters['date_from'] !== ''
    || $filters['date_to'] !== ''
    || $filters['pharmacy_id'] > 0
    || $filters['payment_method'] !== ''
    || $filters['claim_status'] !== '';

uiPortalStart([
    'pageTitle'  => 'Admin Overview',
    'title'      => 'Scheme overview',
    'subtitle'   => 'Monitor AfyaMicro operations across Kampala.',
    'userName'   => $adminName,
    'roleLabel'  => 'Scheme Admin',
    'navItems'   => [
        ['href' => '#overview',      'icon' => 'bank',      'label' => 'Scheme overview',    'active' => true],
        ['href' => '#pharmacies',    'icon' => 'store',     'label' => 'Partner pharmacies', 'active' => false],
        ['href' => '#audit-reports', 'icon' => 'clipboard', 'label' => 'Audit reports',      'active' => false],
        ['href' => '#clients-panel', 'icon' => 'users',     'label' => 'Clients & staff',    'active' => false],
    ],
]);
?>

<div class="admin-topline" id="overview">
    <span>Last updated: <?= e(date('d F Y, h:i A')) ?> EAT</span>
    <button type="button" class="btn btn-secondary btn-small" data-print>
        <?= uiIcon('download', 16) ?> Print / Export report
    </button>
</div>

<?= uiAlert($alert) ?>

<section class="kpi-grid">
    <article class="kpi-card card">
        <div class="kpi-icon teal"><?= uiIcon('wallet') ?></div>
        <p>Total pool funds</p>
        <h2><?= e(formatUGX($kpis['pool_balance'])) ?></h2>
        <span><b><?= e(formatUGX($kpis['total_contributions'])) ?></b> lifetime contributed</span>
    </article>

    <article class="kpi-card card">
        <div class="kpi-icon blue"><?= uiIcon('users') ?></div>
        <p>Active clients</p>
        <h2><?= (int) $kpis['total_clients'] ?></h2>
        <span><b>+<?= (int) $kpis['new_clients_this_month'] ?></b> this month · <?= (int) $kpis['active_wallets'] ?> active wallets</span>
    </article>

    <article class="kpi-card card">
        <div class="kpi-icon amber"><?= uiIcon('store') ?></div>
        <p>Partner pharmacies</p>
        <h2><?= (int) $kpis['active_pharmacies'] ?></h2>
        <span><b><?= (int) $kpis['total_pharmacies'] ?></b> total · <?= (int) $kpis['total_attendants'] ?> attendants</span>
    </article>

    <article class="kpi-card card">
        <div class="kpi-icon purple"><?= uiIcon('clipboard') ?></div>
        <p>Total claims paid</p>
        <h2><?= e(formatUGX($kpis['total_covered'])) ?></h2>
        <span><b><?= (int) $kpis['claims_count'] ?></b> claims · <?= e(formatUGX($kpis['total_copay'])) ?> client co-pay</span>
    </article>
</section>

<section class="card management-card" id="pharmacies">
    <div class="management-head">
        <div>
            <h2>Partner pharmacies</h2>
            <p>Manage verified pharmacies serving AfyaMicro clients across Kampala.</p>
        </div>
        <button
            type="button"
            class="btn btn-primary btn-small"
            data-toggle-target="add-pharmacy-panel"
            data-label-open="Add new pharmacy"
            data-label-close="Cancel"
            aria-expanded="<?= $showPharmacyForm ? 'true' : 'false' ?>"
        >
            <?= uiIcon('plus', 17) ?>
            <span data-toggle-label><?= $showPharmacyForm ? 'Cancel' : 'Add new pharmacy' ?></span>
        </button>
    </div>

    <div id="add-pharmacy-panel" class="<?= $showPharmacyForm ? '' : 'is-hidden' ?>">
        <form class="pharmacy-form" method="POST" action="add_pharmacy.php">
            <?= csrfField() ?>

            <label>
                Pharmacy name
                <input
                    type="text"
                    name="pharmacy_name"
                    required
                    minlength="3"
                    maxlength="120"
                    value="<?= $phNameOld ?>"
                    placeholder="e.g. Nakawa Wellness Pharmacy"
                >
            </label>

            <label>
                Location area
                <input
                    type="text"
                    name="location_area"
                    list="kampala-divisions"
                    required
                    minlength="2"
                    maxlength="100"
                    value="<?= $phLocOld ?>"
                    placeholder="e.g. Makindye Division"
                >
                <datalist id="kampala-divisions">
                    <option value="Kampala Central Division">
                    <option value="Kawempe Division">
                    <option value="Makindye Division">
                    <option value="Nakawa Division">
                    <option value="Rubaga Division">
                </datalist>
            </label>

            <label>
                NDA license no.
                <input
                    type="text"
                    name="license_no"
                    required
                    maxlength="50"
                    value="<?= $phLicOld ?>"
                    placeholder="NDA/RP/2026/0701"
                >
            </label>

            <label>
                Contact phone
                <input
                    type="tel"
                    name="contact_phone"
                    required
                    maxlength="13"
                    inputmode="numeric"
                    pattern="(\+?256|0)[0-9]{9}"
                    value="<?= $phPhoneOld ?>"
                    placeholder="0414250404"
                >
            </label>

            <label>
                Address (optional)
                <input
                    type="text"
                    name="address"
                    maxlength="255"
                    value="<?= $phAddrOld ?>"
                    placeholder="Plot / Street"
                >
            </label>

            <button class="btn btn-primary" type="submit">Save pharmacy</button>
        </form>
    </div>

    <?php if (empty($pharmacies)): ?>
        <div class="empty-state">No pharmacies registered yet.</div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Pharmacy name</th>
                        <th>Location area</th>
                        <th>NDA license no.</th>
                        <th>Contact phone</th>
                        <th>Clients / Claims</th>
                        <th>Covered volume</th>
                        <th class="right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pharmacies as $ph): ?>
                        <tr>
                            <td data-label="Pharmacy">
                                <span class="table-store"><?= uiIcon('store') ?></span>
                                <b><?= e($ph['name']) ?></b>
                            </td>
                            <td data-label="Location"><?= e($ph['location']) ?></td>
                            <td data-label="License"><code><?= e($ph['license_no']) ?></code></td>
                            <td data-label="Phone"><?= e($ph['phone']) ?></td>
                            <td data-label="Clients / Claims">
                                <?= (int) $ph['clients_count'] ?> clients · <?= (int) $ph['claims_count'] ?> claims
                            </td>
                            <td data-label="Covered" class="positive"><?= e(formatUGX($ph['total_covered'])) ?></td>
                            <td data-label="Status" class="right"><?= uiStatusBadge($ph['status'], true) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="card audit-card" id="audit-reports">
    <div class="management-head">
        <div>
            <h2>System audit reports</h2>
            <p>Filter contributions and prescription claims across partner pharmacies.</p>
        </div>

        <form class="audit-filters" method="GET" action="dashboard.php#audit-reports">
            <input type="date" name="date_from" value="<?= e($filters['date_from']) ?>" title="From date" aria-label="From date">
            <input type="date" name="date_to" value="<?= e($filters['date_to']) ?>" title="To date" aria-label="To date">

            <select name="pharmacy_id" aria-label="Filter by pharmacy">
                <option value="">All pharmacies</option>
                <?php foreach ($pharmacies as $ph): ?>
                    <?php $pid = (int) $ph['pharmacy_id']; ?>
                    <option value="<?= $pid ?>" <?= $filters['pharmacy_id'] === $pid ? 'selected' : '' ?>>
                        <?= e($ph['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="payment_method" aria-label="Filter by payment channel">
                <option value="">All channels</option>
                <?php foreach ($paymentMethods as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= $filters['payment_method'] === $code ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="claim_status" aria-label="Filter by claim status">
                <option value="">All claim statuses</option>
                <?php foreach ($claimStatuses as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= $filters['claim_status'] === $code ? 'selected' : '' ?>>
                        <?= e($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn btn-primary btn-small" type="submit">Apply filters</button>
            <?php if ($hasActiveFilters): ?>
                <a class="btn btn-secondary btn-small" href="dashboard.php#audit-reports">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="report-summary-strip">
        <span>
            Completed deposits:
            <strong><?= (int) $reportTotals['contributions_count'] ?> (<?= e(formatUGX($reportTotals['contributions_amount'])) ?>)</strong>
        </span>
        <span>
            Claims:
            <strong><?= (int) $reportTotals['claims_count'] ?></strong>
            (Bill <?= e(formatUGX($reportTotals['claims_cost'])) ?> ·
            Covered <strong><?= e(formatUGX($reportTotals['claims_covered'])) ?></strong> ·
            Co-pay <?= e(formatUGX($reportTotals['claims_copay'])) ?>)
        </span>
    </div>

    <div class="tabs activity-tabs" data-tab-group="admin-reports" style="padding-top: 12px;">
        <button type="button" class="active" data-tab-target="report-contributions">
            Contributions <span><?= count($contributionReport) ?></span>
        </button>
        <button type="button" data-tab-target="report-claims">
            Claims <span><?= count($claimReport) ?></span>
        </button>
        <button type="button" data-tab-target="report-channels">
            Channel &amp; Monthly Summary <span><?= count($contributionsByMethod) ?></span>
        </button>
        <button type="button" data-tab-target="report-clients" id="clients-panel">
            Registered Clients <span><?= count($clients) ?></span>
        </button>
    </div>

    <div id="report-contributions" data-tab-panel-group="admin-reports">
        <?php if (empty($contributionReport)): ?>
            <div class="empty-state">No contributions match the selected filters.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Client</th>
                            <th>Policy</th>
                            <th>Channel</th>
                            <th>Payer Phone</th>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th class="right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contributionReport as $row): ?>
                            <tr>
                                <td data-label="Date"><?= uiDate($row['created_at']) ?></td>
                                <td data-label="Client"><b><?= e($row['client_name']) ?></b></td>
                                <td data-label="Policy"><?= e($row['policy_number']) ?></td>
                                <td data-label="Channel">
                                    <span class="type-icon"><?= uiIcon('plus', 14) ?></span>
                                    <?= e($row['payment_method_label']) ?>
                                </td>
                                <td data-label="Payer Phone"><?= e($row['payer_phone']) ?></td>
                                <td data-label="Reference"><code><?= e($row['transaction_ref']) ?></code></td>
                                <td data-label="Amount"><b><?= e(formatUGX($row['amount'])) ?></b></td>
                                <td data-label="Status" class="right"><?= uiStatusBadge($row['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div id="report-claims" class="is-hidden" data-tab-panel-group="admin-reports">
        <?php if (empty($claimReport)): ?>
            <div class="empty-state">No prescription claims match the selected filters.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date &amp; time</th>
                            <th>Client</th>
                            <th>Pharmacy</th>
                            <th>Attendant</th>
                            <th>Prescription notes</th>
                            <th>Total cost</th>
                            <th>Covered</th>
                            <th>Co-pay</th>
                            <th class="right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($claimReport as $row): ?>
                            <tr>
                                <td data-label="Date"><?= uiDate($row['created_at']) ?></td>
                                <td data-label="Client"><b><?= e($row['client_name']) ?></b></td>
                                <td data-label="Pharmacy"><?= e($row['pharmacy_name']) ?></td>
                                <td data-label="Attendant"><?= e($row['attendant_name']) ?></td>
                                <td data-label="Notes" class="td-wrap"><?= e($row['prescription_notes']) ?></td>
                                <td data-label="Total cost"><?= e(formatUGX($row['total_cost'])) ?></td>
                                <td data-label="Covered" class="positive"><?= e(formatUGX($row['covered_amount'])) ?></td>
                                <td data-label="Co-pay"><?= e(formatUGX($row['client_copay'])) ?></td>
                                <td data-label="Status" class="right"><?= uiStatusBadge($row['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div id="report-channels" class="is-hidden" data-tab-panel-group="admin-reports">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Payment Channel</th>
                        <th>Completed Transactions</th>
                        <th class="right">Total Volume</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($contributionsByMethod)): ?>
                        <tr><td colspan="3">No completed contributions in this date range.</td></tr>
                    <?php else: ?>
                        <?php foreach ($contributionsByMethod as $ch): ?>
                            <tr>
                                <td data-label="Channel"><b><?= e($ch['payment_method_label']) ?></b></td>
                                <td data-label="Transactions"><?= (int) $ch['tx_count'] ?></td>
                                <td data-label="Total Volume" class="right positive"><?= e(formatUGX($ch['total_amount'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="table-wrap" style="border-top: 1px solid var(--line);">
            <table>
                <thead>
                    <tr>
                        <th>Month (6-Month Trend)</th>
                        <th>Contributions</th>
                        <th>Claims Covered</th>
                        <th class="right">Client Co-Pay</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthlyTrend as $m): ?>
                        <tr>
                            <td data-label="Month"><code><?= e($m['month']) ?></code></td>
                            <td data-label="Contributions"><?= e(formatUGX($m['contributions'])) ?></td>
                            <td data-label="Covered" class="positive"><?= e(formatUGX($m['covered'])) ?></td>
                            <td data-label="Co-pay" class="right"><?= e(formatUGX($m['copay'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="report-clients" class="is-hidden" data-tab-panel-group="admin-reports">
        <?php if (empty($clients)): ?>
            <div class="empty-state">No registered clients yet.</div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Phone</th>
                            <th>Policy No.</th>
                            <th>Home Pharmacy</th>
                            <th>Contributed</th>
                            <th>Balance</th>
                            <th class="right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clients as $cl): ?>
                            <tr>
                                <td data-label="Client"><b><?= e($cl['full_name']) ?></b></td>
                                <td data-label="Phone"><?= e($cl['phone']) ?></td>
                                <td data-label="Policy"><code><?= e($cl['policy_number'] ?? '—') ?></code></td>
                                <td data-label="Pharmacy"><?= e($cl['home_pharmacy'] ?? '—') ?></td>
                                <td data-label="Contributed"><?= e(formatUGX($cl['total_contributed'] ?? 0)) ?></td>
                                <td data-label="Balance" class="positive"><?= e(formatUGX($cl['available_balance'] ?? 0)) ?></td>
                                <td data-label="Status" class="right"><?= uiStatusBadge($cl['wallet_status'] ?? $cl['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-footer">
        <span>
            Showing <?= count($contributionReport) ?> contributions · <?= count($claimReport) ?> claims · <?= count($clients) ?> clients
        </span>
    </div>
</section>

<?php uiPortalEnd(); ?>
