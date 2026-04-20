<?php
/**
 * Header Include
 * Common head and opening body tag
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="TEFA Bakery and Coffee Dashboard - Manage transactions, products, users, and reports efficiently">
    <meta name="keywords" content="bakery, coffee, dashboard, management, transactions, products, reports, tefa">
    <meta name="author" content="TEFA Bakery and Coffee">
    <meta name="theme-color" content="#2b1b17">
    
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - TEFA Dashboard' : 'TEFA Dashboard'; ?></title>
    
    <!-- Preconnect for Performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo isset($dashboardPage) && $dashboardPage ? 'assets/css/style-dashboard.css' : 'assets/css/style.css'; ?>">
    <?php if (isset($additionalCSS) && !empty($additionalCSS)): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($additionalCSS); ?>">
    <?php endif; ?>
</head>
<body>
