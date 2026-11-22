<?php
/**
 * client/profile.php — Client Personal Profile Page (Content Expansion Design)
 * Wired to client/fetch_data.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$fullName     = (string) ($client['full_name'] ?? ($_SESSION['full_name'] ?? 'Member'));
$phone        = (string) ($client['phone'] ?? '');
$nin          = (string) ($client['nin'] ?? '');
$policyNumber = (string) ($client['policy_number'] ?? 'Pending');
$planName     = (string) ($client['plan_name'] ?? 'Basic Care Plan');
$walletStatus = (string) ($client['wallet_status'] ?? 'active');
$homePharmacy = (string) ($client['home_pharmacy'] ?? 'Not specified');
$homeLocation = (string) ($client['home_pharmacy_location'] ?? '');
$memberSince  = uiDate($client['start_date'] ?? ($client['member_since'] ?? null), 'd M Y');
$initials     = uiInitials($fullName);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('profile', $client); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('My Profile', 'Manage your personal account information', $client); ?>

        <div class="dashboard-content profile-content">

            <?= uiAlert($alert) ?>

            <section class="profile-welcome">
                <div>
                    <span class="eyebrow">ACCOUNT MANAGEMENT</span>
                    <h2>Personal profile</h2>
                    <p>Review your verified AfyaMicro member information and policy details.</p>
                </div>
            </section>

            <section class="profile-overview-card">
                <div class="profile-overview-left">
                    <div class="profile-large-avatar" id="profileLargeAvatar"><?= e($initials) ?></div>

                    <div class="profile-overview-details">
                        <h3 id="profileDisplayName"><?= e($fullName) ?></h3>
                        <p id="profileDisplayPhone"><?= e($phone) ?> · Policy <?= e($policyNumber) ?></p>
                        <span class="profile-member-label">
                            <span class="profile-online-dot"></span>
                            AfyaMicro Member · <?= e(ucfirst($walletStatus)) ?>
                        </span>
                    </div>
                </div>

                <div class="profile-overview-right">
                    <span>BENEFIT PLAN</span>
                    <strong><?= e($planName) ?></strong>
                    <small>Member since <?= e($memberSince) ?></small>
                </div>
            </section>

            <div class="profile-layout">

                <section class="profile-form-card">
                    <div class="profile-section-heading">
                        <div>
                            <h3>Personal information</h3>
                            <p>Verified details associated with your AfyaMicro policy wallet.</p>
                        </div>
                        <span class="profile-section-icon">◎</span>
                    </div>

                    <div class="profile-feedback" id="profileFeedback" role="status" aria-live="polite"></div>

                    <form id="profileForm" onsubmit="return false;">

                        <div class="profile-form-grid">

                            <div class="form-group profile-full-width">
                                <label for="fullName">Full name</label>
                                <input
                                    type="text"
                                    id="fullName"
                                    class="form-control"
                                    value="<?= e($fullName) ?>"
                                    readonly
                                >
                                <small class="field-hint">Registered policy holder name.</small>
                            </div>

                            <div class="form-group">
                                <label for="phone">Ugandan phone number</label>
                                <input
                                    type="tel"
                                    id="phone"
                                    class="form-control"
                                    value="<?= e($phone) ?>"
                                    readonly
                                >
                                <small class="field-hint">Primary login &amp; counter lookup identifier.</small>
                            </div>

                            <div class="form-group">
                                <label for="nin">National Identification Number (NIN)</label>
                                <input
                                    type="text"
                                    id="nin"
                                    class="form-control"
                                    value="<?= e($nin !== '' ? $nin : 'Not provided') ?>"
                                    readonly
                                >
                                <small class="field-hint">Uganda National ID record.</small>
                            </div>

                            <div class="form-group">
                                <label for="policyNo">Policy number</label>
                                <input
                                    type="text"
                                    id="policyNo"
                                    class="form-control"
                                    value="<?= e($policyNumber) ?>"
                                    readonly
                                >
                                <small class="field-hint">Present this ID at partner pharmacies.</small>
                            </div>

                            <div class="form-group">
                                <label for="homePharmacy">Preferred home pharmacy</label>
                                <input
                                    type="text"
                                    id="homePharmacy"
                                    class="form-control"
                                    value="<?= e($homePharmacy . ($homeLocation !== '' ? ' — ' . $homeLocation : '')) ?>"
                                    readonly
                                >
                                <small class="field-hint">You may also claim at any other active partner pharmacy.</small>
                            </div>

                        </div>

                        <div class="profile-form-actions">
                            <a href="wallet.php" class="btn btn-primary">
                                Go to Health Wallet →
                            </a>
                            <a href="settings.php" class="btn btn-outline">
                                Account Settings
                            </a>
                        </div>

                    </form>
                </section>

                <aside class="profile-side-column">

                    <div class="profile-info-card">
                        <div class="profile-side-heading">
                            <span class="profile-side-icon">✓</span>
                            <h3>Account summary</h3>
                        </div>

                        <div class="profile-info-row">
                            <span>Policy status</span>
                            <strong class="demo-status"><?= e(ucfirst($walletStatus)) ?></strong>
                        </div>

                        <div class="profile-info-row">
                            <span>Policy ID</span>
                            <strong><?= e($policyNumber) ?></strong>
                        </div>

                        <div class="profile-info-row">
                            <span>Phone number</span>
                            <strong id="sidePhone"><?= e($phone) ?></strong>
                        </div>

                        <div class="profile-info-row">
                            <span>Home pharmacy</span>
                            <strong id="sidePharmacy"><?= e($homePharmacy) ?></strong>
                        </div>

                        <div class="profile-info-row">
                            <span>Available balance</span>
                            <strong><?= e(formatUGX($client['available_balance'] ?? 0)) ?></strong>
                        </div>
                    </div>

                    <div class="profile-security-card">
                        <div class="profile-side-heading">
                            <span class="profile-side-icon security-icon">⌑</span>
                            <h3>Account security</h3>
                        </div>

                        <p>Your account is protected with bcrypt password hashing, session-bound CSRF tokens, and row-locked wallet transactions.</p>

                        <a href="settings.php" class="profile-settings-link">
                            View account settings →
                        </a>
                    </div>

                    <div class="profile-support-card">
                        <strong>Need assistance?</strong>
                        <p>Contact the AfyaMicro support team for help updating your registered details.</p>
                        <a href="../contact.php">Contact support →</a>
                    </div>

                </aside>

            </div>

        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
</body>
</html>
