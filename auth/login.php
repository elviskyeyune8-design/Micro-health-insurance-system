<?php
/**
 * auth/login.php — Portal Sign In Page (Content Expansion Design)
 * Wired to auth/fetch_data.php & POST auth/login_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | AfyaMicro Kampala</title>
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/responsive.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/content-expansion.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-page">

    <main class="auth-layout">
        <section class="auth-brand-panel">
            <a href="../index.php" class="auth-logo">
                <img src="../assets/images/afyamicrologo.svg" alt="AfyaMicro logo" style="filter: brightness(0) invert(1);">
            </a>

            <div class="brand-content">
                <span class="auth-eyebrow">YOUR HEALTH. YOUR SECURITY.</span>
                <h1>Healthcare protection made more accessible.</h1>
                <p>
                    Welcome to AfyaMicro Kampala. Take control of your
                    health contributions and stay connected to your
                    healthcare protection journey.
                </p>

                <div class="brand-feature">
                    <span class="feature-icon">✓</span>
                    <span>Manage your health contributions</span>
                </div>
                <div class="brand-feature">
                    <span class="feature-icon">✓</span>
                    <span>Keep track of your health wallet</span>
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

        <section class="auth-form-panel">
            <div class="auth-form-container">
                <a href="../index.php" class="mobile-auth-logo">
                    <img src="../assets/images/afyamicrologo.svg" alt="AfyaMicro">
                </a>

                <div class="auth-heading">
                    <span class="form-eyebrow">WELCOME BACK</span>
                    <h2>Sign in to your account</h2>
                    <p>Enter your phone number and password to access your portal.</p>
                </div>

                <?= uiAlert($alert) ?>

                <form id="loginForm" method="POST" action="login_process.php">
                    <?= csrfField() ?>

                    <div class="form-group">
                        <label for="loginPhone">Phone number</label>
                        <div class="input-with-icon">
                            <span class="input-prefix">+256</span>
                            <input
                                type="tel"
                                id="loginPhone"
                                name="phone"
                                class="form-control"
                                placeholder="0701555111"
                                maxlength="13"
                                value="<?= old('phone') ?>"
                                autocomplete="tel"
                                required
                            >
                        </div>
                        <small class="field-error" id="loginPhoneError"></small>
                    </div>

                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="loginPassword"
                                name="password"
                                class="form-control"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="password-toggle"
                                data-password-toggle="loginPassword"
                                aria-label="Show password">Show</button>
                        </div>
                        <small class="field-error" id="loginPasswordError"></small>
                    </div>

                    <div class="auth-options">
                        <label class="remember-option">
                            <input type="checkbox" id="rememberMe" name="remember">
                            <span>Remember me</span>
                        </label>
                        <a href="../contact.php" class="text-link">
                            Need help?
                        </a>
                    </div>

                    <button type="submit" class="btn btn-primary auth-submit">
                        Sign In <span>→</span>
                    </button>
                </form>

                <div class="auth-divider"><span>NEW TO AFYAMICRO?</span></div>

                <p class="auth-switch">
                    Don't have an account?
                    <a href="register.php">Create an account</a>
                </p>

                <p class="auth-legal">
                    By continuing, you agree to our
                    <a href="../about.php">Terms of Use</a> and
                    <a href="../about.php">Privacy Policy</a>.
                </p>

                <div class="auth-extra-content" id="login-guidance">
                    <h3>Seeded Kampala Demo Accounts (Password: <code>password</code>)</h3>
                    <ul>
                        <li><strong>Client (Mukasa John Bosco):</strong> <code>0701555111</code> (Balance: UGX 10,500)</li>
                        <li><strong>Client (Namuli Grace):</strong> <code>0782555222</code> (Balance: UGX 15,000)</li>
                        <li><strong>Pharmacy Attendant (Kisenyi Care):</strong> <code>0772100101</code></li>
                        <li><strong>Scheme Administrator:</strong> <code>0700000001</code></li>
                    </ul>
                </div>
                <a href="../index.php" class="back-home">← Back to homepage</a>
            </div>
        </section>
    </main>

    <script src="../assets/js/auth.js"></script>
</body>
</html>
