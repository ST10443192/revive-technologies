<?php
/**
 * admin/alerts.php
 * Business-wide activity feed: new quotation requests, freelancer
 * applications, payments received, and upcoming events.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

if (isset($_GET['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    redirect('alerts.php');
}

$alerts = [];

$stmt = $pdo->query(
    "SELECT b.*, u.full_name FROM bookings b JOIN users u ON b.client_id = u.user_id
     WHERE b.status = 'pending' ORDER BY b.created_at DESC LIMIT 5"
);
foreach ($stmt->fetchAll() as $b) {
    $alerts[] = ['icon' => '📄', 'title' => 'New quotation request', 'detail' => h($b['full_name']) . ' submitted a ' . h($b['event_type']) . ' request.', 'time' => $b['created_at']];
}

$stmt = $pdo->query(
    "SELECT p.*, u.full_name FROM payments p
     JOIN freelancers f ON p.freelancer_id = f.freelancer_id JOIN users u ON f.user_id = u.user_id
     WHERE p.status = 'pending' ORDER BY p.claim_date DESC LIMIT 5"
);
foreach ($stmt->fetchAll() as $p) {
    $alerts[] = ['icon' => '💲', 'title' => 'Payment claim received', 'detail' => h($p['full_name']) . ' submitted a claim for ' . rand_format($p['amount']) . '.', 'time' => $p['claim_date']];
}

$stmt = $pdo->query(
    "SELECT * FROM inventory WHERE available_stock < total_stock * 0.25 LIMIT 5"
);
foreach ($stmt->fetchAll() as $i) {
    $alerts[] = ['icon' => '⚠️', 'title' => 'Critical stock level', 'detail' => h($i['item_name']) . ' is critically low (' . (int)$i['available_stock'] . ' left).', 'time' => $i['updated_at']];
}

usort($alerts, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

$page_title = 'Alerts';
$dash_role  = 'admin';
$active     = 'alerts';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div><h2>Notifications</h2><p><?php echo count($alerts); ?> recent update(s)</p></div>
    <a href="?mark_all_read=1" class="btn btn-outline btn-sm">Mark all read</a>
</div>

<?php if (empty($alerts)): ?>
    <div class="card empty-state">No notifications right now.</div>
<?php else: foreach ($alerts as $a): ?>
    <div class="card" style="display:flex;gap:14px;align-items:flex-start;margin-bottom:12px;">
        <div style="font-size:20px;"><?php echo $a['icon']; ?></div>
        <div>
            <strong><?php echo h($a['title']); ?></strong>
            <p style="margin:4px 0;"><?php echo $a['detail']; ?></p>
            <span style="font-size:12px;color:var(--text-muted);"><?php echo date('d M Y, H:i', strtotime($a['time'])); ?></span>
        </div>
    </div>
<?php endforeach; endif; ?>

<?php include '../includes/footer_dashboard.php'; ?>
