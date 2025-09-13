<?php
session_start();
require 'language_loader.php'; // Load language file
require 'db_connect.php';

if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header('Location: /home');
    exit();
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($username) || empty($email) || empty($password)) {
        $errors[] = $lang['all_fields_required'];
    }
    if ($password !== $confirm_password) {
        $errors[] = $lang['passwords_no_match'];
    }
    if (strlen($password) < 6) {
        $errors[] = $lang['password_min_length'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = $lang['invalid_email'];
    }
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = $lang['username_email_taken'];
        }
    }
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt= $pdo->prepare($sql);
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $_SESSION['success_message'] = $lang['registration_success'];
            header("Location: /");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang']; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $lang['create_account']; ?> - <?php echo $lang['aether_stream']; ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="style.css?v=1.1">
</head>
<body>
    <div class="login-container">
        <div class="logo"><?php echo $lang['aether_stream']; ?></div>
        <h2><?php echo $lang['create_account']; ?></h2>
        <?php if (!empty($errors)): ?>
            <div class="message error">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="/register">
            <input type="text" name="username" class="input-field" placeholder="<?php echo $lang['username']; ?>" required>
            <input type="email" name="email" class="input-field" placeholder="<?php echo $lang['email_address']; ?>" required>
            <input type="password" name="password" class="input-field" placeholder="<?php echo $lang['password']; ?>" required>
            <input type="password" name="confirm_password" class="input-field" placeholder="<?php echo $lang['confirm_password']; ?>" required>
            <button type="submit" class="submit-btn"><?php echo $lang['register']; ?></button>
        </form>
        <div class="form-footer-link">
            <p><?php echo $lang['already_have_account']; ?> <a href="/"><?php echo $lang['login_here']; ?></a></p>
        </div>
    </div>
</body>
</html>