<?php
/**
 * pharmacies.php — Partner Pharmacies Directory (Content Expansion Design)
 * Renders live partner pharmacies from micro_health_db + Kampala division filters.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/ui.php';

$loggedIn   = isLoggedIn();
$pharmacies = publicPharmacies();

/** Map a location string to one of Kampala's 5 division filter keys. */
function detectDivisionKey(string $location): string
{
    $loc = strtolower($location);
    foreach (['central', 'kawempe', 'makindye', 'nakawa', 'rubaga'] as $div) {
        if (str_contains($loc, $div)) {
            return $div;
        }
    }
    if (str_contains($loc, 'kisenyi')) {
        return 'central';
    }
    if (str_contains($loc, 'katwe')) {
        return 'makindye';
    }
    return 'central';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Explore the AfyaMicro Kampala pharmacy directory and browse pharmacy locations by Kampala division.">
    <title>Partner Pharmacies | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/content-expansion.css">
</head>
<body>

<?php uiPublicHeader('pharmacies', $loggedIn); ?>

<main>

<!-- PAGE HERO -->
<section class="section pharmacy-hero">
    <div class="container pharmacy-hero-content">
        <span class="badge">Healthcare access directory</span>
        <h1 class="section-title">Find a pharmacy near you.</h1>
        <p class="section-subtitle">
            Explore the AfyaMicro pharmacy directory and browse accredited
            partner pharmacies across Kampala's five divisions.
        </p>

        <div class="pharmacy-search">
            <span>⌕</span>
            <input
                type="search"
                id="pharmacySearch"
                placeholder="Search a division or pharmacy..."
                aria-label="Search pharmacies">
        </div>

        <p class="search-hint">
            Search by division, location, or pharmacy name.
        </p>
    </div>
</section>

<!-- DIRECTORY -->
<section class="section">
    <div class="container">

        <div class="directory-heading">
            <div>
                <span class="badge">Explore locations</span>
                <h2 class="section-title">Kampala pharmacy directory</h2>
                <p class="section-subtitle">
                    Select a division to filter verified partner pharmacies and division directory coverage.
                </p>
            </div>
        </div>

        <div class="division-filters" id="divisionFilters">
            <button class="division-filter active" data-filter="all">All Divisions</button>
            <button class="division-filter" data-filter="central">Central</button>
            <button class="division-filter" data-filter="kawempe">Kawempe</button>
            <button class="division-filter" data-filter="makindye">Makindye</button>
            <button class="division-filter" data-filter="nakawa">Nakawa</button>
            <button class="division-filter" data-filter="rubaga">Rubaga</button>
        </div>

        <div class="pharmacy-directory-grid" id="pharmacyGrid">

            <?php foreach ($pharmacies as $ph): ?>
                <?php
                $divKey = detectDivisionKey((string) $ph['location']);
                $searchName = strtolower($ph['name'] . ' ' . $ph['location'] . ' ' . ($ph['address'] ?? '') . ' ' . $ph['license_no']);
                ?>
                <article class="directory-card" data-division="<?= e($divKey) ?>" data-name="<?= e($searchName) ?>">
                    <div class="directory-card-top">
                        <div class="directory-icon">✚</div>
                        <span class="directory-label">Verified Partner · <?= e($ph['license_no']) ?></span>
                    </div>
                    <h3><?= e($ph['name']) ?></h3>
                    <p>
                        <?= e($ph['address'] ?: ('Accredited partner pharmacy in ' . $ph['location'] . '.')) ?>
                    </p>
                    <div class="directory-location">
                        <span>⌖</span> <?= e($ph['location']) ?> · ☎ <?= e($ph['phone']) ?>
                    </div>
                    <div class="directory-card-bottom">
                        <span style="color: var(--primary); font-weight: 700;">● Active AfyaMicro Redemption Counter</span>
                    </div>
                </article>
            <?php endforeach; ?>

            <article class="directory-card" data-division="nakawa" data-name="nakawa division">
                <div class="directory-card-top">
                    <div class="directory-icon">✚</div>
                    <span class="directory-label">Division directory</span>
                </div>
                <h3>Nakawa Division</h3>
                <p>
                    Browse pharmacy information within Nakawa Division.
                </p>
                <div class="directory-location">
                    <span>⌖</span> Nakawa, Kampala
                </div>
                <div class="directory-card-bottom">
                    <span>Onboarding partner pharmacies</span>
                </div>
            </article>

            <article class="directory-card" data-division="rubaga" data-name="rubaga division">
                <div class="directory-card-top">
                    <div class="directory-icon">✚</div>
                    <span class="directory-label">Division directory</span>
                </div>
                <h3>Rubaga Division</h3>
                <p>
                    Browse pharmacy information within Rubaga Division.
                </p>
                <div class="directory-location">
                    <span>⌖</span> Rubaga, Kampala
                </div>
                <div class="directory-card-bottom">
                    <span>Onboarding partner pharmacies</span>
                </div>
            </article>

        </div>

        <div class="no-results" id="noResults" hidden>
            <div>⌕</div>
            <h3>No matching pharmacy or division found</h3>
            <p>Try another search term or select All Divisions.</p>
        </div>

        <p class="directory-disclaimer">
            <strong>Directory notice:</strong> Verified partner pharmacies listed above accept AfyaMicro policy IDs (<code>MHI-KLA-XXXX</code>) and registered client phone numbers for real-time prescription claim deduction.
        </p>

    </div>
</section>

<!-- HOW ACCESS WORKS -->
<section class="section pharmacy-process">
    <div class="container">
        <div class="section-heading">
            <span class="badge">Simple healthcare navigation</span>
            <h2 class="section-title">Using the pharmacy directory</h2>
            <p class="section-subtitle">
                The directory helps members locate verified partner pharmacies across Kampala.
            </p>
        </div>

        <div class="steps-grid">
            <article class="step-card">
                <div class="step-number">01</div>
                <h3>Choose your area</h3>
                <p>Select the Kampala division that is convenient for you.</p>
            </article>

            <article class="step-card">
                <div class="step-number">02</div>
                <h3>Visit a verified partner</h3>
                <p>Present your phone number or AfyaMicro policy ID at the dispensing counter.</p>
            </article>

            <article class="step-card">
                <div class="step-number">03</div>
                <h3>Redeem your cover</h3>
                <p>Your available wallet balance is applied automatically and an official receipt is issued.</p>
            </article>
        </div>
    </div>
</section>

<!-- PARTNER CTA -->
<section class="section cta-section">
    <div class="container cta-content">
        <span class="badge">Working together</span>
        <h2>Are you a pharmacy interested in partnering?</h2>
        <p>
            We welcome enquiries from NDA-licensed pharmacies interested in
            participating in the AfyaMicro Kampala platform.
        </p>
        <a href="contact.php" class="btn btn-white">Contact Our Team</a>
    </div>
</section>

</main>

<!-- PHARMACY CONTENT EXPANSION -->
<section class="directory-guidance section" id="pharmacy-directory-guide">
    <div class="container">
        <div class="section-heading">
            <span class="badge">Using the directory</span>
            <h2 class="section-title">Find and understand pharmacy information</h2>
            <p class="section-subtitle">
                Explore the Kampala divisions covered by this directory and use
                the information available to identify a convenient pharmacy.
            </p>
        </div>

        <div class="directory-guidance-grid">
            <article class="directory-guidance-card">
                <h3>Central Division</h3>
                <p>Includes Kisenyi Care Pharmacy and central Kampala health providers.</p>
            </article>
            <article class="directory-guidance-card">
                <h3>Kawempe Division</h3>
                <p>Includes Kawempe Community Chemist along Bombo Road.</p>
            </article>
            <article class="directory-guidance-card">
                <h3>Makindye Division</h3>
                <p>Includes Katwe Hope Pharmacy along Katwe Main Street.</p>
            </article>
            <article class="directory-guidance-card">
                <h3>Nakawa Division</h3>
                <p>Explore pharmacy information for the Nakawa Division area.</p>
            </article>
            <article class="directory-guidance-card">
                <h3>Rubaga Division</h3>
                <p>Explore pharmacy information for the Rubaga Division area.</p>
            </article>
        </div>

        <div class="content-expansion-note">
            <strong>Before visiting:</strong>
            Ensure your AfyaMicro health wallet is topped up via MTN MoMo, Airtel Money, or a Cash Agent so your prescription can be covered immediately at the counter.
        </div>
    </div>
</section>

<?php uiPublicFooter(); ?>

<script src="assets/js/navigation.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/pharmacies.js"></script>
</body>
</html>
