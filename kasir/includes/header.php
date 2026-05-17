<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kasir - TEFA Bakery and Coffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F9FAFB; }
        
        /* Base Variables matching Gudang/Admin */
        :root {
            --sidebar-dark: #2D1A11;
            --white: #ffffff;
            --orange: #D97706;
            --grey-light: #e5e7eb;
            --grey-mid: #9ca3af;
            --grey-dark: #4b5563;
            --text-dark: #1f2937;
            --background: #F9FAFB;
            --green: #10b981;
        }

        /* Responsive Sidebar Styles */
        @media (max-width: 1024px) {
            .sidebar {
                position: fixed !important;
                left: -280px;
                top: 0;
                bottom: 0;
                z-index: 100;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 20px 0 50px rgba(0,0,0,0.1);
            }
            .sidebar.active {
                left: 0;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(4px);
                z-index: 90;
            }
            .sidebar-overlay.active {
                display: block;
            }
            body.overflow-hidden {
                overflow: hidden;
            }
        }
    </style>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.classList.toggle('overflow-hidden');
        }
    </script>
    <!-- Use Gudang CSS for consistency -->
    <link rel="stylesheet" href="../assets/css/gudang-sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/gudang-header.css?v=<?php echo time(); ?>">
</head>
<body class="flex h-screen overflow-hidden">
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>