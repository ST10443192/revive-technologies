<?php
/**
 * freelancer/earnings.php
 * Shows total/monthly earnings and a payout history table.
 * Also lets the freelancer submit a new payment claim for a
 * completed job that hasn't been claimed yet.
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

// Submit a new payment claim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['amount'])) {
    $booking_id = (int)$_POST['booking_id'];
    $amount     = (float)$_POST['amount'];

    $stmt = $pdo->prepare('INSERT INTO payments (freelancer_id, booking_id, amount, status) VALUES (?, ?, ?, ?)');
    $stmt->execute([$freelancer_id, $booking_id, $amount, 'pending']);

    set_flash('Payment claim submitted for admin approval.');
    redirect('earnings.php');
}

$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM payments WHERE freelancer_id = ? AND status = 'approved'");
$stmt->execute([$freelancer_id]);
$total_earned = $stmt->fetchColumn();

$stmt = $pdo->prepare(
    "SELECT p.*, b.event_type FROM payments p JOIN bookings b ON p.booking_id = b.booking_id
     WHERE p.freelancer_id = ? ORDER BY p.claim_date DESC"
);
$stmt->execute([$freelancer_id]);
$payouts = $stmt->fetchAll();

// Completed jobs that don't have a payment claim yet
$stmt = $pdo->prepare(
    "SELECT b.* FROM bookings b
     WHERE b.freelancer_id = ? AND b.status = 'completed'
     AND b.booking_id NOT IN (SELECT booking_id FROM payments WHERE freelancer_id = ?)"
);
$stmt->execute([$freelancer_id, $freelancer_id]);
$claimable = $stmt->fetchAll();

$page_title = 'Earnings';
$dash_role  = 'freelancer';
$active     = 'earnings';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Earnings</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Track your payments and submit new claims.</p>

<?php if (!empty($claimable)): ?>
<h3>Submit a Payment Claim</h3>
<div class="card" style="max-width:520px;margin-bottom:28px;">
    <form method="POST" action="earnings.php">
        <div class="field">
            <label>Completed Job</label>
            <select name="booking_id" required>
                <?php foreach ($claimable as $c): ?>
                    <option value="<?php echo (int)$c['booking_id']; ?>"><?php echo h($c['event_type']); ?> - <?php echo nice_date($c['event_date']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Claim Amount (R)</label><input type="number" step="0.01" name="amount" placeholder="e.g. 2200" required></div>
        <button type="submit" class="btn btn-primary btn-block">Submit Claim</button>
    </form>
</div>
<?php endif; ?>

<h3 style="margin-bottom:16px;">Payout History</h3>
<div class="table-wrap">
<table class="data-table">
    <tr><th>Job</th><th>Date Claimed</th><th>Amount</th><th>Status</th></tr>
    <?php if (empty($payouts)): ?>
        <tr><td colspan="4" class="empty-state">No payment claims yet.</td></tr>
    <?php else: foreach ($payouts as $p): ?>
        <tr>
            <td><?php echo h($p['event_type']); ?></td>
            <td><?php echo date('d M Y', strtotime($p['claim_date'])); ?></td>
            <td><?php echo rand_format($p['amount']); ?></td>
            <td><span class="pill pill-<?php echo h($p['status']); ?>"><?php echo ucfirst(h($p['status'])); ?></span></td>
        </tr>
    <?php endforeach; endif; ?>
</table>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
