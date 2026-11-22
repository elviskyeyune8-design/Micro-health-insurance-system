<?php
/**
 * config/ui.php
 * ---------------------------------------------------------------------
 * Pure presentation helpers (inline SVG icons, brand logo, alert box,
 * status badges, public site header/footer, client sidebar/topbar, and
 * portal shell wrapper) styled with the feature/afyamic-content-expansion
 * design system. Contains ZERO SQL queries.
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

/** Return an inline 24x24 stroke SVG icon. */
function uiIcon(string $name, int $size = 20): string
{
    static $paths = [
        'arrow'     => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'bank'      => '<path d="m3 10 9-6 9 6"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8M3 21h18"/>',
        'check'     => '<path d="m5 12 4 4L19 6"/>',
        'chevron'   => '<path d="m9 18 6-6-6-6"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4.5V3h6v1.5M9 11h6M9 15h4"/>',
        'close'     => '<path d="m6 6 12 12M18 6 6 18"/>',
        'download'  => '<path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>',
        'eye'       => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
        'heart'     => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.7-7.5 1.1-1.1a5.5 5.5 0 0 0 0-7.8Z"/>',
        'home'      => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>',
        'lock'      => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9Z"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'print'     => '<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/>',
        'receipt'   => '<path d="M5 3v18l3-2 4 2 4-2 3 2V3l-3 2-4-2-4 2-3-2Z"/><path d="M9 10h6M9 14h6"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'shield'    => '<path d="M12 22s8-3.5 8-10V5l-8-3-8 3v7c0 6.5 8 10 8 10Z"/><path d="m9 12 2 2 4-5"/>',
        'store'     => '<path d="M4 10v10h16V10M3 4h18l-2 6a3 3 0 0 1-4 0 3 3 0 0 1-6 0 3 3 0 0 1-4 0L3 4Z"/><path d="M9 20v-5h6v5"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'wallet'    => '<path d="M3 6h15a3 3 0 0 1 3 3v10H5a2 2 0 0 1-2-2V6Z"/><path d="M3 6a3 3 0 0 1 3-3h11v3M16 11h5v5h-5a2.5 2.5 0 0 1 0-5Z"/>',
    ];

    $body = $paths[$name] ?? '';
    return '<svg aria-hidden="true" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
}

/** Render the AfyaMicro Kampala brand mark using afyamicrologo.svg. */
function uiLogo(string $href, bool $inverse = false, string $assetPrefix = '../'): string
{
    $cls = $inverse ? 'logo logo-inverse' : 'logo';
    $imgStyle = $inverse ? ' style="filter: brightness(0) invert(1);"' : '';
    return '<a class="' . $cls . '" href="' . e($href) . '" aria-label="AfyaMicro Kampala home">'
        . '<img src="' . e($assetPrefix) . 'assets/images/afyamicrologo.svg" alt="AfyaMicro Kampala"' . $imgStyle . '>'
        . '</a>';
}

/** Render a dismissible flash / status alert banner from $alert. */
function uiAlert(?array $alert, string $extraDetail = ''): string
{
    if (!$alert) {
        return '';
    }
    $type = in_array($alert['type'], ['success', 'error', 'info'], true) ? $alert['type'] : 'info';
    $icon = $type === 'success' ? 'check' : ($type === 'error' ? 'close' : 'shield');
    $detail = $extraDetail !== '' ? ' <strong>' . e($extraDetail) . '</strong>' : '';

    return '<div class="alert alert-' . e($type) . '" role="alert">'
        . uiIcon($icon, 16)
        . '<span>' . e($alert['message']) . $detail . '</span>'
        . '<button type="button" class="alert-dismiss" data-alert-dismiss aria-label="Dismiss">' . uiIcon('close', 14) . '</button>'
        . '</div>';
}

/** Extract 2 uppercase initials from a person's full name. */
function uiInitials(string $fullName): string
{
    $parts = preg_split('/\s+/', trim($fullName)) ?: [];
    $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
    if (count($parts) === 0) {
        return 'AM';
    }
    if (count($parts) === 1) {
        return strtoupper(mb_substr($parts[0], 0, 2));
    }
    return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
}

