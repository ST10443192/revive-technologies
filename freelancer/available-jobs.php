<?php
/**
 * freelancer/available-jobs.php
 * Shows bookings that are confirmed but have no freelancer assigned yet.
 * Applying assigns this freelancer to the booking.
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = (int)$_POST['booking_id'];
    $stmt = $pdo->prepare("UPDATE bookings SET freelancer_id = ? WHERE booking_id = ? AND freelancer_id IS NULL");
    $stmt->execute([$freelancer_id, $booking_id]);
    set_flash('You have applied and been assigned to this job.');
    redirect('available-jobs.php');
}

$stmt = $pdo->query(
    "SELECT b.*, u.full_name AS client_name FROM bookings b
     JOIN users u ON b.client_id = u.user_id
     WHERE b.freelancer_id IS NULL AND b.status IN ('pending','confirmed')
     ORDER BY b.event_date ASC"
);
$jobs = $stmt->fetchAll();

$page_title = 'Available Jobs';
$dash_role  = 'freelancer';
$active     = 'available';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Available Jobs</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Browse open positions and apply directly.</p>

<?php if (empty($jobs)): ?>
    <div class="card empty-state">No open jobs right now - check back soon!</div>
<?php else: foreach ($jobs as $j): ?>
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div>
            <span class="pill pill-quoted"><?php echo h($j['event_type']); ?></span>
            <h3 style="margin-top:8px;"><?php echo h($j['event_type']); ?> for <?php echo h($j['client_name']); ?></h3>
            <p style="color:var(--text-muted);font-size:13px;">
                📅 <?php echo nice_date($j['event_date']); ?> &nbsp; 📍 <?php echo h($j['venue'] ?: 'TBC'); ?>
                <?php if ($j['guest_count']): ?> &nbsp; 👥 <?php echo (int)$j['guest_count']; ?> guests<?php endif; ?>
            </p>
        </div>
        <form method="POST" action="available-jobs.php">
            <input type="hidden" name="booking_id" value="<?php echo (int)$j['booking_id']; ?>">
            <button type="submit" class="btn btn-primary">Apply Now</button>
        </form>
    </div>
<?php endforeach; endif; ?>

<?php include '../includes/footer_dashboard.php'; ?>
