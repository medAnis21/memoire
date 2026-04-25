<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../include/db.php";

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $code = trim($_POST['code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email.';
    } elseif ($password === '') {
        $message = 'Enter a new password.';
    } elseif ($password !== $confirm) {
        $message = 'Passwords do not match.';
    } else {
        // validate code
        $q = $conn->prepare("SELECT code, expires_at FROM password_resets WHERE email = :email");
        $q->execute([':email' => $email]);
        $row = $q->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $message = 'No reset request found for that email.';
        } else {
            if ($row['code'] !== $code) {
                $message = 'Invalid code.';
            } elseif (strtotime($row['expires_at']) < time()) {
                $message = 'Code has expired. Please request a new one.';
            } else {
                // update user password
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $up = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
                $up->execute([':password' => $hash, ':email' => $email]);
                // remove reset row
                $conn->prepare("DELETE FROM password_resets WHERE email = :email")->execute([':email' => $email]);
                $message = 'Password reset successful. You can now login.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Reset Password</title>
  <link rel="stylesheet" href="../client/style.css">
  <link rel="stylesheet" href="SignUp_LogIn_Form.css">
</head>
<body>
  <nav>
    <a href="../client/index.php" class="nav-logo"><span class="nav-logo-text">SONATRACH</span></a>
    <ul class="nav-links"><li><a href="../client/index.php" class="nav-cta">Home</a></li></ul>
  </nav>

  <div class="container">
    <div style="padding:40px;max-width:520px;margin:60px auto;text-align:left;">
      <h1>Reset Password</h1>
      <?php if ($message): ?>
        <div class="error-message" style="background:rgba(200,148,42,0.08);color:var(--dark);border-color:rgba(200,148,42,0.18);"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
      <form method="post" class="auth-form" style="margin-top:12px;">
        <div class="input-box"><input type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars(
            
            
            
            
            
            
            
            
            
            
            
            
            
            ($_SESSION['reset_email'] ?? '')
        ); ?>"></div>
        <div class="input-box"><input type="text" name="code" placeholder="Verification code" required></div>
        <div class="input-box"><input type="password" name="password" placeholder="New password" required></div>
        <div class="input-box"><input type="password" name="confirm" placeholder="Confirm password" required></div>
        <button class="btn" type="submit">Reset Password</button>
      </form>
      <?php if (!empty($_SESSION['reset_code'])): ?>
        <div class="error-message" style="background:rgba(0,0,0,0.04);color:var(--dark);border-color:rgba(0,0,0,0.06);margin-top:12px;">Test code (local dev): <?php echo htmlspecialchars($_SESSION['reset_code']); ?></div>
        <?php unset($_SESSION['reset_code']); ?>
      <?php endif; ?>
      <p style="margin-top:12px;"><a href="index.php" class="back-link">Back to login</a></p>
    </div>
  </div>

  <script src="../client/script.js"></script>
</body>
</html>
