<?php
/**
 * admin/dashboard.php
 * Business overview: active bookings, revenue, pending quotations,
 * active freelancers, recent bookings and low-stock alerts.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

$active_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status IN ('confirmed','quoted')")->fetchColumn();
$pending_quotes  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'pending'")->fetchColumn();
$active_freelancers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'freelancer' AND status = 'active'")->fetchColumn();

$total_quotes_count = $pdo->query("SELECT COUNT(*) FROM quotations")->fetchColumn();
$accepted_quotes_count = $pdo->query("SELECT COUNT(*) FROM quotations WHERE status = 'accepted'")->fetchColumn();
$approval_rate = $total_quotes_count ? round($accepted_quotes_count / $total_quotes_count * 100) : 0;

$recent_bookings = $pdo->query(
    "SELECT b.*, u.full_name AS client_name FROM bookings b JOIN users u ON b.client_id = u.user_id
     ORDER BY b.created_at DESC LIMIT 5"
)->fetchAll();

$low_stock = $pdo->query(
    "SELECT * FROM inventory WHERE available_stock < total_stock * 0.5 ORDER BY available_stock ASC LIMIT 5"
)->fetchAll();

// "Upcoming tasks" - bookings with an event happening in the next 14 days
$upcoming_tasks = $pdo->query(
    "SELECT b.*, u.full_name AS client_name FROM bookings b JOIN users u ON b.client_id = u.user_id
     WHERE b.event_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
     AND b.status IN ('confirmed','quoted')
     ORDER BY b.event_date ASC LIMIT 5"
)->fetchAll();

$unread_messages = $pdo->query("SELECT COUNT(*) FROM messages WHERE receiver_id = " . (int)$_SESSION['user_id'] . " AND is_read = 0")->fetchColumn();
$unread_alerts = $unread_messages;

$page_title = 'Dashboard';
$dash_role  = 'admin';
$active     = 'dashboard';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Dashboard</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Welcome back! Here's your business overview.</p>

<div class="grid grid-4" style="margin-bottom:32px;">
    <div class="card stat-card">
        <div class="stat-icon icon-blue">📅</div>
        <div class="num"><?php echo (int)$active_bookings; ?></div>
        <div class="label">Active Bookings</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-gold">📄</div>
        <div class="num"><?php echo (int)$pending_quotes; ?></div>
        <div class="label">Pending Quotations</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-purple">👥</div>
        <div class="num"><?php echo (int)$active_freelancers; ?></div>
        <div class="label">Active Freelancers</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-green">✅</div>
        <div class="num"><?php echo $approval_rate; ?>%</div>
        <div class="label">Approval Rate</div>
    </div>
</div>

<div class="two-col">
    <div class="card">
        <div style="display:flex;justify-content:space-between;"><h3>Recent Bookings</h3><a href="quotations.php" style="font-size:13px;">View all →</a></div>
        <?php if (empty($recent_bookings)): ?>
            <p class="empty-state">No bookings yet.</p>
        <?php else: foreach ($recent_bookings as $b): ?>
            <div class="list-item">
                <div>
                    <strong><?php echo h($b['client_name']); ?></strong><br>
                    <span style="font-size:13px;color:var(--text-muted);">BK-<?php echo str_pad($b['booking_id'],3,'0',STR_PAD_LEFT); ?> &middot; <?php echo nice_date($b['event_date']); ?></span>
                </div>
                <span class="pill pill-<?php echo h($b['status']); ?>"><?php echo ucfirst(h($b['status'])); ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card">
        <div style="display:flex;justify-content:space-between;"><h3>Low Stock Alerts</h3><a href="inventory.php" style="font-size:13px;">Manage →</a></div>
        <?php if (empty($low_stock)): ?>
            <p class="empty-state">All inventory levels look healthy.</p>
        <?php else: foreach ($low_stock as $item): ?>
            <div class="list-item">
                <div>
                    <strong><?php echo h($item['item_name']); ?></strong><br>
                    <span style="font-size:13px;color:var(--danger);">Current: <?php echo (int)$item['available_stock']; ?> | Total: <?php echo (int)$item['total_stock']; ?></span>
                </div>
                <a href="inventory.php" class="btn btn-danger btn-sm">Restock</a>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="two-col" style="margin-top:24px;">
    <div class="card">
        <div style="display:flex;justify-content:space-between;"><h3>Upcoming Tasks</h3><a href="quotations.php" style="font-size:13px;">View all →</a></div>
        <p style="color:var(--text-muted);font-size:13px;margin-top:-6px;">Confirmed or quoted events happening in the next 14 days.</p>
        <?php if (empty($upcoming_tasks)): ?>
            <p class="empty-state">Nothing due in the next two weeks.</p>
        <?php else: foreach ($upcoming_tasks as $t): ?>
            <div class="list-item">
                <div>
                    <strong>Prepare: <?php echo h($t['client_name']); ?> &middot; <?php echo h($t['event_type']); ?></strong><br>
                    <span style="font-size:13px;color:var(--text-muted);"><?php echo nice_date($t['event_date']); ?></span>
                </div>
                <span class="pill pill-<?php echo h($t['status']); ?>"><?php echo ucfirst(h($t['status'])); ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card">
        <h3>Manage Packages</h3>
        <p style="color:var(--text-muted);font-size:13px;margin-top:-6px;margin-bottom:16px;">Review the sample packages shown to clients on their portfolio page.</p>
        <a href="packages.php" class="btn btn-outline btn-block">View Packages →</a>
    </div>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
