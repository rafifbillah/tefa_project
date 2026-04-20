<?php
/**
 * Login Page
 * TEFA Bakery and Coffee Login
 */
$pageTitle = 'Login';
$dashboardPage = false;
?>
<?php include 'includes/header.php'; ?>

    <div class="container">
      <!-- Left Panel - Branding -->
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

      <!-- Right Panel - Login Form -->
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

          <!-- Alert Container -->
          <div
            id="alertContainer"
            class="alert-container"
            aria-live="polite"
          ></div>

          <form id="loginForm" class="login-form" novalidate>
            <!-- Username Field -->
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

            <!-- Password Field -->
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

            <!-- Remember Me & Forgot Password -->
            <div class="form-extras">
              <label class="remember-me">
                <input type="checkbox" id="rememberMe" name="remember" />
                <span class="checkmark"></span>
                <span>Ingat saya</span>
              </label>
              <a href="#" class="forgot-password">Lupa password?</a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-btn" id="loginBtn">
              <span class="btn-text">LOGIN</span>
              <span class="btn-icon">
                <i class="fas fa-arrow-right" aria-hidden="true"></i>
              </span>
              <span class="btn-loader">
                <i class="fas fa-spinner fa-spin"></i>
              </span>
            </button>
          </form>

          <!-- Footer Note -->
          <p class="login-footer">
            &copy; 2024 TEFA Bakery and Coffee. All rights reserved.
          </p>
        </div>
      </div>
    </div>

<?php include 'includes/footer.php'; ?>
</html>
