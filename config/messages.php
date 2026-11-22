<?php
/**
 * config/messages.php
 * ---------------------------------------------------------------------
 * Maps the short ?success= / ?error= codes used in redirects to
 * human-readable messages. Loaded automatically by config/db.php.
 *
 * Frontend usage (any view):
 *     <?php if ($alert = statusMessage()): ?>
 *         <div class="alert alert-<?= e($alert['type']) ?>"><?= e($alert['message']) ?></div>
 *     <?php endif; ?>
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

const STATUS_MESSAGES = [
    'success' => [
        'welcome'           => 'Welcome back!',
        'registered'        => 'Registration successful. Your health wallet is ready — make your first deposit.',
        'logged_out'        => 'You have been logged out.',
        'deposit_completed' => 'Deposit received and added to your health wallet.',
        'claim_approved'    => 'Claim approved: the full prescription cost was covered by the wallet.',
        'claim_partial'     => 'Claim partially covered: collect the co-payment shown from the client.',
        'pharmacy_added'    => 'Pharmacy added successfully.',
    ],
    'error' => [
        // General / session
        'login_required'         => 'Please log in to continue.',
        'access_denied'          => 'You do not have permission to access that page.',
        'invalid_request'        => 'Invalid request. Please use the form provided.',
        'session_expired'        => 'Your session expired. Please try again.',
        'missing_fields'         => 'Please fill in all required fields.',
        'server_error'           => 'Something went wrong on our side. Please try again.',
        // Login
        'invalid_credentials'    => 'Invalid phone number or password.',
        'account_suspended'      => 'This account is suspended. Please contact the administrator.',
        // Registration
        'invalid_name'           => 'Enter your full name (3–120 characters).',
        'invalid_phone'          => 'Enter a valid Ugandan mobile number, e.g. 0701234567.',
        'invalid_nin'            => 'National ID Number must be 14 letters/digits.',
        'invalid_pharmacy'       => 'Please choose a valid partner pharmacy.',
        'weak_password'          => 'Password must be at least 6 characters.',
        'password_mismatch'      => 'Passwords do not match.',
        'phone_taken'            => 'An account with this phone number already exists.',
        'nin_taken'              => 'An account with this National ID Number already exists.',
        'registration_failed'    => 'Registration failed. Please try again.',
        // Deposits
        'invalid_amount'         => 'Enter a valid amount greater than zero.',
        'amount_too_large'       => 'Amount exceeds the maximum allowed per transaction (UGX 5,000,000).',
        'invalid_payment_method' => 'Choose MTN MoMo, Airtel Money or Cash (Agent).',
        'invalid_payer_phone'    => 'Enter a valid mobile money number, e.g. 0772123456.',
        'wallet_not_found'       => 'No health wallet was found for this account.',
        'wallet_inactive'        => 'This health wallet is not active.',
        'deposit_failed'         => 'The deposit could not be processed. Please try again.',
        // Claims
        'client_not_found'       => 'No active client matches that phone or policy number.',
        'invalid_prescription'   => 'Enter the prescription notes (medicines dispensed).',
        'insufficient_balance'   => 'Insufficient balance: the client\'s wallet is empty. The client must pay in full.',
        'no_pharmacy_assigned'   => 'Your attendant account is not linked to a pharmacy. Contact the administrator.',
        'claim_failed'           => 'The claim could not be processed. Please try again.',
        'claim_not_found'        => 'Claim not found.',
        // Pharmacies
        'invalid_pharmacy_name'  => 'Enter the pharmacy name (3–120 characters).',
        'invalid_location'       => 'Enter the location / area (2–100 characters).',
        'invalid_license'        => 'Enter a valid NDA licence number (3–50 characters).',
        'invalid_contact_phone'  => 'Enter a valid 10-digit phone number, e.g. 0414250101.',
        'duplicate_pharmacy'     => 'A pharmacy with that name or licence number already exists.',
        'pharmacy_failed'        => 'The pharmacy could not be saved. Please try again.',
        // Reports
        'invalid_date_range'     => 'Invalid date range. Use YYYY-MM-DD and make sure "from" is before "to".',
    ],
];

/**
 * Read ?success= or ?error= from the current URL.
 * Returns ['type' => 'success'|'error', 'code' => string, 'message' => string] or null.
 * Unknown codes are ignored (prevents arbitrary text injection via the URL).
 */
function statusMessage(): ?array
{
    foreach (['error', 'success'] as $type) {
        $code = $_GET[$type] ?? null;
        if (is_string($code) && isset(STATUS_MESSAGES[$type][$code])) {
            return ['type' => $type, 'code' => $code, 'message' => STATUS_MESSAGES[$type][$code]];
        }
    }
    return null;
}

