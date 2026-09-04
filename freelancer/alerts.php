<?php
/**
 * freelancer/alerts.php
 * Activity feed built from real payment and booking data.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('freelancer');

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM freelancers WHERE user_id = ?');
$stmt->execute([$user_id]);
$freelancer = $stmt->fetch();
$freelancer_id = $freelancer['freelancer_id'] ?? 0;

if (isset($_GET['mark_all_read'])) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ?");
    $stmt->execute([$user_id]);
    redirect('alerts.php');
}

$alerts = [];

$stmt = $pdo->prepare("SELECT * FROM payments WHERE freelancer_id = ? ORDER BY claim_date DESC LIMIT 5");
$stmt->execute([$freelancer_id]);
foreach ($stmt->fetchAll() as $p) {
    $alerts[] = [
        'icon'   => $p['status'] === 'approved' ? '✅' : ($p['status'] === 'rejected' ? '❌' : '⏳'),
        'title'  => 'Payment ' . ucfirst($p['status']),
        'detail' => rand_format($p['amount']) . ' claim is ' . $p['status'] . '.',
        'time'   => $p['claim_date'],
    ];
}

$stmt = $pdo->prepare(
    "SELECT * FROM bookings WHERE freelancer_id IS NULL AND status = 'pending' ORDER BY created_at DESC LIMIT 3"
);
$stmt->execute();
foreach ($stmt->fetchAll() as $b) {
    $alerts[] = [
        'icon' => '🔔',
        'title' => 'New job available',
        'detail' => h($b['event_type']) . ' in ' . h($b['venue'] ?: 'TBC'),
        'time' => $b['created_at'],
    ];
}

usort($alerts, fn($a, $b) => strtotime($b['time']) <=> strtotime($a['time']));

$page_title = 'Alerts';
$dash_role  = 'freelancer';
$active     = 'alerts';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div><h2>Alerts</h2><p><?php echo count($alerts); ?> recent update(s)</p></div>
    <a href="?mark_all_read=1" class="btn btn-outline btn-sm">Mark all read</a>
</div>

<?php if (empty($alerts)): ?>
    <div class="card empty-state">No alerts right now.</div>
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
