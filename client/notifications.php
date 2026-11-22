<?php
/**
 * client/notifications.php — Client Notifications Centre (Content Expansion Design)
 * Wired to client/fetch_data.php (builds live notifications from deposits, claims & policy)
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$fullName     = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'Member'));
$policyNumber = (string) ($client['policy_number'] ?? 'Pending');
$availableBal = (float) ($client['available_balance'] ?? 0);

$notifItems = [];

foreach (array_slice($contributions, 0, 10) as $idx => $c) {
    $notifItems[] = [
        'id'       => 'contrib-' . $c['contribution_id'],
        'category' => 'contribution',
        'title'    => 'Contribution credited: ' . formatUGX($c['amount']),
        'message'  => 'Received ' . formatUGX($c['amount']) . ' via ' . $c['payment_method_label']
                    . ' (Ref: ' . $c['transaction_ref'] . ', Payer: ' . $c['payer_phone'] . ').',
        'date'     => uiDate($c['created_at']),
        'raw_date' => (string) $c['created_at'],
        'unread'   => $idx < 2,
    ];
}

foreach (array_slice($claims, 0, 10) as $idx => $cl) {
    $notifItems[] = [
        'id'       => 'claim-' . $cl['claim_id'],
        'category' => 'claim',
        'title'    => 'Prescription claim processed (' . sprintf('CLM-%06d', $cl['claim_id']) . ')',
        'message'  => $cl['pharmacy_name'] . ' dispensed: ' . $cl['prescription_notes']
                    . '. Wallet covered ' . formatUGX($cl['covered_amount'])
                    . ' (Cash co-pay: ' . formatUGX($cl['client_copay']) . ').',
        'date'     => uiDate($cl['created_at']),
        'raw_date' => (string) $cl['created_at'],
        'unread'   => $idx === 0,
    ];
}

$notifItems[] = [
    'id'       => 'wallet-status',
    'category' => 'wallet',
    'title'    => 'Active Health Wallet Balance: ' . formatUGX($availableBal),
    'message'  => 'Your policy ' . $policyNumber . ' is active and ready for prescription coverage at any partner pharmacy.',
    'date'     => uiDate($client['start_date'] ?? null, 'd M Y'),
    'raw_date' => (string) ($client['start_date'] ?? ''),
    'unread'   => false,
];

$notifItems[] = [
    'id'       => 'account-welcome',
    'category' => 'account',
    'title'    => 'Welcome to AfyaMicro Kampala, ' . $fullName,
    'message'  => 'Your policy ' . $policyNumber . ' is linked to ' . ($client['home_pharmacy'] ?? 'our partner pharmacy network') . '.',
    'date'     => uiDate($client['member_since'] ?? null, 'd M Y'),
    'raw_date' => (string) ($client['member_since'] ?? ''),
    'unread'   => false,
];

$totalNotifs  = count($notifItems);
$unreadNotifs = count(array_filter($notifItems, static fn ($n) => $n['unread']));
$readNotifs   = $totalNotifs - $unreadNotifs;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('notifications', $client, $unreadNotifs); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('Notifications', 'Stay updated with your AfyaMicro account activity', $client); ?>

        <div class="dashboard-content notifications-content">

            <section class="notifications-heading">
                <div>
                    <span class="eyebrow">YOUR UPDATES · <?= e($policyNumber) ?></span>
                    <h2>Notification centre</h2>
                    <p>Important updates about your deposits, prescription claims, and policy status.</p>
                </div>

                <button type="button" class="btn btn-outline" id="markAllRead">
                    ✓ Mark all as read
                </button>
            </section>

            <section class="notification-statistics">
                <article class="notification-stat-card">
                    <div class="notification-stat-icon total-icon">♧</div>
                    <div>
                        <span>Total notifications</span>
                        <strong id="totalNotifications"><?= $totalNotifs ?></strong>
                    </div>
                </article>

                <article class="notification-stat-card">
                    <div class="notification-stat-icon unread-icon">●</div>
                    <div>
                        <span>Unread messages</span>
                        <strong id="unreadNotifications"><?= $unreadNotifs ?></strong>
                    </div>
                </article>

                <article class="notification-stat-card">
                    <div class="notification-stat-icon read-icon">✓</div>
                    <div>
                        <span>Read messages</span>
                        <strong id="readNotifications"><?= $readNotifs ?></strong>
                    </div>
                </article>
            </section>

            <section class="notification-panel">

                <div class="notification-panel-heading">
                    <div>
                        <h3>Recent notifications</h3>
                        <p>Manage and review your account updates.</p>
                    </div>

                    <span class="unread-counter" id="unreadCounter"><?= $unreadNotifs ?> unread</span>
                </div>

                <div class="notification-toolbar">
                    <div class="notification-tabs" role="group" aria-label="Filter notifications">
                        <button type="button" class="notification-tab active" data-filter="all">
                            All <span id="allTabCount"><?= $totalNotifs ?></span>
                        </button>

                        <button type="button" class="notification-tab" data-filter="unread">
                            Unread <span id="unreadTabCount"><?= $unreadNotifs ?></span>
                        </button>

                        <button type="button" class="notification-tab" data-filter="read">
                            Read <span id="readTabCount"><?= $readNotifs ?></span>
                        </button>
                    </div>

                    <select id="notificationCategory" aria-label="Filter by category">
                        <option value="all">All categories</option>
                        <option value="account">Account</option>
                        <option value="wallet">Wallet</option>
                        <option value="contribution">Contributions</option>
                        <option value="claim">Health claims</option>
                    </select>
                </div>

                <div class="notification-list" id="notificationList">
                    <?php foreach ($notifItems as $item): ?>
                        <article class="notification-item <?= $item['unread'] ? 'unread' : 'read' ?>"
                                 data-category="<?= e($item['category']) ?>"
                                 data-state="<?= $item['unread'] ? 'unread' : 'read' ?>"
                                 style="padding:18px;border:1px solid var(--border);border-radius:12px;margin-bottom:12px;background:<?= $item['unread'] ? '#F0F9F6' : '#FFFFFF' ?>;">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                                <div>
                                    <span class="badge" style="margin-bottom:8px;"><?= e(ucfirst($item['category'])) ?></span>
                                    <h4 style="margin-bottom:6px;"><?= e($item['title']) ?></h4>
                                    <p style="font-size:13px;color:var(--text);"><?= e($item['message']) ?></p>
                                </div>
                                <small style="color:var(--muted);white-space:nowrap;"><?= e($item['date']) ?></small>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="notification-empty hidden" id="notificationEmpty">
                    <div class="empty-notification-icon">♧</div>
                    <h3>No notifications found</h3>
                    <p>There are no messages matching the selected filter.</p>
                </div>

                <div class="notification-panel-footer">
                    <span id="notificationResults">Showing <?= $totalNotifs ?> notifications</span>
                </div>

            </section>

            <section class="notification-help">
                <div class="notification-help-icon">ⓘ</div>
                <div>
                    <strong>About your notifications</strong>
                    <p>Notifications are generated automatically whenever a deposit or pharmacy claim is recorded on your policy wallet.</p>
                    <a href="../contact.php">Need help? Contact support →</a>
                </div>
            </section>

        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const items = Array.from(document.querySelectorAll(".notification-item"));
    const tabs = Array.from(document.querySelectorAll(".notification-tab"));
    const catSelect = document.getElementById("notificationCategory");
    const markAllBtn = document.getElementById("markAllRead");
    const emptyBox = document.getElementById("notificationEmpty");
    const resultsText = document.getElementById("notificationResults");
    let activeTab = "all";

    function updateCounts() {
        const total = items.length;
        const unread = items.filter(i => i.dataset.state === "unread").length;
        const read = total - unread;
        const setTxt = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        setTxt("totalNotifications", total);
        setTxt("unreadNotifications", unread);
        setTxt("readNotifications", read);
        setTxt("unreadCounter", unread + " unread");
        setTxt("allTabCount", total);
        setTxt("unreadTabCount", unread);
        setTxt("readTabCount", read);
        setTxt("sidebarUnreadCount", unread);
    }

    function filterList() {
        const cat = catSelect ? catSelect.value : "all";
        let shown = 0;
        items.forEach(item => {
            const matchState = activeTab === "all" || item.dataset.state === activeTab;
            const matchCat = cat === "all" || item.dataset.category === cat;
            const visible = matchState && matchCat;
            item.hidden = !visible;
            if (visible) shown++;
        });
        if (emptyBox) emptyBox.classList.toggle("hidden", shown > 0);
        if (resultsText) resultsText.textContent = "Showing " + shown + " notifications";
    }

    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");
            activeTab = tab.dataset.filter || "all";
            filterList();
        });
    });

    if (catSelect) catSelect.addEventListener("change", filterList);

    if (markAllBtn) {
        markAllBtn.addEventListener("click", function () {
            items.forEach(item => {
                item.dataset.state = "read";
                item.classList.remove("unread");
                item.classList.add("read");
                item.style.background = "#FFFFFF";
            });
            updateCounts();
            filterList();
        });
    }
});
</script>
</body>
</html>
