<?php
session_start();
require_once __DIR__ . '/db.php';

if (!empty($_SESSION['admin_id'])) {
    header("Location: admin-dashboard.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare(
        "SELECT id, username, password_hash
         FROM admins WHERE username = ? LIMIT 1"
    );
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        header("Location: admin-dashboard.php");
        exit;
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login - ColorNest</title>
<style>
body{font-family:Arial;background:#f3f5f9;display:grid;
place-items:center;min-height:100vh;margin:0}
form{background:white;padding:28px;border-radius:12px;
width:min(340px,85%);box-shadow:0 5px 25px #0001}
input,button{box-sizing:border-box;width:100%;padding:12px;
margin:8px 0;border:1px solid #ddd;border-radius:6px}
button{background:#174b3a;color:white;cursor:pointer}
.error{color:#b42318}
</style>
</head>
<body>
<form method="post">
<h2>ColorNest Admin Login</h2>
<p>Sign in to manage site visit bookings.</p>

<label>Username</label>
<input name="username" required autocomplete="username">

<label>Password</label>
<input type="password" name="password" required
autocomplete="current-password">

<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<button type="submit">Login</button>
</form>
</body>
</html>