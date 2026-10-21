<?php
session_start();
require_once __DIR__ . '/db.php';

$count = (int) $pdo->query(
    "SELECT COUNT(*) FROM admins"
)->fetchColumn();

if ($count > 0) {
    exit("An admin account already exists. Setup is disabled.");
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || strlen($password) < 12) {
        $error = "Enter a username and a password of at least 12 characters.";
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO admins (username, password_hash)
                 VALUES (?, ?)"
            );

            $stmt->execute([
                $username,
                password_hash($password, PASSWORD_DEFAULT)
            ]);

            header("Location: admin-login.php");
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = "Could not create the account. Check the database setup.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Create Admin - ColorNest</title>
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
<h2>Create Admin</h2>
<p>Set up your ColorNest administrator account.</p>

<label>Username</label>
<input name="username" required maxlength="100"
autocomplete="username">

<label>Password (minimum 12 characters)</label>
<input type="password" name="password" required minlength="12"
autocomplete="new-password">

<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<button type="submit">Create Admin Account</button>
</form>
</body>
</html>