/** Format a MySQL datetime/date safely for display. */
function uiDate(?string $datetime, string $format = 'd M Y · h:i A'): string
{
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    return $ts === false ? e($datetime) : date($format, $ts);
}

/** Render a status badge matching the AfyaMicro palette. */
function uiStatusBadge(string $status, bool $withDot = false): string
{
    $s = strtolower(trim($status));
    $cls = match ($s) {
        'active', 'completed', 'approved', 'success' => 'status-success success',
        'pending', 'partial'                         => 'status-warning warning',
        default                                      => 'status-danger danger',
    };
    $dot = $withDot ? '<i></i> ' : '';
    return '<span class="status-pill badge ' . $cls . '">' . $dot . e(strtoupper($status)) . '</span>';
}

/** Render the shared public site header from feature/afyamic-content-expansion. */
function uiPublicHeader(string $activePage = 'home', bool $loggedIn = false): void
{
    $role = (string) ($_SESSION['role'] ?? 'client');
    $portalUrl = match ($role) {
        'admin'     => 'admin/dashboard.php',
        'attendant' => 'attendant/dashboard.php',
        default     => 'client/dashboard.php',
    };
    ?>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="brand-logo">
            <img src="assets/images/afyamicrologo.svg" alt="AfyaMicro Kampala logo">
        </a>

        <button class="menu-toggle" id="menuToggle"
                aria-label="Open navigation menu"
                aria-expanded="false">
            <span></span><span></span><span></span>
        </button>

        <nav class="nav-links" id="mainNav">
            <a href="index.php" class="<?= $activePage === 'home' ? 'active' : '' ?>">Home</a>
            <a href="how-it-works.php" class="<?= $activePage === 'how-it-works' ? 'active' : '' ?>">How It Works</a>
            <a href="pharmacies.php" class="<?= $activePage === 'pharmacies' ? 'active' : '' ?>">Partner Pharmacies</a>
            <a href="about.php" class="<?= $activePage === 'about' ? 'active' : '' ?>">About Us</a>
            <a href="contact.php" class="<?= $activePage === 'contact' ? 'active' : '' ?>">Contact</a>
        </nav>

        <div class="header-actions">
            <?php if ($loggedIn): ?>
                <a href="<?= e($portalUrl) ?>" class="btn btn-primary">My Dashboard</a>
                <a href="auth/logout.php" class="btn btn-outline">Sign Out</a>
            <?php else: ?>
                <a href="auth/login.php" class="btn btn-outline">Sign In</a>
                <a href="auth/register.php" class="btn btn-primary">Get Covered</a>
            <?php endif; ?>
        </div>
    </div>
</header>
    <?php
}

/** Render the shared public site footer from feature/afyamic-content-expansion. */
function uiPublicFooter(): void
{
    ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="index.php">
                    <img src="assets/images/afyamicrologo.svg" alt="AfyaMicro Kampala logo">
                </a>
                <p>
                    Making healthcare preparation simpler through
                    accessible digital health wallet experiences across Kampala.
                </p>
            </div>

            <div class="footer-column">
                <h3>Explore</h3>
                <a href="index.php">Home</a>
                <a href="how-it-works.php">How It Works</a>
                <a href="pharmacies.php">Partner Pharmacies</a>
            </div>

            <div class="footer-column">
                <h3>AfyaMicro</h3>
                <a href="about.php">About Us</a>
                <a href="contact.php">Contact</a>
                <a href="auth/register.php">Get Covered</a>
            </div>

            <div class="footer-column">
                <h3>Your Account</h3>
                <a href="auth/login.php">Sign In</a>
                <a href="auth/register.php">Register</a>
                <a href="client/dashboard.php">Client Portal</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <span id="currentYear"><?= date('Y') ?></span> AfyaMicro Kampala. All rights reserved.</p>
            <p>Healthcare preparation, made simpler.</p>
        </div>
    </div>
</footer>
    <?php
}

