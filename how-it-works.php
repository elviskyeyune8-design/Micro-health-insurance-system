<?php
/**
 * how-it-works.php — How AfyaMicro Works (Content Expansion Design)
 */

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/ui.php';

$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Discover how AfyaMicro Kampala works, from account registration to managing your health wallet.">
    <title>How It Works | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/content-expansion.css">
</head>
<body>

<?php uiPublicHeader('how-it-works', $loggedIn); ?>

<main>

<section class="section" style="background:linear-gradient(120deg,#eff9f5,#ffffff);">
    <div class="container section-heading">
        <span class="badge">Simple. Accessible. Convenient.</span>
        <h1 class="section-title">How AfyaMicro works</h1>
        <p class="section-subtitle">
            Understand how the AfyaMicro Kampala platform helps individuals
            prepare for healthcare expenses, manage their health wallet, and
            redeem prescribed medicines at partner pharmacies.
        </p>
        <a href="auth/register.php" class="btn btn-primary">Create Your Account</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <span class="badge">Your journey</span>
            <h2 class="section-title">Four steps to get started</h2>
            <p class="section-subtitle">
                A guided digital experience designed around your healthcare needs.
            </p>
        </div>

        <div class="steps-grid">
            <article class="step-card">
                <div class="step-number">01</div>
                <h3>Register</h3>
                <p>
                    Create your account using your full name, Ugandan mobile
                    number, optional NIN, and preferred home pharmacy to get an instant MHI-KLA-XXXX policy ID.
                </p>
            </article>

            <article class="step-card">
                <div class="step-number">02</div>
                <h3>Access your wallet</h3>
                <p>
                    View your personal health wallet balance, policy status, and
                    contribution summary through your client dashboard.
                </p>
            </article>

            <article class="step-card">
                <div class="step-number">03</div>
                <h3>Make contributions</h3>
                <p>
                    Deposit flexible micro-premiums via MTN Mobile Money,
                    Airtel Money, or Cash Agents starting from UGX 1,000.
                </p>
            </article>

            <article class="step-card">
                <div class="step-number">04</div>
                <h3>Redeem pharmacy care</h3>
                <p>
                    Visit any accredited partner pharmacy in Kampala; the attendant verifies your policy and deducts covered prescription costs automatically.
                </p>
            </article>
        </div>
    </div>
</section>

<section class="section" style="background:#f4faf7;">
    <div class="container benefits-grid">
        <div>
            <span class="badge">Your personal account</span>
            <h2 class="section-title">Everything organised in one place.</h2>
            <p style="color:var(--muted);line-height:1.8;">
                The AfyaMicro dashboard brings all your health wallet activities
                together in one accessible interface.
            </p>

            <ul class="benefit-list">
                <li><span>✓</span> Personal account &amp; policy ID information</li>
                <li><span>✓</span> Real-time health wallet balance overview</li>
                <li><span>✓</span> Itemised contribution &amp; claim history</li>
                <li><span>✓</span> Verified partner pharmacy directory</li>
                <li><span>✓</span> Notifications and account settings</li>
            </ul>

            <a href="auth/register.php" class="btn btn-primary">Get Started Today</a>
        </div>

        <div class="benefits-visual">
            <div class="benefits-panel">
                <div class="panel-heading">
                    <div>
                        <span>AFYAMICRO EXPERIENCE</span>
                        <h3>Your health dashboard</h3>
                    </div>
                    <div class="panel-symbol">♥</div>
                </div>

                <div class="overview-item">
                    <span>01 · Account</span>
                    <strong>Personal profile &amp; MHI-KLA policy</strong>
                </div>
                <div class="overview-item">
                    <span>02 · Wallet</span>
                    <strong>Contribution overview &amp; mobile money top-up</strong>
                </div>
                <div class="overview-item">
                    <span>03 · Activity</span>
                    <strong>Transaction &amp; claim receipt history</strong>
                </div>
                <div class="overview-item">
                    <span>04 · Healthcare</span>
                    <strong>Kampala partner pharmacy directory</strong>
                </div>
                <div class="panel-footer">Live integrated portal</div>
            </div>
        </div>
    </div>
</section>

<section class="section cta-section">
    <div class="container cta-content">
        <h2>Ready to explore AfyaMicro?</h2>
        <p>Start with your account and discover the platform.</p>
        <a href="auth/register.php" class="btn btn-white">Create an Account</a>
    </div>
</section>

</main>

<section id="content-expansion-added" class="content-expansion-section">
    <div class="container">
        <div class="content-expansion-intro">
            <span class="wallet-eyebrow">YOUR MEMBER JOURNEY</span>
            <h2 class="section-title">Understand each part of the platform.</h2>
            <p>Explore how the different areas of the AfyaMicro interface work together, from creating a member profile to reviewing account activity.</p>
        </div>
        <div class="content-expansion-grid">
            <article class="content-expansion-card">
                <h3>1. Create a member profile</h3>
                <p>Use the registration page to enter your name, Ugandan phone number, optional NIN and home pharmacy selection to activate your policy wallet.</p>
            </article>
            <article class="content-expansion-card">
                <h3>2. Top up your wallet</h3>
                <p>Open the wallet or contributions page to view your available balance, enter an amount and deposit via MTN MoMo, Airtel Money or Cash Agent.</p>
            </article>
            <article class="content-expansion-card">
                <h3>3. Review contributions</h3>
                <p>Track every mobile-money and cash-agent contribution along with its unique transaction reference and timestamp.</p>
            </article>
            <article class="content-expansion-card">
                <h3>4. Explore pharmacy information</h3>
                <p>Browse verified partner pharmacies across Kampala divisions including Kisenyi, Kawempe, and Katwe.</p>
            </article>
            <article class="content-expansion-card">
                <h3>5. Redeem prescription claims</h3>
                <p>At the pharmacy counter, the attendant looks up your policy, calculates insurance coverage and any cash co-pay, and prints an 80mm receipt.</p>
            </article>
            <article class="content-expansion-card">
                <h3>6. Track account activity</h3>
                <p>Review transactions, notifications and dashboard summaries to monitor your complete healthcare savings and claims ledger.</p>
            </article>
        </div>
    </div>
</section>

<?php uiPublicFooter(); ?>

<script src="assets/js/navigation.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
