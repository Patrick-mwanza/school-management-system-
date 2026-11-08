<?php
// includes/page_start.php
// Call this after setting $pageTitle and $breadcrumbs to open the page HTML structure
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="School Management System - <?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?>">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Dashboard'); ?> | SchoolMS</title>
    <link rel="stylesheet" href="<?php echo getBasePath(); ?>css/style.css">
</head>
<body>
<div class="app-layout">

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">

        <?php include __DIR__ . '/header.php'; ?>

        <div class="page-content">
            <?php showFlashMessage(); ?>