/** Render the Client Dashboard Sidebar from feature/afyamic-content-expansion. */
function uiClientSidebar(string $activePage, ?array $client, int $notifCount = 0): void
{
    $fullName     = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'AfyaMicro Member'));
    $policyNumber = (string) ($client['policy_number'] ?? 'Client Account');
    $initials     = uiInitials($fullName);
    ?>
    <div class="dashboard-overlay" id="dashboardOverlay"></div>

    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <a href="../index.php" class="dashboard-brand">
            <img src="../assets/images/afyamicrologo.svg" alt="AfyaMicro Kampala" style="filter: brightness(0) invert(1);">
        </a>

        <div class="sidebar-section-label">MAIN MENU</div>

        <nav class="dashboard-nav">
            <a href="dashboard.php" class="<?= $activePage === 'dashboard' ? 'active' : '' ?>">
                <span class="nav-icon">⌂</span>
                <span>Overview</span>
            </a>

            <a href="wallet.php" class="<?= $activePage === 'wallet' ? 'active' : '' ?>">
                <span class="nav-icon">▣</span>
                <span>My Health Wallet</span>
            </a>

            <a href="contributions.php" class="<?= $activePage === 'contributions' ? 'active' : '' ?>">
                <span class="nav-icon">↗</span>
                <span>Contributions</span>
            </a>

            <a href="claims.php" class="<?= $activePage === 'claims' ? 'active' : '' ?>">
                <span class="nav-icon">♡</span>
                <span>My Claims</span>
            </a>

            <a href="transactions.php" class="<?= $activePage === 'transactions' ? 'active' : '' ?>">
                <span class="nav-icon">⇄</span>
                <span>Transactions</span>
            </a>
        </nav>

        <div class="sidebar-section-label">ACCOUNT</div>

        <nav class="dashboard-nav">
            <a href="notifications.php" class="<?= $activePage === 'notifications' ? 'active' : '' ?>">
                <span class="nav-icon">♧</span>
                <span>Notifications</span>
                <?php if ($notifCount > 0): ?>
                    <span class="nav-count" id="sidebarUnreadCount"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>

            <a href="profile.php" class="<?= $activePage === 'profile' ? 'active' : '' ?>">
                <span class="nav-icon">♙</span>
                <span>My Profile</span>
            </a>

            <a href="settings.php" class="<?= $activePage === 'settings' ? 'active' : '' ?>">
                <span class="nav-icon">⚙</span>
                <span>Settings</span>
            </a>

            <a href="../pharmacies.php">
                <span class="nav-icon">✚</span>
                <span>Find a Pharmacy</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <nav class="dashboard-nav">
                <a href="../how-it-works.php">
                    <span class="nav-icon">ⓘ</span>
                    <span>Help &amp; Support</span>
                </a>

                <a href="../auth/logout.php" id="logoutLink">
                    <span class="nav-icon">⇥</span>
                    <span>Sign Out</span>
                </a>
            </nav>

            <div class="sidebar-profile">
                <div class="profile-avatar" id="sidebarAvatar"><?= e($initials) ?></div>
                <div>
                    <strong id="sidebarUserName"><?= e($fullName) ?></strong>
                    <small><?= e($policyNumber) ?></small>
                </div>
            </div>
        </div>
    </aside>
    <?php
}

/** Render the Client Dashboard Topbar from feature/afyamic-content-expansion. */
function uiClientTopbar(string $title, string $subtitle, ?array $client): void
{
    $fullName     = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'AfyaMicro Member'));
    $policyNumber = (string) ($client['policy_number'] ?? 'Member account');
    $initials     = uiInitials($fullName);
    ?>
        <header class="dashboard-topbar">
            <div class="dashboard-topbar-left">
                <button class="dashboard-menu-toggle"
                    id="dashboardMenuToggle"
                    aria-label="Open dashboard menu"
                    aria-expanded="false">
                    ☰
                </button>

                <div>
                    <h1 class="dashboard-page-title"><?= e($title) ?></h1>
                    <p class="dashboard-page-subtitle"><?= e($subtitle) ?></p>
                </div>
            </div>

            <div class="dashboard-topbar-actions">
                <a href="notifications.php"
                    class="dashboard-notification"
                    aria-label="Notifications">
                    ♧
                    <span class="notification-dot"></span>
                </a>

                <div class="dashboard-user">
                    <div class="profile-avatar" id="topbarAvatar"><?= e($initials) ?></div>

                    <div class="dashboard-user-details">
                        <strong id="topbarUserName"><?= e($fullName) ?></strong>
                        <small><?= e($policyNumber) ?></small>
                    </div>
                </div>
            </div>
        </header>
    <?php
}

