<?php
/**
 * index.php — Public Landing Page (AfyaMicro Kampala Content Expansion Design)
 * Wired to config/db.php, config/ui.php, auth/login.php & auth/register.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/ui.php';

$alert      = statusMessage();
$loggedIn   = isLoggedIn();
$pharmacies = publicPharmacies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="AfyaMicro Kampala helps individuals and families prepare for everyday healthcare costs through affordable health micro-insurance.">
    <title>AfyaMicro Kampala | Affordable Healthcare Protection</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/content-expansion.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php uiPublicHeader('home', $loggedIn); ?>

<?php if ($alert): ?>
    <div class="container" style="padding-top: 22px;">
        <?= uiAlert($alert) ?>
    </div>
<?php endif; ?>

<main>

    <!-- HERO -->
    <section class="hero-section section">
        <div class="container hero-grid">
            <div class="hero-content">
                <span class="badge">Healthcare protection made accessible</span>

                <h1>
                    Your health matters.
                    <span>Protect it, one day at a time.</span>
                </h1>

                <p>
                    AfyaMicro Kampala helps you prepare for everyday healthcare
                    costs through flexible health micro-insurance savings and
                    access to participating partner pharmacies across Kampala.
                </p>

                <div class="hero-actions">
                    <a href="auth/register.php" class="btn btn-primary">
                        Get Started
                    </a>
                    <a href="how-it-works.php" class="btn btn-outline">
                        Discover How It Works
                    </a>
                </div>

                <div class="hero-trust">
                    <span>✓ Simple registration</span>
                    <span>✓ Flexible contributions</span>
                    <span>✓ Healthcare focused</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="health-card">
                    <div class="health-card-top">
                        <div>
                            <span class="card-label">YOUR HEALTH WALLET</span>
                            <h3>AfyaMicro</h3>
                        </div>
                        <div class="health-icon">✚</div>
                    </div>

                    <div class="wallet-balance">
                        <span>Illustrative wallet balance</span>
                        <h2>UGX 50,000</h2>
                        <span class="balance-note">Active micro-insurance cover</span>
                    </div>

                    <div class="wallet-progress">
                        <div class="progress-heading">
                            <span>Health savings progress</span>
                            <strong>50%</strong>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill"></div>
                        </div>
                    </div>

                    <div class="health-card-bottom">
                        <span>AfyaMicro Kampala</span>
                        <span>HEALTH • WELLNESS • CARE</span>
                    </div>
                </div>

                <div class="floating-note">
                    <span class="floating-icon">♥</span>
                    <div>
                        <strong>Care starts with preparation</strong>
                        <small>Plan ahead for your health</small>
                    </div>
                </div>

                <div class="hero-decoration decoration-one"></div>
                <div class="hero-decoration decoration-two"></div>
            </div>
        </div>
    </section>

    <!-- INTRODUCTION -->
    <section class="section intro-section">
        <div class="container">
            <div class="section-heading">
                <span class="badge">A smarter approach to healthcare</span>
                <h2 class="section-title">Healthcare preparation should be simple.</h2>
                <p class="section-subtitle">
                    Unexpected medical expenses can put pressure on individuals
                    and families. AfyaMicro is designed to make healthcare
                    preparation more accessible and easier to manage.
                </p>
            </div>

            <div class="intro-grid">
                <article class="card intro-card">
                    <div class="feature-icon">01</div>
                    <h3>Prepare financially</h3>
                    <p>Build a habit of setting aside small, flexible amounts towards your healthcare needs.</p>
                </article>

                <article class="card intro-card">
                    <div class="feature-icon">02</div>
                    <h3>Manage your wallet</h3>
                    <p>View your contributions and monitor your health wallet balance in real time.</p>
                </article>

                <article class="card intro-card">
                    <div class="feature-icon">03</div>
                    <h3>Access participating pharmacies</h3>
                    <p>Redeem prescribed medications at verified Kampala partner pharmacies with instant co-pay calculation.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="section how-section" id="how-it-works">
        <div class="container">
            <div class="section-heading">
                <span class="badge">Three simple steps</span>
                <h2 class="section-title">How AfyaMicro works</h2>
                <p class="section-subtitle">
                    A straightforward journey from registration to managing
                    your healthcare contributions and pharmacy claims.
                </p>
            </div>

            <div class="steps-grid">
                <article class="step-card">
                    <div class="step-number">01</div>
                    <h3>Create your account</h3>
                    <p>Register using your personal details and phone number to receive your unique MHI-KLA-XXXX policy ID.</p>
                </article>

                <article class="step-card">
                    <div class="step-number">02</div>
                    <h3>Build your health wallet</h3>
                    <p>Make flexible micro-deposits via MTN Mobile Money, Airtel Money, or local Cash Agents.</p>
                </article>

                <article class="step-card">
                    <div class="step-number">03</div>
                    <h3>Redeem healthcare services</h3>
                    <p>Present your phone or policy number at any accredited partner pharmacy to cover prescriptions.</p>
                </article>
            </div>

            <div class="center-action">
                <a href="how-it-works.php" class="btn btn-primary">Explore the Process</a>
            </div>
        </div>
    </section>

    <!-- BENEFITS -->
    <section class="section benefits-section">
        <div class="container benefits-grid">
            <div class="benefits-content">
                <span class="badge">Designed around your needs</span>
                <h2 class="section-title">Take a more organised approach to healthcare costs.</h2>
                <p>
                    AfyaMicro brings important account activities together
                    in one simple digital experience.
                </p>

                <ul class="benefit-list">
                    <li><span>✓</span> Easy account registration &amp; instant policy issuance</li>
                    <li><span>✓</span> Personal health wallet overview</li>
                    <li><span>✓</span> Real-time contribution &amp; claim activity tracking</li>
                    <li><span>✓</span> Accredited Kampala pharmacy directory access</li>
                    <li><span>✓</span> Transparent co-payment &amp; receipt records</li>
                </ul>

                <a href="auth/register.php" class="btn btn-primary">Create Your Account</a>
            </div>

            <div class="benefits-visual">
                <div class="benefits-panel">
                    <div class="panel-heading">
                        <div>
                            <span>MY HEALTH ACCOUNT</span>
                            <h3>Account overview</h3>
                        </div>
                        <div class="panel-symbol">♥</div>
                    </div>

                    <div class="overview-item">
                        <span>Wallet activity</span>
                        <strong>Contribution history</strong>
                    </div>
                    <div class="overview-item">
                        <span>Account information</span>
                        <strong>Personal profile &amp; policy ID</strong>
                    </div>
                    <div class="overview-item">
                        <span>Healthcare access</span>
                        <strong>Partner pharmacy directory</strong>
                    </div>

                    <div class="panel-footer">
                        Connected to live AfyaMicro wallet &amp; claim services
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PHARMACIES -->
    <section class="section pharmacy-section" id="pharmacies">
        <div class="container">
            <div class="section-heading">
                <span class="badge">Healthcare access</span>
                <h2 class="section-title">Explore our pharmacy directory</h2>
                <p class="section-subtitle">
                    Find verified partner pharmacies and division directory
                    information across Kampala.
                </p>
            </div>

            <div class="pharmacy-grid">
                <?php if (!empty($pharmacies)): ?>
                    <?php foreach ($pharmacies as $ph): ?>
                        <article class="card pharmacy-card">
                            <div class="pharmacy-symbol">+</div>
                            <h3><?= e($ph['name']) ?></h3>
                            <p><?= e($ph['location']) ?><?= !empty($ph['address']) ? ' · ' . e($ph['address']) : '' ?></p>
                            <span class="directory-status">Verified Partner · <?= e($ph['license_no']) ?></span>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>

                <article class="card pharmacy-card">
                    <div class="pharmacy-symbol">+</div>
                    <h3>Nakawa Division</h3>
                    <p>Explore pharmacy directory information for Nakawa.</p>
                    <span class="directory-status">Division directory</span>
                </article>

                <article class="card pharmacy-card">
                    <div class="pharmacy-symbol">+</div>
                    <h3>Rubaga Division</h3>
                    <p>Explore pharmacy directory information for Rubaga.</p>
                    <span class="directory-status">Division directory</span>
                </article>
            </div>

            <div class="center-action">
                <a href="pharmacies.php" class="btn btn-outline">View Pharmacy Directory</a>
            </div>
        </div>
    </section>

    <!-- PAYMENT OPTIONS -->
    <section class="section payment-section">
        <div class="container">
            <div class="section-heading">
                <span class="badge">Convenient payment experience</span>
                <h2 class="section-title">Flexible payment options</h2>
                <p class="section-subtitle">
                    Top up your health wallet anytime using familiar Ugandan mobile money and agent channels.
                </p>
            </div>

            <div class="payment-grid">
                <a class="payment-card payment-card-link" href="client/wallet.php?method=MTN_MoMo" aria-label="Choose MTN Mobile Money"><strong>MTN</strong><span>Mobile Money</span></a>
                <a class="payment-card payment-card-link" href="client/wallet.php?method=Airtel_Money" aria-label="Choose Airtel Money"><strong>Airtel</strong><span>Money</span></a>
                <a class="payment-card payment-card-link" href="client/wallet.php?method=Cash_Agent" aria-label="Choose Cash Agent"><img src="assets/images/payments/pesapal.png" alt="Pesapal" class="payment-logo pesapal-logo"><span>Cash Agent / Pesapal</span></a>
                <a class="payment-card payment-card-link" href="client/wallet.php?method=MTN_MoMo" aria-label="Choose Wallet Deposit"><strong>UGX</strong><span>Instant Micro-Deposit</span></a>
            </div>

            <p class="payment-disclaimer">
                All deposits credit your AfyaMicro health wallet immediately with a unique transaction reference code.
            </p>
        </div>
    </section>

    <!-- FAQ -->
    <section class="section faq-section">
        <div class="container faq-container">
            <div class="section-heading">
                <span class="badge">Frequently asked questions</span>
                <h2 class="section-title">Questions? We have answers.</h2>
                <p class="section-subtitle">
                    Learn more about AfyaMicro Kampala and how your health wallet works.
                </p>
            </div>

            <div class="faq-list">
                <article class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        What is AfyaMicro Kampala?
                        <span>+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            AfyaMicro Kampala is a digital health micro-insurance platform
                            designed to help low-income pharmacy clients in Kampala save small,
                            flexible amounts and redeem prescribed medicines at accredited partner pharmacies.
                        </p>
                    </div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        How do I create an account?
                        <span>+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            Visit the registration page, enter your full name, Ugandan phone number,
                            and preferred home pharmacy, and receive your unique MHI-KLA-XXXX policy number immediately.
                        </p>
                    </div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        Can I use mobile money?
                        <span>+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            Yes! You can make micro-deposits via MTN Mobile Money, Airtel Money,
                            or an accredited Cash Agent directly from your client wallet.
                        </p>
                    </div>
                </article>

                <article class="faq-item">
                    <button class="faq-question" aria-expanded="false">
                        Where can I find participating pharmacies?
                        <span>+</span>
                    </button>
                    <div class="faq-answer">
                        <p>
                            Visit the Partner Pharmacies directory to view active NDA-licensed
                            pharmacies across Kampala divisions including Kisenyi, Kawempe, and Katwe.
                        </p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <!-- CALL TO ACTION -->
    <section class="section cta-section">
        <div class="container cta-content">
            <span class="badge">Start preparing today</span>
            <h2>Your health deserves a plan.</h2>
            <p>
                Take the first step towards a more organised approach
                to healthcare preparation.
            </p>
            <a href="auth/register.php" class="btn btn-white">Join AfyaMicro</a>
        </div>
    </section>

</main>

<section id="content-expansion-added" class="content-expansion-section">
    <div class="container">
        <div class="content-expansion-intro">
            <span class="wallet-eyebrow">THE AFYAMICRO EXPERIENCE</span>
            <h2 class="section-title">A more organised way to prepare for healthcare expenses.</h2>
            <p>AfyaMicro brings together useful account tools that help members understand and organise their healthcare-related financial activities through one digital experience.</p>
        </div>
        <div class="content-expansion-grid">
            <article class="content-expansion-card">
                <h3>Understand your finances</h3>
                <p>Use the wallet interface to view your real-time balance, make mobile-money deposits and review recorded wallet activity.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Keep your records organised</h3>
                <p>Explore contribution records, transaction history and account notifications from your member dashboard.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Explore healthcare information</h3>
                <p>Use the pharmacy directory to browse verified partner pharmacies across Kampala divisions.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Follow your account activity</h3>
                <p>Review account updates, contribution references and prescription claim receipts through the member-facing pages.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Access information in one place</h3>
                <p>Dedicated pages make account information, wallet tools and healthcare guidance easy to navigate.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Use a responsive interface</h3>
                <p>The platform adapts cleanly across desktop, tablet and mobile screens for clients, pharmacy attendants and administrators.</p>
            </article>
        </div>
        <div class="content-expansion-note">
            <strong>Academic &amp; System Notice:</strong> AfyaMicro Kampala is a Web-Based Micro Health Insurance System developed at Makerere University Business School (MUBS) connecting client health wallets, partner pharmacy counters, and scheme administration.
        </div>
    </div>
</section>

<?php uiPublicFooter(); ?>

<script src="assets/js/navigation.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>
