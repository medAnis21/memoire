<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../include/db.php";

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // check user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create password_resets table if not exists
        $conn->exec("CREATE TABLE IF NOT EXISTS password_resets (
            email VARCHAR(255) PRIMARY KEY,
            code VARCHAR(10) NOT NULL,
            expires_at DATETIME NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

        // generate code and store
        $code = random_int(100000, 999999);
        $expires = date('Y-m-d H:i:s', time() + 15 * 60);
        $up = $conn->prepare("REPLACE INTO password_resets (email, code, expires_at) VALUES (:email, :code, :expires)");
        $up->execute([':email' => $email, ':code' => $code, ':expires' => $expires]);

        // Send email (may not work on local environment)
        $subject = 'Password reset code';
        $body = "Your Sonatrach password reset code is: $code\nThis code will expire in 15 minutes.";
        $headers = "From: no-reply@sonatrach.local\r\n" .
               "Content-Type: text/plain; charset=utf-8\r\n";

        $sent = false;
        try {
          $sent = mail($email, $subject, $body, $headers);
        } catch (Exception $e) {
          $sent = false;
        }

        // store email in session so reset page can prefill
        $_SESSION['reset_email'] = $email;
        if (!$sent) {
          // for local dev show code on reset page via session
          $_SESSION['reset_code'] = $code;
        }

        // redirect user to reset page where they can enter the code
        header('Location: reset.php');
        exit();
    } else {
        $message = 'Please enter a valid email address.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Forgot Password</title>
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
      <h1>Forgot Password</h1>
      <p>Enter your account email and we'll send a verification code.</p>
      <?php if ($message): ?>
        <div class="error-message" style="background:rgba(200,148,42,0.08);color:var(--dark);border-color:rgba(200,148,42,0.18);"><?php echo htmlspecialchars($message); ?></div>
      <?php endif; ?>
      <form method="post" class="auth-form" style="margin-top:12px;">
        <div class="input-box">
          <input type="email" name="email" placeholder="you@example.com" required>
        </div>
        <button class="btn" type="submit">Send Code</button>
      </form>
      <p style="margin-top:12px;"><a href="index.php" class="back-link">Back to login</a></p>
    </div>
  </div>

  <script src="../client/script.js"></script>
</body>
</html>
