<?php
// session_start() is now handled by db_connect.php
require '../db_connect.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/dashboard');
    exit;
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Please enter both username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            
            header('Location: /admin/dashboard');
            exit;
        } else {
            $error_message = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Aether Stream</title>
    <link rel="stylesheet" href="/admin/admin_style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-box">
            <h1>Aether Stream</h1>
            <h2>Admin Panel Login</h2>
            
            <?php if(!empty($error_message)): ?>
                <p class="error" style="background-color: #5d1a1a; color: #ffc4c4; padding: 10px; border-radius: 5px; margin-bottom: 15px;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="/admin/index" method="POST">
                <div class="input-group" style="margin-bottom: 15px; text-align: left;">
                    <label for="username" style="display: block; margin-bottom: 5px;">Username</label>
                    <input type="text" id="username" name="username" required autofocus style="width: 100%; padding: 10px; box-sizing: border-box;">
                </div>
                <div class="input-group" style="margin-bottom: 20px; text-align: left;">
                    <label for="password" style="display: block; margin-bottom: 5px;">Password</label>
                    <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; box-sizing: border-box;">
                </div>
                <button type="submit" class="login-btn" style="width: 100%; padding: 12px; cursor: pointer;">Login</button>
            </form>
        </div>
    </div>
</body>
</html>