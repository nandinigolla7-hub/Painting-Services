<?php
session_start();

if (empty($_SESSION['admin_id'])) {
    header("Location: admin-login.php");
    exit;
}

require_once __DIR__ . '/db.php';

$stmt = $pdo->query(
    "SELECT id, customer_name, phone, address, city,
            property_type, visit_date, service
     FROM bookings
     ORDER BY id DESC"
);

$bookings = $stmt->fetchAll();

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bookings - ColorNest Admin</title>
<style>
*{box-sizing:border-box}
body{font-family:Arial,sans-serif;margin:0;background:#f4f6f8;color:#222}
header{background:#174b3a;color:white;padding:20px 5%;
display:flex;justify-content:space-between;align-items:center;gap:12px}
header a{color:white}
main{padding:25px 4%;overflow-x:auto}
h2{margin-top:0}
table{width:100%;border-collapse:collapse;background:white;
min-width:950px}
th,td{padding:13px;text-align:left;border-bottom:1px solid #e5e7eb}
th{background:#e8efe9}
tr:hover{background:#f8faf9}
.count{margin-bottom:16px}
.empty{background:white;padding:24px;border-radius:8px}
</style>
</head>
<body>
<header>
    <div>
        <h2>ColorNest Admin</h2>
        <div>Welcome, <?= e($_SESSION['admin_username'] ?? 'Admin') ?></div>
    </div>
    <a href="admin-logout.php">Logout</a>
</header>

<main>
<h2>Site Visit Bookings</h2>
<p class="count">Total bookings: <strong><?= count($bookings) ?></strong></p>

<?php if (!$bookings): ?>
    <div class="empty">No bookings have been received yet.</div>
<?php else: ?>
<table>
<thead>
<tr>
<th>ID</th>
<th>Customer</th>
<th>Phone</th>
<th>Address</th>
<th>City</th>
<th>Property</th>
<th>Visit Date</th>
<th>Service</th>
</tr>
</thead>
<tbody>
<?php foreach ($bookings as $booking): ?>
<tr>
<td><?= e($booking['id']) ?></td>
<td><?= e($booking['customer_name']) ?></td>
<td><?= e($booking['phone']) ?></td>
<td><?= e($booking['address']) ?></td>
<td><?= e($booking['city']) ?></td>
<td><?= e($booking['property_type']) ?></td>
<td><?= e($booking['visit_date']) ?></td>
<td><?= e($booking['service']) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
</main>
</body>
</html>