<?php
/**
 * admin/add_pharmacy.php           (role: admin)
 * POST  pharmacy_name, location_area, license_no, contact_phone, address (optional), csrf_token
 * ->    ../admin/dashboard.php?success=pharmacy_added
 * Errors -> ../admin/dashboard.php?error=<code>
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
requireRole('admin');

const ADMIN_DASHBOARD = '../admin/dashboard.php';

requirePost(ADMIN_DASHBOARD);
verifyCsrf(ADMIN_DASHBOARD);

$pharmacyName = inputText('pharmacy_name', 120);
$locationArea = inputText('location_area', 100);
$licenseNo    = strtoupper(inputText('license_no', 50));
$contactPhone = normalizePhone(inputText('contact_phone', 20));
$address      = inputText('address', 255);

setOld([
    'pharmacy_name' => $pharmacyName,
    'location_area' => $locationArea,
    'license_no'    => $licenseNo,
    'contact_phone' => $contactPhone,
    'address'       => $address,
]);

$fail = static fn (string $code) => redirectTo(ADMIN_DASHBOARD, ['error' => $code]);

// ---------------------------- Validation -----------------------------
if ($pharmacyName === '' || $locationArea === '' || $licenseNo === '' || $contactPhone === '') {
    $fail('missing_fields');
}
if (mb_strlen($pharmacyName) < 3) {
    $fail('invalid_pharmacy_name');
}
if (mb_strlen($locationArea) < 2) {
    $fail('invalid_location');
}
if (!preg_match('#^[A-Z0-9/\-. ]{3,50}$#', $licenseNo)) {
    $fail('invalid_license');
}
if (!preg_match('/^0[0-9]{9}$/', $contactPhone)) {          // landline 0414… or mobile 07…
    $fail('invalid_contact_phone');
}

// ---------------------------- Persistence ----------------------------
try {
    $stmt = $pdo->prepare('SELECT 1 FROM pharmacies WHERE name = :name OR license_no = :license LIMIT 1');
    $stmt->execute([':name' => $pharmacyName, ':license' => $licenseNo]);
    if ($stmt->fetchColumn()) {
        $fail('duplicate_pharmacy');
    }

    $stmt = $pdo->prepare(
        "INSERT INTO pharmacies (name, license_no, location, address, phone, status)
         VALUES (:name, :license, :location, :address, :phone, 'active')"
    );
    $stmt->execute([
        ':name'     => $pharmacyName,
        ':license'  => $licenseNo,
        ':location' => $locationArea,
        ':address'  => $address !== '' ? $address : null,
        ':phone'    => $contactPhone,
    ]);
} catch (PDOException $e) {
    error_log('[MHI] add_pharmacy: ' . $e->getMessage());
    $fail($e->getCode() === '23000' ? 'duplicate_pharmacy' : 'pharmacy_failed');
}

unset($_SESSION['old']);

redirectTo(ADMIN_DASHBOARD, ['success' => 'pharmacy_added']);