/** Render the opening HTML5 + sidebar + topbar shell for Attendant & Admin portals. */
function uiPortalStart(array $cfg): void
{
    $pageTitle  = $cfg['pageTitle'] ?? APP_NAME;
    $title      = $cfg['title'] ?? '';
    $subtitle   = $cfg['subtitle'] ?? '';
    $userName   = $cfg['userName'] ?? ($_SESSION['full_name'] ?? 'User');
    $roleLabel  = $cfg['roleLabel'] ?? ucfirst((string) ($_SESSION['role'] ?? 'Portal'));
    $navItems   = $cfg['navItems'] ?? [];
    $homeHref   = $cfg['homeHref'] ?? '../index.php';
    $logoutHref = $cfg['logoutHref'] ?? '../auth/logout.php';
    $initials   = uiInitials($userName);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | AfyaMicro Kampala</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">
<div class="dashboard-layout">
    <div class="dashboard-overlay" id="dashboardOverlay"></div>

    <aside class="dashboard-sidebar" id="dashboardSidebar">
        <a href="<?= e($homeHref) ?>" class="dashboard-brand">
            <img src="../assets/images/afyamicrologo.svg" alt="AfyaMicro Kampala" style="filter: brightness(0) invert(1);">
        </a>

        <div class="sidebar-section-label">WORKSPACE</div>

        <nav class="dashboard-nav" aria-label="Portal navigation">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= e($item['href']) ?>" class="<?= !empty($item['active']) ? 'active' : '' ?>">
                    <span class="nav-icon"><?= uiIcon($item['icon'], 17) ?></span>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-section-label">DIRECTORY &amp; HELP</div>
        <nav class="dashboard-nav">
            <a href="../pharmacies.php">
                <span class="nav-icon">✚</span>
                <span>Partner Pharmacies</span>
            </a>
            <a href="../how-it-works.php">
                <span class="nav-icon">ⓘ</span>
                <span>How It Works</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <nav class="dashboard-nav">
                <a href="<?= e($logoutHref) ?>" id="logoutLink">
                    <span class="nav-icon">⇥</span>
                    <span>Sign Out</span>
                </a>
            </nav>

            <div class="sidebar-profile">
                <div class="profile-avatar"><?= e($initials) ?></div>
                <div>
                    <strong><?= e($userName) ?></strong>
                    <small><?= e($roleLabel) ?></small>
                </div>
            </div>
        </div>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-topbar">
            <div class="dashboard-topbar-left">
                <button class="dashboard-menu-toggle"
                    id="dashboardMenuToggle"
                    data-sidebar-open
                    aria-label="Open navigation menu"
                    aria-expanded="false">
                    ☰
                </button>

                <div>
                    <h1 class="dashboard-page-title"><?= e($title) ?></h1>
                    <p class="dashboard-page-subtitle"><?= e($subtitle) ?></p>
                </div>
            </div>

            <div class="dashboard-topbar-actions">
                <span class="badge"><?= e($roleLabel) ?></span>
                <div class="dashboard-user">
                    <div class="profile-avatar"><?= e($initials) ?></div>
                    <div class="dashboard-user-details">
                        <strong><?= e($userName) ?></strong>
                        <small><?= e($roleLabel) ?></small>
                    </div>
                </div>
            </div>
        </header>
        <div class="portal-main">
    <?php
}

/** Close the portal shell and include assets/js/app.js. */
function uiPortalEnd(): void
{
    ?>
        </div>
        <footer style="padding:30px 0 5px;text-align:center;color:#7C8C91;font-size:12px;">
            &copy; <?= date('Y') ?> AfyaMicro Kampala. All rights reserved.
        </footer>
    </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
    <?php
}
