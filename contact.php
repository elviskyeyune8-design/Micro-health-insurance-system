<?php
/**
 * contact.php — Contact AfyaMicro Kampala (Content Expansion Design)
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
    <meta name="description" content="Contact AfyaMicro Kampala for platform enquiries and support.">
    <title>Contact Us | AfyaMicro Kampala</title>

    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/about-contact.css">
    <link rel="stylesheet" href="assets/css/content-expansion.css">
</head>
<body>

<?php uiPublicHeader('contact', $loggedIn); ?>

<main>

<section class="section contact-hero">
    <div class="container section-heading">
        <span class="badge">We are here to help</span>
        <h1 class="section-title">Let's talk about your healthcare experience.</h1>
        <p class="section-subtitle">
            Have a question about AfyaMicro Kampala?
            Send us an enquiry using the form below.
        </p>
    </div>
</section>

<section class="section contact-main">
    <div class="container contact-grid">

        <div class="contact-information">
            <span class="badge">Get in touch</span>
            <h2 class="section-title">We would love to hear from you.</h2>

            <p>
                Whether you have a question about the platform,
                the pharmacy directory or a possible partnership,
                use our enquiry form.
            </p>

            <div class="contact-info-card">
                <div class="contact-info-icon">✉</div>
                <div>
                    <h3>General enquiries</h3>
                    <p>Use the enquiry form to send your question.</p>
                </div>
            </div>

            <div class="contact-info-card">
                <div class="contact-info-icon">✚</div>
                <div>
                    <h3>Pharmacy partnerships</h3>
                    <p>Contact us about participating pharmacy enquiries across Kampala.</p>
                </div>
            </div>

            <div class="contact-info-card">
                <div class="contact-info-icon">◷</div>
                <div>
                    <h3>Platform support</h3>
                    <p>Toll-free helpline: 0800 222 018 · Kampala, Uganda.</p>
                </div>
            </div>

            <div class="contact-note">
                <strong>Support notice</strong>
                <p>
                    You can also reach any accredited partner pharmacy counter in Kisenyi, Kawempe, or Katwe for immediate policy verification.
                </p>
            </div>
        </div>

        <div class="contact-form-wrapper">
            <div class="form-header">
                <span class="badge">Send an enquiry</span>
                <h2>How can we help?</h2>
                <p>Complete the details below.</p>
            </div>

            <form id="contactForm" class="contact-form" novalidate>

                <div class="form-group">
                    <label for="contactName">Full name</label>
                    <input class="form-control" type="text"
                           id="contactName" name="name"
                           placeholder="Enter your full name" required>
                    <small class="field-error" id="nameError"></small>
                </div>

                <div class="form-group">
                    <label for="contactEmail">Email address</label>
                    <input class="form-control" type="email"
                           id="contactEmail" name="email"
                           placeholder="example@email.com" required>
                    <small class="field-error" id="emailError"></small>
                </div>

                <div class="form-group">
                    <label for="contactSubject">Enquiry subject</label>
                    <select class="form-control" id="contactSubject"
                            name="subject" required>
                        <option value="">Select an enquiry subject</option>
                        <option value="General enquiry">General enquiry</option>
                        <option value="Account support">Account support</option>
                        <option value="Pharmacy partnership">Pharmacy partnership</option>
                        <option value="Payment enquiry">Payment enquiry</option>
                        <option value="Feedback">Feedback or suggestion</option>
                        <option value="Other">Other</option>
                    </select>
                    <small class="field-error" id="subjectError"></small>
                </div>

                <div class="form-group">
                    <label for="contactMessage">Your message</label>
                    <textarea class="form-control" id="contactMessage"
                              name="message" rows="5"
                              placeholder="Write your message here..."
                              required></textarea>
                    <small class="field-error" id="messageError"></small>
                </div>

                <button type="submit" class="btn btn-primary btn-block">
                    Submit Enquiry
                </button>

                <div class="form-feedback" id="formFeedback"
                     role="status" aria-live="polite"></div>
            </form>
        </div>

    </div>
</section>

<section class="section contact-bottom">
    <div class="container section-heading">
        <span class="badge">Explore more</span>
        <h2 class="section-title">Discover AfyaMicro Kampala</h2>
        <p class="section-subtitle">
            Learn more about the platform and how its healthcare wallet experience works.
        </p>
        <a href="how-it-works.php" class="btn btn-primary">How It Works</a>
    </div>
</section>

</main>

<!-- CONTACT CONTENT EXPANSION -->
<section class="contact-guidance section" id="contact-guidance">
    <div class="container">
        <div class="section-heading">
            <span class="badge">Contact information</span>
            <h2 class="section-title">How can we assist you?</h2>
            <p class="section-subtitle">
                Choose the type of enquiry that best describes what you would
                like to know about AfyaMicro Kampala.
            </p>
        </div>

        <div class="contact-guidance-grid">
            <article class="contact-guidance-card">
                <h3>General enquiries</h3>
                <p>
                    Ask questions about the purpose of AfyaMicro, its micro-insurance
                    wallet services and how the platform works.
                </p>
            </article>
            <article class="contact-guidance-card">
                <h3>Pharmacy partnerships</h3>
                <p>
                    Make an enquiry about onboarding your NDA-licensed community pharmacy to the AfyaMicro network.
                </p>
            </article>
            <article class="contact-guidance-card">
                <h3>Platform support</h3>
                <p>
                    Get assistance with account registration, mobile-money deposits, or prescription claim receipts.
                </p>
            </article>
        </div>
    </div>
</section>

<?php uiPublicFooter(); ?>

<script src="assets/js/navigation.js"></script>
<script src="assets/js/main.js"></script>
<script src="assets/js/contact.js"></script>
</body>
</html>
