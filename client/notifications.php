<?php
/**
 * client/notifications.php
 * Shows recent activity related to this client: quotation replies,
 * event reminders and new messages. Built from real table data
 * so it stays in sync with what's actually happening.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('client');

$client_id = $_SESSION['user_id'];

// Mark all as read
if (isset($_GET['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ?");
    $stmt->execute([$client_id]);
    redirect('notifications.php');
}

// Build a simple activity feed from quotations + bookings + messages
$notifications = [];

$stmt = $pdo->prepare("SELECT * FROM quotations WHERE client_id = ? AND status != 'sent' ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$client_id]);
foreach ($stmt->fetchAll() as $q) {
    $notifications[] = [
        'icon' => $q['status'] === 'accepted' ? '✅' : '❌',
        'title' => 'Quotation ' . ucfirst($q['status']),
        'detail' => 'Your quotation #QT-' . str_pad($q['quote_id'], 3, '0', STR_PAD_LEFT) . ' has been ' . $q['status'] . '.',
        'time' => $q['created_at'],
    ];
}

$stmt = $pdo->prepare("SELECT * FROM bookings WHERE client_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$client_id]);
foreach ($stmt->fetchAll() as $b) {
    $notifications[] = [
        'icon' => '📅',
        'title' => 'Booking ' . ucfirst($b['status']),
        'detail' => 'Your "' . $b['event_type'] . '" booking is scheduled for ' . nice_date($b['event_date']) . '.',
        'time' => $b['created_at'],
    ];
}

$admin = $pdo->query("SELECT user_id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$unread = 0;
if ($admin) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
    $stmt->execute([$client_id]);
    $unread = $stmt->fetchColumn();
    if ($unread > 0) {
        $notifications[] = [
            'icon' => '💬',
            'title' => "New message from Bee's Events & Decor",
            'detail' => 'You have ' . $unread . ' unread message(s).',
            'time' => date('Y-m-d H:i:s'),
        ];
    }
}

usort($notifications, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

$page_title = 'Notifications';
$dash_role  = 'client';
$active     = 'alerts';
$base       = '../';
$unread_messages = $unread;
$unread_alerts   = $unread;
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div>
        <h2>Notifications</h2>
        <p><?php echo count($notifications); ?> recent update(s)</p>
    </div>
    <a href="?mark_all_read=1" class="btn btn-outline btn-sm">Mark all read</a>
</div>

<?php if (empty($notifications)): ?>
    <div class="card empty-state">No notifications yet.</div>
<?php else: foreach ($notifications as $n): ?>
    <div class="card" style="display:flex;gap:14px;align-items:flex-start;margin-bottom:12px;">
        <div style="font-size:20px;"><?php echo $n['icon']; ?></div>
        <div>
            <strong><?php echo h($n['title']); ?></strong>
            <p style="margin:4px 0;color:var(--text-dark);"><?php echo h($n['detail']); ?></p>
            <span style="font-size:12px;color:var(--text-muted);"><?php echo date('d M Y, H:i', strtotime($n['time'])); ?></span>
        </div>
    </div>
<?php endforeach; endif; ?>

<?php include '../includes/footer_dashboard.php'; ?>
