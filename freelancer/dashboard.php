<?php
/**
 * freelancer/dashboard.php
 * Overview stats: total earned, jobs completed, upcoming jobs, rating.
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

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE freelancer_id = ? AND status = 'approved'");
$stmt->execute([$freelancer_id]);
$total_earned = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE freelancer_id = ? AND status = 'completed'");
$stmt->execute([$freelancer_id]);
$jobs_completed = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE freelancer_id = ? AND status = 'confirmed' AND event_date >= CURDATE()");
$stmt->execute([$freelancer_id]);
$upcoming_jobs = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT b.*, u.full_name AS client_name FROM bookings b
     JOIN users u ON b.client_id = u.user_id
     WHERE b.freelancer_id = ? AND b.status = 'confirmed' AND b.event_date >= CURDATE()
     ORDER BY b.event_date ASC LIMIT 5"
);
$stmt->execute([$freelancer_id]);
$upcoming_list = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$stmt->execute([$user_id]);
$unread_messages = $stmt->fetchColumn();
$unread_alerts = $unread_messages;

$page_title = 'Dashboard';
$dash_role  = 'freelancer';
$active     = 'dashboard';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Dashboard</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Here's an overview of your activity.</p>

<div class="grid grid-4" style="margin-bottom:32px;">
    <div class="card stat-card">
        <div class="stat-icon icon-green">💲</div>
        <div class="num"><?php echo rand_format($total_earned); ?></div>
        <div class="label">Total Earned</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-blue">✅</div>
        <div class="num"><?php echo (int)$jobs_completed; ?></div>
        <div class="label">Jobs Completed</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-gold">⏰</div>
        <div class="num"><?php echo (int)$upcoming_jobs; ?></div>
        <div class="label">Upcoming Jobs</div>
    </div>
    <div class="card stat-card">
        <div class="stat-icon icon-purple">⭐</div>
        <div class="num"><?php echo number_format($freelancer['rating'] ?? 0, 1); ?></div>
        <div class="label">Rating</div>
    </div>
</div>

<div class="two-col">
    <div class="card">
        <div style="display:flex;justify-content:space-between;"><h3>My Upcoming Jobs</h3><a href="my-jobs.php" style="font-size:13px;">View all →</a></div>
        <?php if (empty($upcoming_list)): ?>
            <p class="empty-state">No upcoming jobs yet. Check <a href="available-jobs.php">Available Jobs</a>.</p>
        <?php else: foreach ($upcoming_list as $j): ?>
            <div class="list-item">
                <div>
                    <strong><?php echo h($j['event_type']); ?></strong><br>
                    <span style="font-size:13px;color:var(--text-muted);"><?php echo nice_date($j['event_date']); ?> &middot; <?php echo h($j['venue']); ?></span>
                </div>
            </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card">
        <h3>Your Profile</h3>
        <p><strong>Skill:</strong> <?php echo h($freelancer['skill'] ?? '-'); ?></p>
        <p><strong>Hourly Rate:</strong> <?php echo rand_format($freelancer['hourly_rate'] ?? 0); ?></p>
        <p><strong>Rating:</strong> <?php echo number_format($freelancer['rating'] ?? 0, 1); ?> ★</p>
    </div>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
