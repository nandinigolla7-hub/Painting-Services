
<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/db.php';

function respond($status, $message) {
    http_response_code($status);

    echo json_encode([
        'success' => $status >= 200 && $status < 300,
        'message' => $message
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, 'Please submit the booking form.');
}

$name = trim($_POST['name'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city = trim($_POST['city'] ?? '');
$property = trim($_POST['property'] ?? '');
$visitDate = trim($_POST['visitDate'] ?? '');
$interest = trim($_POST['interest'] ?? '');

if (
    !$name || !$phone || !$address || !$city ||
    !$property || !$visitDate || !$interest
) {
    respond(422, 'Please complete all required fields.');
}

if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
    respond(422, 'Enter a valid 10-digit mobile number.');
}

$date = DateTime::createFromFormat('!Y-m-d', $visitDate);

if (!$date || $date->format('Y-m-d') !== $visitDate) {
    respond(422, 'Please select a valid visit date.');
}

try {
    // 1. Save the booking to MySQL first.
    $sql = "INSERT INTO bookings
        (customer_name, phone, address, city,
         property_type, visit_date, service)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        $name,
        $phone,
        $address,
        $city,
        $property,
        $visitDate,
        $interest
    ]);

} catch (PDOException $e) {
    error_log("Booking insert failed: " . $e->getMessage());

    respond(500, 'Unable to save your booking. Please try again.');
}

// 2. Send an email notification after saving the booking.
// Email failure will not undo the saved booking.
try {
    require_once __DIR__ . '/vendor/autoload.php';

    $mailConfig = require __DIR__ . '/mail-config.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    $mail->isSMTP();
    $mail->Host = $mailConfig['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure =
        PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = $mailConfig['port'];
    $mail->CharSet = 'UTF-8';

    $mail->setFrom(
        $mailConfig['username'],
        'ColorNest Painting'
    );

    $mail->addAddress($mailConfig['owner_email']);

    $safe = static function ($value) {
        return htmlspecialchars(
            (string) $value,
            ENT_QUOTES,
            'UTF-8'
        );
    };

    $mail->isHTML(true);
    $mail->Subject = 'New Painting Site Visit Booking';

    $mail->Body = '
        <h2>New Site Visit Booking</h2>
        <p><strong>Customer:</strong> ' . $safe($name) . '</p>
        <p><strong>Phone:</strong> ' . $safe($phone) . '</p>
        <p><strong>Address:</strong> ' . $safe($address) . '</p>
        <p><strong>City:</strong> ' . $safe($city) . '</p>
        <p><strong>Property:</strong> ' . $safe($property) . '</p>
        <p><strong>Visit date:</strong> ' . $safe($visitDate) . '</p>
        <p><strong>Service:</strong> ' . $safe($interest) . '</p>
    ';

    $mail->AltBody =
        "New site visit booking\n" .
        "Customer: $name\n" .
        "Phone: $phone\n" .
        "Address: $address\n" .
        "City: $city\n" .
        "Property: $property\n" .
        "Visit date: $visitDate\n" .
        "Service: $interest";

    $mail->send();

} catch (\Throwable $e) {
    error_log('ColorNest email failed: ' . $e->getMessage());
}

// 3. Report booking success even if email delivery fails.
respond(
    200,
    'Thank you! Your site visit request has been saved.'
);
