<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../include/db.php";

$login_error = '';

/* REGISTER */
if(isset($_POST['register'])) {

    $username = trim($_POST['register_username']);
    $email    = trim($_POST['register_email']);

    if ($_POST['register_password'] !== $_POST['register_confirm_password']) {
        die("Passwords do not match");
    }

    $password = password_hash($_POST['register_password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $check->execute([':email' => $email]);

    if ($check->rowCount() > 0) {
        die("Email already exists");
    }

    // New registrations are always 'user' role (not admin)
    $stmt = $conn->prepare("
        INSERT INTO users (username, email, password, role)
        VALUES (:username, :email, :password, 'user')
    ");
    $stmt->execute([
        ':username' => $username,
        ':email'    => $email,
        ':password' => $password
    ]);

    echo "Registration successful";
}

/* LOGIN */
if(isset($_POST['login'])) {

    $username = trim($_POST['login_username']);
    $password = $_POST['login_password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];  // 'admin' or 'user'

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../client/index.php");
        }
        exit();
    } else {
        $login_error = 'Email or Password Incorect';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login/Signup Form</title>
        <link rel="stylesheet" href="../client/style.css">
        <link rel="stylesheet" href="SignUp_LogIn_Form.css">
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
    <body>
                <nav>
                    <a href="../client/index.php" class="nav-logo">
                        <span class="nav-logo-text">SONATRACH</span>
                    </a>
                    <ul class="nav-links" id="navLinks">
                        <li><a href="../client/index.php" class="nav-cta">Home</a></li>
                    </ul>
                </nav>
        <div class="container">
          <div class="form-box login">
              <form action="" method="post">
                  <h1>Login</h1>
                  <div class="input-box">
                      <input type="text" name="login_username" placeholder="Username" required>
                      <i class='bx bxs-user'></i>
                  </div>
                  <div class="input-box">
                      <input type="password" name="login_password" placeholder="Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>
                  <div class="forgot-link">
                      <a href="forgot.php">Forgot Password?</a>
                  </div>
                  <button type="submit" name="login" class="btn">Login</button>
                  <?php if (!empty($login_error)): ?>
                  <div class="error-message"><?php echo htmlspecialchars($login_error); ?></div>
                  <?php endif; ?>
              </form>
          </div>

          <div class="form-box register">
              <form action="" method="post">
                  <h1>Registration</h1>
                  <div class="input-box">
                      <input type="text" name="register_username" placeholder="Username" required>
                      <i class='bx bxs-user'></i>
                  </div>
                  <div class="input-box">
                      <input type="email" name="register_email" placeholder="Email" required>
                      <i class='bx bxs-envelope' ></i>
                  </div>
                  <div class="input-box">
                      <input type="password" name="register_password" placeholder="Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>
                  <div class="input-box">
                      <input type="password" name="register_confirm_password" placeholder="Confirm Password" required>
                      <i class='bx bxs-lock-alt' ></i>
                  </div>
                  <button type="submit" name="register" class="btn">Register</button>
              </form>
          </div>

          <div class="toggle-box">
              <div class="toggle-panel toggle-left">
                  <h1>Welcome Back!</h1>
                  <p>Don't have an account?</p>
                  <button class="btn register-btn">Register</button>
              </div>

              <div class="toggle-panel toggle-right">
                  <h1>Hello, Welcome!</h1>
                  <p>Already have an account?</p>
                  <button class="btn login-btn">Login</button>
              </div>
          </div>
      </div>

            <script src="../client/script.js"></script>
            <script src="SignUp_LogIn_Form.js"></script>
  </body>
</html>