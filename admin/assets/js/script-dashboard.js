/**
 * TEFA Bakery and Coffee - Dashboard Script
 * Enhanced with animations, interactions, and chart configurations
 */

(function () {
  "use strict";

  // ==========================================
  // Configuration
  // ==========================================
  const config = {
    chart: {
      animation: {
        duration: 1500,
        easing: "easeInOutQuart",
      },
      colors: {
        primary: "#d4832c",
        primaryLight: "rgba(212, 131, 44, 0.1)",
        secondary: "#2b1b17",
        success: "#27ae60",
        grid: "rgba(0, 0, 0, 0.05)",
      },
      instances: {
        lineChart: null,
        pieChart: null,
      },
    },
    counter: {
      duration: 2000,
      step: 50,
    },
    sidebar: {
      collapsedWidth: 80,
      expandedWidth: 260,
    },
  };

  // ==========================================
  // DOM Elements
  // ==========================================
  const elements = {
    sidebar: document.getElementById("sidebar"),
    toggleBtn: document.getElementById("toggle-btn"),
    menuItems: document.querySelectorAll(".menu-item"),
    logoutBtn: document.getElementById("logoutBtn"),
    pageTitle: document.getElementById("pageTitle"),
    filterBtns: document.querySelectorAll(".filter-btn"),
    counters: document.querySelectorAll(".counter"),
  };

  // ==========================================
  // Image Error Handling
  // ==========================================

  /**
   * Handle image loading errors
   */
  function handleImageError(img) {
    const isAvatar = img.classList.contains("avatar");
    const fallbackSrc = isAvatar
      ? "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTAiIGhlaWdodD0iOTAiIHZpZXdCb3g9IjAgMCA5MCA5MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDUiIGN5PSI0NSIgcj0iNDUiIGZpbGw9IiNkNDgzMmMiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI1MCIgaGVpZ2h0PSI1MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IndoaXRlIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCI+CjxwYXRoIGQ9Ik0xNSAyMEg5YTMgMyAwIDAgMC0zIDN2MWEzIDMgMCAwIDAgMyAzaDEyYTMgMyAwIDAgMCAzLTN2LTFhMyAzIDAgMCAwLTMtM3oiLz4KPGNpcmNsZSBjeD0iMTIiIGN5PSIxMCIgcj0iNCIvPgo8L3N2Zz4KPC9zdmc+"
      : "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiBmaWxsPSIjZGRkIi8+Cjx0ZXh0IHg9IjQwIiB5PSI0NSIgZm9udC1mYW1pbHk9IkFyaWFsLCBzYW5zLXNlcmlmIiBmb250LXNpemU9IjEwIiBmaWxsPSIjNjY2IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iMC4zZW0iPk5vIEltYWdlPC90ZXh0Pgo8L3N2Zz4=";

    img.src = fallbackSrc;
  }

  /**
   * Initialize image error handling
   */
  function initImageErrorHandling() {
    const images = document.querySelectorAll("img");
    images.forEach((img) => {
      img.addEventListener("error", () => handleImageError(img));
    });
  }

  /**
   * Show notification message
   */
  function showNotification(message, type = "info") {
    // Create notification element
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
      <i class="fas fa-${type === "success" ? "check-circle" : type === "error" ? "exclamation-circle" : "info-circle"}"></i>
      <span>${message}</span>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.add("show"), 10);

    // Auto-remove
    setTimeout(() => {
      notification.classList.remove("show");
      setTimeout(() => notification.remove(), 300);
    }, 3000);
  }

  /**
   * Resize all charts to fit their containers
   */
  function resizeCharts() {
    // Use transitionend event to ensure resize happens after CSS transition completes
    const sidebar = elements.sidebar;
    if (sidebar) {
      const handleTransitionEnd = () => {
        if (config.chart.instances.lineChart) {
          config.chart.instances.lineChart.resize();
        }
        if (config.chart.instances.pieChart) {
          config.chart.instances.pieChart.resize();
        }
        sidebar.removeEventListener("transitionend", handleTransitionEnd);
      };

      // Check if transition is happening
      const computedStyle = window.getComputedStyle(sidebar);
      const transitionDuration = parseFloat(computedStyle.transitionDuration);

      if (transitionDuration > 0) {
        sidebar.addEventListener("transitionend", handleTransitionEnd);
      } else {
        // No transition, resize immediately
        if (config.chart.instances.lineChart) {
          config.chart.instances.lineChart.resize();
        }
        if (config.chart.instances.pieChart) {
          config.chart.instances.pieChart.resize();
        }
      }
    }
  }

  // ==========================================
  // Sidebar Functionality
  // ==========================================

  /**
   * Initialize sidebar toggle
   */
  function initSidebar() {
    if (!elements.toggleBtn || !elements.sidebar) return;

    elements.toggleBtn.addEventListener("click", () => {
      elements.sidebar.classList.toggle("collapsed");

      // Update aria-expanded
      const isExpanded = !elements.sidebar.classList.contains("collapsed");
      elements.toggleBtn.setAttribute("aria-expanded", isExpanded);

      // Update icon
      const icon = elements.toggleBtn.querySelector("i");
      if (elements.sidebar.classList.contains("collapsed")) {
        icon.classList.replace("fa-bars", "fa-arrow-right");
      } else {
        icon.classList.replace("fa-arrow-right", "fa-bars");
      }

      // Resize charts after sidebar transition
      resizeCharts();
    });
  }

  // ==========================================
  // Menu Navigation
  // ==========================================

  /**
   * Initialize menu navigation
   */
  function initMenuNavigation() {
    if (!elements.menuItems.length) return;

    elements.menuItems.forEach((item) => {
      item.addEventListener("click", (e) => {
        // Remove active class from all items
        elements.menuItems.forEach((menuItem) => {
          menuItem.classList.remove("active");
        });

        // Add active class to clicked item
        item.classList.add("active");

        // Allow navigation to proceed normally
      });
    });
  }

  // ==========================================
  // Counter Animation
  // ==========================================

  /**
   * Animate counter from 0 to target
   */
  function animateCounter(element, target) {
    const duration = config.counter.duration;
    const startTimestamp = performance.now();
    const isCurrency = element
      .closest(".stat-card")
      ?.querySelector("p")
      ?.textContent.includes("Pendapatan");

    const step = (timestamp) => {
      const progress = Math.min((timestamp - startTimestamp) / duration, 1);
      const easeOutQuart = 1 - Math.pow(1 - progress, 4);
      const currentValue = Math.floor(easeOutQuart * target);

      if (isCurrency) {
        element.textContent = currentValue.toLocaleString("id-ID");
      } else {
        element.textContent = currentValue;
      }

      if (progress < 1) {
        window.requestAnimationFrame(step);
      } else {
        // Final value with formatting
        if (isCurrency) {
          element.textContent = target.toLocaleString("id-ID");
        } else {
          element.textContent = target;
        }
      }
    };

    window.requestAnimationFrame(step);
  }

  /**
   * Initialize counter animations
   */
  function initCounters() {
    if (!elements.counters.length) return;

    // Use Intersection Observer for scroll-triggered animation
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            const target = parseInt(entry.target.dataset.target, 10);
            if (!isNaN(target)) {
              animateCounter(entry.target, target);
            }
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.5 },
    );

    elements.counters.forEach((counter) => {
      observer.observe(counter);
    });
  }

  // ==========================================
  // Chart Configurations
  // ==========================================

  /**
   * Get common chart options
   */
  function getCommonOptions() {
    return {
      responsive: true,
      maintainAspectRatio: false,
      animation: config.chart.animation,
      plugins: {
        legend: {
          display: false,
        },
      },
    };
  }

  /**
   * Initialize Line Chart (Sales Statistics)
   */
  function initLineChart() {
    const canvas = document.getElementById("salesLineChart");
    if (!canvas || typeof Chart === "undefined") return;

    const ctx = canvas.getContext("2d");

    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 250);
    gradient.addColorStop(0, config.chart.colors.primaryLight);
    gradient.addColorStop(1, "rgba(212, 131, 44, 0)");

    config.chart.instances.lineChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: ["Jun", "Jul", "Aug", "Sep", "Oct"],
        datasets: [
          {
            data: [2100, 2400, 2300, 2982, 2800],
            borderColor: config.chart.colors.primary,
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: config.chart.colors.primary,
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
          },
        ],
      },
      options: {
        ...getCommonOptions(),
        scales: {
          y: {
            display: false,
            beginAtZero: false,
            min: 1800,
          },
          x: {
            grid: {
              display: false,
            },
            ticks: {
              color: "#888",
              font: {
                family: "Poppins",
                size: 11,
              },
            },
          },
        },
        interaction: {
          intersect: false,
          mode: "index",
        },
        plugins: {
          tooltip: {
            backgroundColor: config.chart.colors.secondary,
            titleFont: {
              family: "Poppins",
              size: 13,
            },
            bodyFont: {
              family: "Poppins",
              size: 12,
            },
            padding: 12,
            cornerRadius: 8,
            displayColors: false,
            callbacks: {
              label: function (context) {
                return "Sales: " + context.parsed.y.toLocaleString("id-ID");
              },
            },
          },
        },
      },
    });
  }

  /**
   * Initialize Doughnut Chart (Payment Methods)
   */
  function initPieChart() {
    const canvas = document.getElementById("paymentPieChart");
    if (!canvas || typeof Chart === "undefined") return;

    const ctx = canvas.getContext("2d");

    config.chart.instances.pieChart = new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: ["Tunai", "Transfer"],
        datasets: [
          {
            data: [75, 25],
            backgroundColor: [
              config.chart.colors.primary,
              config.chart.colors.secondary,
            ],
            borderWidth: 0,
            hoverOffset: 8,
          },
        ],
      },
      options: {
        ...getCommonOptions(),
        cutout: "70%",
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                family: "Poppins",
                size: 12,
              },
              color: "#666",
            },
          },
          tooltip: {
            backgroundColor: config.chart.colors.secondary,
            titleFont: {
              family: "Poppins",
              size: 13,
            },
            bodyFont: {
              family: "Poppins",
              size: 12,
            },
            padding: 12,
            cornerRadius: 8,
            callbacks: {
              label: function (context) {
                return context.label + ": " + context.parsed + "%";
              },
            },
          },
        },
      },
    });
  }

  // ==========================================
  // Filter Tabs
  // ==========================================

  /**
   * Initialize filter tabs
   */
  function initFilterTabs() {
    if (!elements.filterBtns.length) return;

    elements.filterBtns.forEach((btn) => {
      btn.addEventListener("click", () => {
        // Remove active class from all
        elements.filterBtns.forEach((b) => b.classList.remove("active"));

        // Add active to clicked
        btn.classList.add("active");

        // Here you would typically update chart data
        // For demo, we'll just log the filter
        console.log("Filter changed to:", btn.dataset.filter);
      });
    });
  }

  // ==========================================
  // Logout Handler
  // ==========================================

  /**
   * Initialize logout handler
   */
  function initLogout() {
    if (!elements.logoutBtn) return;

    elements.logoutBtn.addEventListener("click", (e) => {
      e.preventDefault();

      // Clear session data
      localStorage.removeItem("tefa_session");

      // Show logout message
      showNotification("Logged out successfully", "success");

      // Redirect to login after a brief delay
      setTimeout(() => {
        window.location.href = "login.php";
      }, 1000);
    });
  }

  // ==========================================
  // Window Resize Handler
  // ==========================================

  /**
   * Handle window resize
   */
  function handleResize() {
    resizeCharts();
  }

  // ==========================================
  // Initialize
  // ==========================================

  /**
   * Initialize all dashboard functionality
   */
  function init() {
    initSidebar();
    initMenuNavigation();
    initCounters();
    initFilterTabs();
    initLogout();
    initImageErrorHandling();

    // Initialize charts
    initLineChart();
    initPieChart();

    // Window resize listener
    window.addEventListener("resize", handleResize);

    console.log("TEFA Dashboard initialized successfully");
  }

  // ==========================================
  // Document Ready
  // ==========================================

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
