<?php
/**
 * auth/register.php — Client Registration Page (Content Expansion Design)
 * Wired to auth/fetch_data.php ($pharmacies) & POST auth/register_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$selectedPharmacy = old('pharmacy_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | AfyaMicro Kampala</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">

    <main class="auth-layout register-layout">
        <section class="auth-brand-panel">
            <a href="../index.php" class="auth-logo">
                <img src="../assets/images/afyamicrologo.svg" alt="AfyaMicro logo" style="filter: brightness(0) invert(1);">
            </a>

            <div class="brand-content">
                <span class="auth-eyebrow">START YOUR JOURNEY</span>
                <h1>A healthier tomorrow starts with a simple step.</h1>
                <p>
                    Create your AfyaMicro account and receive an automatic
                    MHI-KLA-XXXX policy number with a personal UGX health wallet.
                </p>

                <div class="brand-feature">
                    <span class="feature-icon">✓</span>
                    <span>Create your personal account &amp; policy ID</span>
                </div>
                <div class="brand-feature">
                    <span class="feature-icon">✓</span>
                    <span>Save flexible amounts via Mobile Money</span>
                </div>
                <div class="brand-feature">
                    <span class="feature-icon">✓</span>
                    <span>Redeem prescriptions at partner pharmacies</span>
                </div>
            </div>

            <div class="brand-bottom">
                <span>AfyaMicro Kampala</span>
                <span>Accessible healthcare, together.</span>
            </div>
        </section>

        <section class="auth-form-panel register-form-panel">
            <div class="auth-form-container register-container">
                <a href="../index.php" class="mobile-auth-logo">
                    <img src="../assets/images/afyamicrologo.svg" alt="AfyaMicro">
                </a>

                <div class="auth-heading">
                    <span class="form-eyebrow">GET STARTED</span>
                    <h2>Create your account</h2>
                    <p>Fill in your details to get started with AfyaMicro.</p>
                </div>

                <?= uiAlert($alert) ?>

                <form id="registerForm" method="POST" action="register_process.php">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="fullName">Full name</label>
                        <input type="text" id="fullName" name="full_name"
                            class="form-control" placeholder="e.g. Musa Kato"
                            minlength="3" maxlength="120"
                            value="<?= old('full_name') ?>"
                            autocomplete="name" required>
                        <small class="field-error" id="fullNameError"></small>
                    </div>

                    <div class="form-group">
                        <label for="registerPhone">Phone number</label>
                        <div class="input-with-icon">
                            <span class="input-prefix">+256</span>
                            <input type="tel" id="registerPhone" name="phone"
                                class="form-control" placeholder="0701555111"
                                maxlength="13"
                                value="<?= old('phone') ?>"
                                autocomplete="tel" required>
                        </div>
                        <small class="field-error" id="registerPhoneError"></small>
                        <small class="field-hint">Use your Ugandan mobile number (e.g. 0701555111).</small>
                    </div>

                    <div class="form-group">
                        <label for="nin">National Identification Number <span class="optional">(Optional — 14 chars)</span></label>
                        <input type="text" id="nin" name="nin"
                            class="form-control" maxlength="14"
                            value="<?= old('nin') ?>"
                            placeholder="e.g. CM88021145PQRA">
                    </div>

                    <div class="form-group">
                        <label for="homePharmacy">Preferred home pharmacy</label>
                        <select id="homePharmacy" name="pharmacy_id" class="form-control" required>
                            <option value="">Select partner pharmacy...</option>
                            <?php foreach ($pharmacies as $p): ?>
                                <?php $pid = (string) (int) $p['pharmacy_id']; ?>
                                <option value="<?= $pid ?>" <?= $selectedPharmacy === $pid ? 'selected' : '' ?>>
                                    <?= e($p['name']) ?> — <?= e($p['location']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="field-error" id="homePharmacyError"></small>
                    </div>

                    <div class="form-group">
                        <label for="registerPassword">Create password</label>
                        <div class="password-wrapper">
                            <input type="password" id="registerPassword"
                                name="password" class="form-control"
                                minlength="6"
                                placeholder="Create a password (minimum 6 characters)"
                                autocomplete="new-password" required>
                            <button type="button" class="password-toggle"
                                data-password-toggle="registerPassword"
                                aria-label="Show password">Show</button>
                        </div>
                        <small class="field-error" id="registerPasswordError"></small>
                        <small class="field-hint">Your password must contain at least 6 characters.</small>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirmPassword"
                                name="confirm_password" class="form-control"
                                minlength="6"
                                placeholder="Enter your password again"
                                autocomplete="new-password" required>
                            <button type="button" class="password-toggle"
                                data-password-toggle="confirmPassword"
                                aria-label="Show password">Show</button>
                        </div>
                        <small class="field-error" id="confirmPasswordError"></small>
                    </div>

                    <label class="remember-option terms-option">
                        <input type="checkbox" id="acceptTerms" checked required>
                        <span>I agree to the Terms of Use and Privacy Policy.</span>
                    </label>

                    <button type="submit" class="btn btn-primary auth-submit">
                        Create Account <span>→</span>
                    </button>
                </form>

                <div class="auth-divider"><span>ALREADY REGISTERED?</span></div>

                <p class="auth-switch">
                    Already have an account?
                    <a href="login.php">Sign in</a>
                </p>

                <div class="auth-extra-content" id="registration-guidance">
                    <h3>Instant Policy &amp; Wallet Activation</h3>
                    <ul>
                        <li>Your unique policy number (<strong>MHI-KLA-XXXX</strong>) is generated automatically.</li>
                        <li>Your health wallet starts at <strong>UGX 0</strong> and can be topped up anytime via MTN MoMo, Airtel Money, or Cash Agent.</li>
                        <li>You can claim prescriptions at any active partner pharmacy in Kampala.</li>
                    </ul>
                </div>
                <a href="../index.php" class="back-home">← Back to homepage</a>
            </div>
        </section>
    </main>

    <script src="../assets/js/auth.js"></script>
</body>
</html>
