<?php
/**
 * includes/header_dashboard.php
 * -------------------------------------------------
 * Shared topbar for the Client / Freelancer / Admin portals.
 *
 * Expected variables set by the page BEFORE including this file:
 *   $page_title      (string) e.g. "Portfolio"
 *   $dash_role       (string) 'client' | 'freelancer' | 'admin'
 *   $active          (string) key of the current nav item (see $nav_items below)
 *   $base            (string) relative path back to project root, e.g. '../'
 *   $unread_messages (int)  optional
 *   $unread_alerts   (int)  optional
 * -------------------------------------------------
 */
$unread_messages = $unread_messages ?? 0;
$unread_alerts   = $unread_alerts   ?? 0;

$nav_items = [
    'client' => [
        'dashboard'   => ['label' => 'Portfolio',        'href' => 'dashboard.php'],
        'submit'      => ['label' => 'Submit Design',    'href' => 'submit-design.php'],
        'invitations' => ['label' => 'Invitation Cards',  'href' => 'invitations.php'],
        'messages'    => ['label' => 'Messages',         'href' => 'messages.php'],
        'alerts'      => ['label' => 'Notifications',    'href' => 'notifications.php'],
        'quote'       => ['label' => 'Request Quote',    'href' => 'request-quote.php'],
    ],
    'freelancer' => [
        'dashboard' => ['label' => 'Dashboard',      'href' => 'dashboard.php'],
        'available' => ['label' => 'Available Jobs', 'href' => 'available-jobs.php'],
        'myjobs'    => ['label' => 'My Jobs',         'href' => 'my-jobs.php'],
        'messages'  => ['label' => 'Messages',        'href' => 'messages.php'],
        'alerts'    => ['label' => 'Alerts',          'href' => 'alerts.php'],
        'earnings'  => ['label' => 'Earnings',        'href' => 'earnings.php'],
    ],
    'admin' => [
        'dashboard'   => ['label' => 'Dashboard',   'href' => 'dashboard.php'],
        'quotations'  => ['label' => 'Quotations',  'href' => 'quotations.php'],
        'inventory'   => ['label' => 'Inventory',   'href' => 'inventory.php'],
        'calendar'    => ['label' => 'Calendar',    'href' => 'calendar.php'],
        'freelancers' => ['label' => 'Freelancers', 'href' => 'freelancers.php'],
        'messages'    => ['label' => 'Messages',    'href' => 'messages.php'],
        'alerts'      => ['label' => 'Alerts',      'href' => 'alerts.php'],
    ],
];

$portal_labels = ['client' => 'Client Portal', 'freelancer' => 'Freelancer Portal', 'admin' => 'Admin'];

// Small inline icon (24x24 stroke icons) shown before each nav label.
$nav_icons = [
    'dashboard'   => '<path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z"/>',
    'submit'      => '<path d="M12 16V4M12 4 7 9M12 4l5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/>',
    'invitations' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
    'messages'    => '<path d="M21 11.5a8.38 8.38 0 0 1-8.5 8.5 8.5 8.5 0 0 1-4-1L3 20l1-4.5A8.38 8.38 0 0 1 21 11.5Z"/>',
    'alerts'      => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>',
    'quote'       => '<path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/><path d="M9 13h6M9 17h6"/>',
    'available'   => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    'myjobs'      => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="m8 12 3 3 5-6"/>',
    'earnings'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M9 9.5c0-1.2 1.2-2 3-2s3 .8 3 2-1.2 1.5-3 2-3 .9-3 2.1 1.3 1.9 3 1.9 3-.7 3-1.9"/>',
    'quotations'  => '<path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2Z"/><path d="M9 13h6M9 17h6"/>',
    'inventory'   => '<path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>',
    'calendar'    => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
    'freelancers' => '<circle cx="9" cy="8" r="3.2"/><path d="M3.5 20a5.5 5.5 0 0 1 11 0"/><circle cx="17.5" cy="9.5" r="2.6"/><path d="M15.2 13.2a4.6 4.6 0 0 1 6.3 4.3"/>',
];
function nav_icon_svg($markup) {
    return '<svg class="nav-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $markup . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo h($page_title ?? 'Dashboard'); ?> - Bee's Events & Decor</title>
<link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>

<div class="dash-topbar">
    <div class="container">
        <div class="dash-brand">
            <img src="<?php echo $base; ?>assets/img/logo-icon.png" alt="Bee's Events & Decor" class="logo-img">
            <span class="brand-name" style="font-size:16px;">Bee's Events &amp; Decor</span>
            <div>
                <span class="portal-label"><?php echo h($portal_labels[$dash_role]); ?></span>
                <span class="welcome">Welcome, <?php echo h($dash_role === 'admin' ? "Bee's Events & Decor" : ($_SESSION['full_name'] ?? '')); ?> 👋</span>
            </div>
        </div>

        <div class="dash-nav">
            <?php foreach ($nav_items[$dash_role] as $key => $item): ?>
                <a href="<?php echo h($item['href']); ?>" class="<?php echo $active === $key ? 'active' : ''; ?>">
                    <?php echo isset($nav_icons[$key]) ? nav_icon_svg($nav_icons[$key]) : ''; ?>
                    <?php echo h($item['label']); ?>
                    <?php if ($key === 'messages' && $unread_messages > 0): ?>
                        <span class="badge-count"><?php echo (int)$unread_messages; ?></span>
                    <?php endif; ?>
                    <?php if ($key === 'alerts' && $unread_alerts > 0): ?>
                        <span class="badge-count"><?php echo (int)$unread_alerts; ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="dash-user">
            <?php $display_name = $dash_role === 'admin' ? "Bee's Events & Decor" : ($_SESSION['full_name'] ?? ''); ?>
            <span class="dash-avatar"><?php echo h(strtoupper(substr($display_name, 0, 1))); ?></span>
            <span><?php echo h($display_name); ?></span>
            <a href="<?php echo $base; ?>auth/logout.php" class="btn btn-outline-light btn-sm">Logout</a>
        </div>
    </div>
</div>

<div class="dash-body">
<div class="container">

<?php $flash = get_flash(); ?>
<?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'error' : 'success'; ?>">
        <?php echo h($flash['message']); ?>
    </div>
<?php endif; ?>
