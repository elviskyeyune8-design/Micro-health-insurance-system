<?php
/**
 * about.php — About AfyaMicro Kampala (Content Expansion Design)
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
    <meta name="description" content="Learn about AfyaMicro Kampala, its mission, vision and approach to accessible healthcare preparation.">
    <title>About Us | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/about-contact.css">
    <link rel="stylesheet" href="assets/css/content-expansion.css">
</head>
<body>

<?php uiPublicHeader('about', $loggedIn); ?>

<main>

<section class="section about-hero">
    <div class="container about-hero-grid">
        <div>
            <span class="badge">About AfyaMicro Kampala</span>
            <h1 class="section-title">
                Building a simpler approach to healthcare preparation.
            </h1>
            <p>
                AfyaMicro Kampala is a digital health micro-insurance platform
                designed to make healthcare financial preparation more accessible,
                organised and convenient for low-income pharmacy clients.
            </p>

            <div class="hero-actions">
                <a href="how-it-works.php" class="btn btn-primary">How It Works</a>
                <a href="contact.php" class="btn btn-outline">Contact Us</a>
            </div>
        </div>

        <div class="about-illustration">
            <div class="about-heart">♥</div>
            <div class="about-circle circle-a"></div>
            <div class="about-circle circle-b"></div>
            <div class="about-illustration-text">
                <strong>Health comes first.</strong>
                <span>Preparation. Access. Care.</span>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container about-intro">
        <div class="section-heading">
            <span class="badge">Who we are</span>
            <h2 class="section-title">A digital approach to healthcare preparation.</h2>
            <p class="section-subtitle">
                Healthcare expenses can arise unexpectedly. AfyaMicro
                Kampala is designed around the idea that preparing for
                healthcare costs should be easier to understand and manage.
            </p>
        </div>
    </div>
</section>

<section class="section about-purpose">
    <div class="container purpose-grid">

        <article class="purpose-card">
            <div class="purpose-icon">◎</div>
            <span class="badge">Our mission</span>
            <h2>Make healthcare preparation more accessible.</h2>
            <p>
                To provide an accessible digital platform that helps
                individuals organise healthcare contributions, monitor
                their health wallet and redeem prescribed medicines at participating
                partner pharmacies.
            </p>
        </article>

        <article class="purpose-card">
            <div class="purpose-icon">◇</div>
            <span class="badge">Our vision</span>
            <h2>A future where healthcare preparation is simpler.</h2>
            <p>
                To contribute to a future in which individuals and
                families across Kampala can approach everyday healthcare expenses
                with greater awareness, organisation and confidence.
            </p>
        </article>

    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-heading">
            <span class="badge">What guides us</span>
            <h2 class="section-title">Our core values</h2>
            <p class="section-subtitle">
                The principles behind the AfyaMicro digital experience.
            </p>
        </div>

        <div class="values-grid">

            <article class="value-card">
                <div class="value-icon">♥</div>
                <h3>People first</h3>
                <p>We place individuals and their healthcare needs at the centre of the experience.</p>
            </article>

            <article class="value-card">
                <div class="value-icon">✧</div>
                <h3>Accessibility</h3>
                <p>We aim to make digital healthcare preparation easier to navigate on any device.</p>
            </article>

            <article class="value-card">
                <div class="value-icon">✓</div>
                <h3>Transparency</h3>
                <p>Every contribution and pharmacy claim deduction is tracked with an auditable reference.</p>
            </article>

            <article class="value-card">
                <div class="value-icon">◈</div>
                <h3>Innovation</h3>
                <p>We pair mobile money micro-savings with instant pharmacy counter verification.</p>
            </article>

        </div>
    </div>
</section>

<section class="section about-platform">
    <div class="container platform-grid">
        <div>
            <span class="badge">The AfyaMicro platform</span>
            <h2 class="section-title">Designed around a connected experience.</h2>
            <p>
                The platform brings client, pharmacy attendant, and scheme administrator
                functions together through a unified digital interface.
            </p>
        </div>

        <div class="platform-features">
            <div class="platform-feature">
                <span>01</span>
                <div>
                    <h3>Personal accounts &amp; policies</h3>
                    <p>Instant registration and MHI-KLA-XXXX policy issuance.</p>
                </div>
            </div>

            <div class="platform-feature">
                <span>02</span>
                <div>
                    <h3>Health wallet</h3>
                    <p>Micro-deposits via MTN MoMo, Airtel Money &amp; Cash Agents.</p>
                </div>
            </div>

            <div class="platform-feature">
                <span>03</span>
                <div>
                    <h3>Pharmacy counter claims</h3>
                    <p>Real-time eligibility lookup, co-pay split &amp; thermal receipt printing.</p>
                </div>
            </div>

            <div class="platform-feature">
                <span>04</span>
                <div>
                    <h3>Scheme oversight</h3>
                    <p>Partner pharmacy onboarding and multi-filter audit reporting.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section cta-section">
    <div class="container cta-content">
        <h2>Discover a simpler healthcare experience.</h2>
        <p>Explore the AfyaMicro Kampala platform.</p>
        <a href="how-it-works.php" class="btn btn-white">Explore AfyaMicro</a>
    </div>
</section>

</main>

<section id="content-expansion-added" class="content-expansion-section">
    <div class="container">
        <div class="content-expansion-intro">
            <span class="wallet-eyebrow">OUR PURPOSE</span>
            <h2 class="section-title">The challenge we address.</h2>
            <p>Healthcare expenses can be difficult to organise. AfyaMicro connects personal micro-savings wallets with community pharmacies across Kampala.</p>
        </div>
        <div class="content-expansion-grid">
            <article class="content-expansion-card">
                <h3>Better financial organisation</h3>
                <p>Provide a central interface for viewing wallet activity, contribution records and transaction information.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Accessible information</h3>
                <p>Present platform guidance and healthcare directory information in a format that is easy to understand and navigate.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Member visibility</h3>
                <p>Give members a comprehensive dashboard where account details, contributions, claims and notifications can be reviewed.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Transparent communication</h3>
                <p>Clearly display covered prescription amounts, cash co-payments, and remaining wallet balances on every claim.</p>
            </article>
            <article class="content-expansion-card">
                <h3>Local relevance</h3>
                <p>Built around Ugandan Shilling (UGX) micro-deposits, local mobile-money rails, and Kampala KCCA divisions.</p>
            </article>
            <article class="content-expansion-card">
                <h3>MUBS Academic Project</h3>
                <p>Developed at Makerere University Business School (MUBS), Faculty of Computing and Informatics, Department of Applied Computing.</p>
            </article>
        </div>
    </div>
</section>

<?php uiPublicFooter(); ?>

<script src="assets/js/navigation.js"></script>
<script src="assets/js/main.js"></script>
</body>
</html>
