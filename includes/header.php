<?php
// includes/header.php
// Top navigation bar - included in every protected page
// Expects: $pageTitle (string), $breadcrumbs (array of ['label'=>'','url'=>''])
if (!isset($pageTitle))   $pageTitle   = 'Dashboard';
if (!isset($breadcrumbs)) $breadcrumbs = [];
?>
<header class="top-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">☰</button>
        <div class="page-title">
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            <?php if (!empty($breadcrumbs)): ?>
                <div class="breadcrumb">
                    <a href="<?php echo getBasePath(); ?>dashboard.php">Home</a>
                    <?php foreach ($breadcrumbs as $crumb): ?>
                        <span>›</span>
                        <?php if (!empty($crumb['url'])): ?>
                            <a href="<?php echo $crumb['url']; ?>"><?php echo htmlspecialchars($crumb['label']); ?></a>
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($crumb['label']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="header-right">
        <span class="current-time" id="liveClock"></span>
        <div class="user-avatar" title="<?php echo htmlspecialchars(getCurrentUserName()); ?>">
            <?php echo strtoupper(substr(getCurrentUserName(), 0, 1)); ?>
        </div>
    </div>
</header>
