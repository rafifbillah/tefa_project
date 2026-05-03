<?php
require_once __DIR__ . '/../core/Auth.php';

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $auth = new Auth();
    if ($auth->login($username, $password)) {
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - TEFA Bakery and Coffee</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/gudang-login.css">
</head>
<body>

<div class="container">
  <div class="left-panel" role="region" aria-label="Brand Introduction">
    <div class="overlay"></div>
    <div class="quote-container">
      <blockquote class="quote">
        "Good bread is the most fundamentally satisfying of all foods; and
        good bread with fresh butter, the greatest of feasts."
      </blockquote>
      <cite class="author">— James Beard</cite>
    </div>
  </div>

  <div class="right-panel" role="main">
    <div class="login-box">
      <div class="logo">
        <i class="fas fa-bread-slice" aria-hidden="true"></i>
        <span>TEFA Bakery and Coffee</span>
      </div>

      <div class="welcome-text">
        <h2>Selamat Datang</h2>
        <p class="login-subtitle">Silakan masuk untuk melanjutkan</p>
      </div>

      <?php if (isset($error)) : ?>
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <span><?php echo $error; ?></span>
        </div>
      <?php endif; ?>

      <form id="loginForm" class="login-form" method="POST" action="" novalidate>
        <div class="input-group">
          <div class="input-wrapper">
            <i class="far fa-user main-icon" aria-hidden="true"></i>
            <input
              type="text"
              id="username"
              name="username"
              placeholder=" "
              required
              minlength="3"
              autocomplete="username"
              aria-describedby="usernameHelp"
            />
            <label for="username">Username</label>
          </div>
          <span id="usernameHelp" class="input-help">Min. 3 karakter</span>
        </div>

        <div class="input-group">
          <div class="input-wrapper">
            <i class="fas fa-lock main-icon" aria-hidden="true"></i>
            <input
              type="password"
              id="password"
              name="password"
              placeholder=" "
              required
              minlength="4"
              autocomplete="current-password"
              aria-describedby="passwordHelp"
            />
            <label for="password">Password</label>
            <button
              type="button"
              class="toggle-password"
              id="togglePassword"
              aria-label="Tampilkan password"
            >
              <i class="far fa-eye" aria-hidden="true"></i>
            </button>
          </div>
          <span id="passwordHelp" class="input-help">Min. 4 karakter</span>
        </div>

        <div class="form-extras">
          <label class="remember-me">
            <input type="checkbox" id="rememberMe" name="remember" />
            <span class="checkmark"></span>
            <span>Ingat saya</span>
          </label>
          <a href="#" class="forgot-password">Lupa password?</a>
        </div>

        <button type="submit" name="submit" class="login-btn" id="loginBtn">
          <span class="btn-text">LOGIN</span>
          <span class="btn-icon">
            <i class="fas fa-arrow-right" aria-hidden="true"></i>
          </span>
          <span class="btn-loader">
            <i class="fas fa-spinner fa-spin"></i>
          </span>
        </button>
      </form>

      <p class="login-footer">
        &copy; 2024 TEFA Bakery and Coffee. All rights reserved.
      </p>
    </div>
  </div>
</div>

<script>
    // Fitur Sembunyikan/Tampilkan Password
    document.addEventListener('DOMContentLoaded', () => {
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });
</script>

</body>
</html>