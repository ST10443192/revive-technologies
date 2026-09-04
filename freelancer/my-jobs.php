<?php
/**
 * freelancer/my-jobs.php
 * Shows jobs this freelancer has been assigned to, and lets them
 * mark a job as completed (which then allows a payment claim).
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_complete'])) {
    $booking_id = (int)$_POST['mark_complete'];
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'completed' WHERE booking_id = ? AND freelancer_id = ?");
    $stmt->execute([$booking_id, $freelancer_id]);
    set_flash('Job marked as completed. You can now submit a payment claim.');
    redirect('my-jobs.php');
}

$stmt = $pdo->prepare(
    "SELECT b.*, u.full_name AS client_name FROM bookings b
     JOIN users u ON b.client_id = u.user_id
     WHERE b.freelancer_id = ? ORDER BY b.event_date DESC"
);
$stmt->execute([$freelancer_id]);
$jobs = $stmt->fetchAll();

$page_title = 'My Jobs';
$dash_role  = 'freelancer';
$active     = 'myjobs';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>My Jobs</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Track your accepted and completed events.</p>

<?php if (empty($jobs)): ?>
    <div class="card empty-state">You haven't been assigned to any jobs yet. Browse <a href="available-jobs.php">Available Jobs</a>.</div>
<?php else: foreach ($jobs as $j): ?>
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;gap:14px;align-items:center;">
            <div class="stat-icon <?php echo $j['status'] === 'completed' ? 'icon-green' : 'icon-gold'; ?>">
                <?php echo $j['status'] === 'completed' ? '✓' : '⏱'; ?>
            </div>
            <div>
                <strong><?php echo h($j['event_type']); ?></strong> - <?php echo h($j['client_name']); ?><br>
                <span style="font-size:13px;color:var(--text-muted);">📅 <?php echo nice_date($j['event_date']); ?> &middot; <?php echo h($j['venue'] ?: 'TBC'); ?></span>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <span class="pill pill-<?php echo h($j['status']); ?>"><?php echo ucfirst(h($j['status'])); ?></span>
            <?php if ($j['status'] === 'confirmed'): ?>
                <form method="POST" action="my-jobs.php">
                    <input type="hidden" name="mark_complete" value="<?php echo (int)$j['booking_id']; ?>">
                    <button type="submit" class="btn btn-outline btn-sm">Mark Complete</button>
                </form>
            <?php elseif ($j['status'] === 'completed'): ?>
                <a href="earnings.php?claim=<?php echo (int)$j['booking_id']; ?>" class="btn btn-primary btn-sm">Claim Payment</a>
            <?php endif; ?>
        </div>
    </div>
<?php endforeach; endif; ?>

<?php include '../includes/footer_dashboard.php'; ?>
