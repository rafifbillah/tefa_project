/**
 * TEFA Bakery and Coffee - Login Script
 * Enhanced with validation, error handling, and animations
 */

(function () {
  "use strict";

  // ==========================================
  // DOM Elements
  // ==========================================
  const elements = {
    loginForm: document.getElementById("loginForm"),
    username: document.getElementById("username"),
    password: document.getElementById("password"),
    role: document.getElementById("role"),
    togglePassword: document.getElementById("togglePassword"),
    loginBtn: document.getElementById("loginBtn"),
    alertContainer: document.getElementById("alertContainer"),
    rememberMe: document.getElementById("rememberMe"),
  };

  // ==========================================
  // Configuration
  // ==========================================
  const config = {
    minUsernameLength: 3,
    minPasswordLength: 4,
    loginDelay: 1500, // Simulated network delay
    roleRedirects: {
      admin: "index.php",
      kasir: "index.php",
      pemasok: "index.php",
    },
  };

  // ==========================================
  // Utility Functions
  // ==========================================

  /**
   * Show alert message
   */
  function showAlert(message, type = "error") {
    if (!elements.alertContainer) return;

    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;
    alertDiv.innerHTML = `
      <i class="fas fa-${type === "error" ? "exclamation-circle" : "check-circle"}"></i>
      <span>${message}</span>
    `;

    elements.alertContainer.innerHTML = "";
    elements.alertContainer.appendChild(alertDiv);

    // Auto-remove after 5 seconds
    setTimeout(() => {
      alertDiv.style.opacity = "0";
      setTimeout(() => alertDiv.remove(), 300);
    }, 5000);
  }

  /**
   * Clear alert
   */
  function clearAlert() {
    if (elements.alertContainer) {
      elements.alertContainer.innerHTML = "";
    }
  }

  /**
   * Validate username
   */
  function validateUsername(username) {
    if (!username || username.trim().length < config.minUsernameLength) {
      return `Username minimal ${config.minUsernameLength} karakter`;
    }
    return null;
  }

  /**
   * Validate password
   */
  function validatePassword(password) {
    if (!password || password.length < config.minPasswordLength) {
      return `Password minimal ${config.minPasswordLength} karakter`;
    }
    return null;
  }

  /**
   * Validate role
   */
  function validateRole(role) {
    if (!role) {
      return "Silakan pilih role Anda";
    }
    return null;
  }

  /**
   * Validate entire form
   */
  function validateForm() {
    const username = elements.username.value.trim();
    const password = elements.password.value;
    const role = elements.role.value;

    const errors = [];

    const usernameError = validateUsername(username);
    if (usernameError) errors.push(usernameError);

    const passwordError = validatePassword(password);
    if (passwordError) errors.push(passwordError);

    const roleError = validateRole(role);
    if (roleError) errors.push(roleError);

    return errors;
  }

  /**
   * Set button loading state
   */
  function setButtonLoading(isLoading) {
    if (isLoading) {
      elements.loginBtn.classList.add("loading");
      elements.loginBtn.disabled = true;
    } else {
      elements.loginBtn.classList.remove("loading");
      elements.loginBtn.disabled = false;
    }
  }

  /**
   * Simulate login (replace with actual API call)
   */
  async function simulateLogin(credentials) {
    try {
      return new Promise((resolve, reject) => {
        setTimeout(() => {
          // Simulate network error occasionally (for demo)
          if (Math.random() < 0.1) {
            reject(new Error("Network error. Please check your connection."));
            return;
          }

          // Simulate successful login
          if (
            credentials.username &&
            credentials.password &&
            credentials.role
          ) {
            resolve({
              success: true,
              user: {
                username: credentials.username,
                role: credentials.role,
              },
            });
          } else {
            reject(new Error("Invalid credentials"));
          }
        }, config.loginDelay);
      });
    } catch (error) {
      throw new Error("Login service unavailable. Please try again later.");
    }
  }

  /**
   * Handle successful login
   */
  function handleLoginSuccess(user) {
    showAlert(`Selamat datang, ${user.username}!`, "success");

    // Save session token instead of user data for security
    if (elements.rememberMe && elements.rememberMe.checked) {
      const sessionToken = btoa(
        JSON.stringify({
          username: user.username,
          role: user.role,
          timestamp: Date.now(),
          expires: Date.now() + 7 * 24 * 60 * 60 * 1000, // 7 days
        }),
      );
      localStorage.setItem("tefa_session", sessionToken);
    }

    // Redirect after showing success message
    setTimeout(() => {
      const redirectUrl = config.roleRedirects[user.role] || "index.php";
      window.location.href = redirectUrl;
    }, 1000);
  }

  /**
   * Handle login error
   */
  function handleLoginError(error) {
    showAlert(error.message || "Login gagal. Silakan coba lagi.", "error");
  }

  // ==========================================
  // Event Handlers
  // ==========================================

  /**
   * Toggle password visibility
   */
  function handleTogglePassword() {
    const passwordInput = elements.password;
    const type =
      passwordInput.getAttribute("type") === "password" ? "text" : "password";
    passwordInput.setAttribute("type", type);

    // Toggle icon
    const icon = elements.togglePassword.querySelector("i");
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");

    // Update aria-label
    const newLabel =
      type === "password" ? "Tampilkan password" : "Sembunyikan password";
    elements.togglePassword.setAttribute("aria-label", newLabel);
  }

  /**
   * Handle form submission
   */
  async function handleFormSubmit(e) {
    e.preventDefault();

    // Clear previous alerts
    clearAlert();

    // Validate form
    const errors = validateForm();
    if (errors.length > 0) {
      showAlert(errors[0], "error");
      return;
    }

    // Get form values
    const credentials = {
      username: elements.username.value.trim(),
      password: elements.password.value,
      role: elements.role.value,
    };

    // Set loading state
    setButtonLoading(true);

    try {
      const response = await simulateLogin(credentials);
      handleLoginSuccess(response.user);
    } catch (error) {
      handleLoginError(error);
    } finally {
      setButtonLoading(false);
    }
  }

  /**
   * Handle input validation feedback
   */
  function handleInputValidation(e) {
    const input = e.target;
    const value = input.value.trim();
    let error = null;

    if (input.id === "username") {
      error = validateUsername(value);
    } else if (input.id === "password") {
      error = validatePassword(value);
    } else if (input.id === "role") {
      error = validateRole(value);
    }

    // You could add visual feedback here (e.g., border color change)
    // For now, we'll just clear alerts on input
    if (value) {
      // Optional: Show inline validation
    }
  }

  // ==========================================
  // Initialize
  // ==========================================

  /**
   * Initialize event listeners
   */
  function initEventListeners() {
    // Toggle password visibility
    if (elements.togglePassword) {
      elements.togglePassword.addEventListener("click", handleTogglePassword);
    }

    // Form submission
    if (elements.loginForm) {
      elements.loginForm.addEventListener("submit", handleFormSubmit);
    }

    // Input validation feedback
    if (elements.username) {
      elements.username.addEventListener("input", handleInputValidation);
    }
    if (elements.password) {
      elements.password.addEventListener("input", handleInputValidation);
    }
    if (elements.role) {
      elements.role.addEventListener("change", handleInputValidation);
    }

    // Check for saved session
    checkSavedSession();
  }

  /**
   * Check for saved session
   */
  function checkSavedSession() {
    const sessionToken = localStorage.getItem("tefa_session");
    if (sessionToken) {
      try {
        const sessionData = JSON.parse(atob(sessionToken));
        if (sessionData.expires > Date.now()) {
          // Valid session, could auto-redirect or show welcome back
          console.log("Welcome back,", sessionData.username);
        } else {
          // Expired session
          localStorage.removeItem("tefa_session");
        }
      } catch (e) {
        // Invalid token
        localStorage.removeItem("tefa_session");
      }
    }
  }

  // ==========================================
  // Document Ready
  // ==========================================

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initEventListeners);
  } else {
    initEventListeners();
  }
})();

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("searchInput");
  const tableRows = document.querySelectorAll("#userTable tr");

  if (searchInput) {
    searchInput.addEventListener("keyup", () => {
      const value = searchInput.value.toLowerCase();

      tableRows.forEach((row) => {
        // Mencari di dalam teks seluruh baris
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(value) ? "" : "none";
      });
    });
  }

  // Contoh event untuk tombol hapus
  const deleteBtns = document.querySelectorAll(".delete");
  deleteBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      if (confirm("Apakah Anda yakin ingin menghapus user ini?")) {
        btn.closest("tr").remove();
      }
    });
  });
});
