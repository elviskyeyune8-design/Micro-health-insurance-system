<?php
/**
 * client/settings.php — Client Account Settings Page (Content Expansion Design)
 * Wired to client/fetch_data.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$policyNumber = (string) ($client['policy_number'] ?? 'Pending');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard-body">

<div class="dashboard-layout">

    <?php uiClientSidebar('settings', $client); ?>

    <main class="dashboard-main">

        <?php uiClientTopbar('Settings', 'Manage your account preferences (' . $policyNumber . ')', $client); ?>

        <div class="dashboard-content settings-content">

            <section class="settings-welcome">
                <span class="eyebrow">PERSONAL PREFERENCES</span>
                <h2>Account settings</h2>
                <p>Customise how you use and interact with AfyaMicro Kampala.</p>
            </section>

            <div class="settings-feedback" id="settingsFeedback" role="status" aria-live="polite"></div>

            <div class="settings-layout">

                <nav class="settings-navigation" aria-label="Settings sections">
                    <a href="#accountPreferences" class="settings-nav-item active">
                        <span>◎</span> Account preferences
                    </a>
                    <a href="#notificationPreferences" class="settings-nav-item">
                        <span>♧</span> Notifications
                    </a>
                    <a href="#displayPreferences" class="settings-nav-item">
                        <span>◐</span> Display preferences
                    </a>
                    <a href="#privacyPreferences" class="settings-nav-item">
                        <span>⌑</span> Privacy and security
                    </a>
                    <a href="#sessionPreferences" class="settings-nav-item">
                        <span>↪</span> Session settings
                    </a>
                </nav>

                <div class="settings-sections">

                    <!-- ACCOUNT PREFERENCES -->
                    <section class="settings-card" id="accountPreferences">
                        <div class="settings-card-heading">
                            <div>
                                <h3>Account preferences</h3>
                                <p>Choose how your account preferences should be displayed.</p>
                            </div>
                            <span class="settings-heading-icon">◎</span>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Preferred contact method</strong>
                                <span>Choose your preferred way to receive account communications.</span>
                            </div>

                            <select id="contactMethod" class="settings-select">
                                <option value="phone">Phone (<?= e($client['phone'] ?? '') ?>)</option>
                                <option value="email">Email</option>
                                <option value="both">Both phone and email</option>
                            </select>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Preferred language</strong>
                                <span>Choose the language for the interface.</span>
                            </div>

                            <select id="preferredLanguage" class="settings-select">
                                <option value="English">English</option>
                                <option value="Luganda">Luganda</option>
                            </select>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Time zone</strong>
                                <span>Used when displaying contribution and claim timestamps.</span>
                            </div>

                            <select id="timeZone" class="settings-select">
                                <option value="Africa/Kampala">East Africa Time (Kampala · UTC+3)</option>
                                <option value="UTC">Coordinated Universal Time (UTC)</option>
                            </select>
                        </div>
                    </section>

                    <!-- NOTIFICATION PREFERENCES -->
                    <section class="settings-card" id="notificationPreferences">
                        <div class="settings-card-heading">
                            <div>
                                <h3>Notification preferences</h3>
                                <p>Choose the types of notifications you would prefer to receive.</p>
                            </div>
                            <span class="settings-heading-icon">♧</span>
                        </div>

                        <div class="settings-toggle-row">
                            <div class="settings-field-info">
                                <strong>Account notifications</strong>
                                <span>Updates about your member account and policy profile.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="accountNotifications" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="settings-toggle-row">
                            <div class="settings-field-info">
                                <strong>Wallet notifications</strong>
                                <span>Updates about wallet balance changes.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="walletNotifications" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="settings-toggle-row">
                            <div class="settings-field-info">
                                <strong>Contribution notifications</strong>
                                <span>Confirmations for MTN MoMo, Airtel Money &amp; Cash Agent deposits.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="contributionNotifications" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="settings-toggle-row">
                            <div class="settings-field-info">
                                <strong>Health claim notifications</strong>
                                <span>Receipts for prescriptions redeemed at partner pharmacies.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="claimNotifications" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="settings-toggle-row">
                            <div class="settings-field-info">
                                <strong>Email notifications preference</strong>
                                <span>Preference for email communication.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="emailNotifications">
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="settings-toggle-row">
                            <div class="settings-field-info">
                                <strong>SMS notifications preference</strong>
                                <span>Preference for SMS alerts to <?= e($client['phone'] ?? '') ?>.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="smsNotifications" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </section>

                    <!-- DISPLAY PREFERENCES -->
                    <section class="settings-card" id="displayPreferences">
                        <div class="settings-card-heading">
                            <div>
                                <h3>Display preferences</h3>
                                <p>Choose how the interface should appear.</p>
                            </div>
                            <span class="settings-heading-icon">◐</span>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Colour theme</strong>
                                <span>Choose your preferred appearance.</span>
                            </div>

                            <select id="colourTheme" class="settings-select">
                                <option value="light">Light theme</option>
                                <option value="dark">Dark theme (preference)</option>
                                <option value="system">System default</option>
                            </select>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Dashboard compact view</strong>
                                <span>Preference for a more compact dashboard layout.</span>
                            </div>

                            <label class="switch">
                                <input type="checkbox" id="compactView">
                                <span class="switch-slider"></span>
                            </label>
                        </div>
                    </section>

                    <!-- PRIVACY AND SECURITY -->
                    <section class="settings-card" id="privacyPreferences">
                        <div class="settings-card-heading">
                            <div>
                                <h3>Privacy and security</h3>
                                <p>Security protections active on your AfyaMicro account.</p>
                            </div>
                            <span class="settings-heading-icon">⌑</span>
                        </div>

                        <div class="settings-security-message">
                            <strong>Server-Side Session &amp; Database Protection</strong>
                            <p>Your wallet and profile are secured by PHP 8 PDO prepared statements, bcrypt password hashes, HttpOnly session cookies, and InnoDB row-level transaction locks.</p>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Personal information</strong>
                                <span>Review the information saved in your member profile.</span>
                            </div>
                            <a href="profile.php" class="settings-text-link">View profile →</a>
                        </div>
                    </section>

                    <!-- SESSION SETTINGS -->
                    <section class="settings-card" id="sessionPreferences">
                        <div class="settings-card-heading">
                            <div>
                                <h3>Session settings</h3>
                                <p>Manage your current authenticated session.</p>
                            </div>
                            <span class="settings-heading-icon">↪</span>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Remember me preference</strong>
                                <span>Controls remembered phone number on this browser.</span>
                            </div>
                            <label class="switch">
                                <input type="checkbox" id="rememberMe">
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="settings-field">
                            <div class="settings-field-info">
                                <strong>Current browser</strong>
                                <span id="browserInformation">Active authenticated session</span>
                            </div>
                        </div>

                        <div class="settings-session-actions">
                            <button type="button" class="btn btn-outline" id="clearPreferences">
                                Reset preferences
                            </button>
                            <a href="../auth/logout.php" class="btn btn-outline logout-settings" id="settingsLogout">
                                Sign out
                            </a>
                        </div>
                    </section>

                    <div class="settings-save-bar">
                        <p>Your preference changes are saved when you click Save Preferences.</p>
                        <div>
                            <button type="button" class="btn btn-outline" id="cancelSettings">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveSettings">Save Preferences</button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>
</div>

<script src="../assets/js/app.js"></script>
<script src="../assets/js/settings.js"></script>
</body>
</html>
