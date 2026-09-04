<?php
/**
 * admin/quotations.php
 * Admin can view all bookings, send a quotation (amount + details)
 * for any pending booking, and see the status of existing quotations.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

// Create a new quotation for a booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['amount'])) {
    $booking_id = (int)$_POST['booking_id'];
    $amount     = (float)$_POST['amount'];
    $elements   = $_POST['elements'] ?? [];
    $terms      = trim($_POST['payment_terms'] ?? '');
    $details    = trim($_POST['details'] ?? '');

    $extra = [];
    if (!empty($elements)) $extra[] = 'Includes: ' . implode(', ', array_map('trim', $elements));
    if ($terms !== '') $extra[] = 'Payment terms: ' . $terms;
    if (!empty($extra)) $details = implode("\n", $extra) . "\n\n" . $details;

    $stmt = $pdo->prepare('SELECT client_id FROM bookings WHERE booking_id = ?');
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch();

    if ($booking) {
        $stmt = $pdo->prepare(
            "INSERT INTO quotations (booking_id, client_id, amount, details, status) VALUES (?, ?, ?, ?, 'sent')"
        );
        $stmt->execute([$booking_id, $booking['client_id'], $amount, $details]);

        $stmt = $pdo->prepare("UPDATE bookings SET status = 'quoted' WHERE booking_id = ?");
        $stmt->execute([$booking_id]);

        set_flash('Quotation sent to the client.');
    }
    redirect('quotations.php');
}

$filter = $_GET['filter'] ?? 'all';
$where  = '';
if (in_array($filter, ['pending', 'approved', 'rejected'], true)) {
    $map = ['pending' => 'sent', 'approved' => 'accepted', 'rejected' => 'rejected'];
    $where = "WHERE q.status = '" . $map[$filter] . "'";
}

$quotations = $pdo->query(
    "SELECT q.*, u.full_name AS client_name, b.event_type, b.event_date
     FROM quotations q
     JOIN users u ON q.client_id = u.user_id
     JOIN bookings b ON q.booking_id = b.booking_id
     {$where}
     ORDER BY q.created_at DESC"
)->fetchAll();

$total_quoted   = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM quotations")->fetchColumn();
$approved_value = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM quotations WHERE status='accepted'")->fetchColumn();
$pending_value  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM quotations WHERE status='sent'")->fetchColumn();
$total_count    = $pdo->query("SELECT COUNT(*) FROM quotations")->fetchColumn();
$approved_count = $pdo->query("SELECT COUNT(*) FROM quotations WHERE status='accepted'")->fetchColumn();
$approval_rate  = $total_count ? round($approved_count / $total_count * 100) : 0;

// Bookings still needing a quote
$unquoted = $pdo->query(
    "SELECT b.*, u.full_name AS client_name FROM bookings b JOIN users u ON b.client_id = u.user_id
     WHERE b.status = 'pending' ORDER BY b.created_at DESC"
)->fetchAll();

$page_title = 'Quotations';
$dash_role  = 'admin';
$active     = 'quotations';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div><h2>Quotations</h2><p>Manage and track all client quotations</p></div>
</div>

<?php if (!empty($unquoted)): ?>
<h3>Create a Quotation</h3>
<div class="card" style="max-width:640px;margin-bottom:28px;">
    <form method="POST" action="quotations.php">
        <div class="field">
            <label>Booking</label>
            <select name="booking_id" required>
                <?php foreach ($unquoted as $b): ?>
                    <option value="<?php echo (int)$b['booking_id']; ?>">
                        <?php echo h($b['client_name']); ?> - <?php echo h($b['event_type']); ?> (<?php echo nice_date($b['event_date']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field"><label>Quoted Amount (R)</label><input type="number" step="0.01" name="amount" placeholder="e.g. 24500" required></div>
        <div class="field">
            <label>Included Elements (select all that apply)</label>
            <div class="grid grid-2" style="gap:8px;">
                <?php
                $q_elements = ['Floral arrangements', 'Balloon styling', 'Fairy/string lighting', 'Draping & backdrops', 'Table & chair styling', 'Stage/podium design', 'Photo booth backdrop', 'Signage & welcome sign', 'Setup & breakdown crew', 'Delivery & transport'];
                foreach ($q_elements as $qe):
                ?>
                <label style="display:flex;align-items:center;gap:8px;font-weight:400;text-transform:none;font-size:14px;">
                    <input type="checkbox" name="elements[]" value="<?php echo h($qe); ?>"> <?php echo h($qe); ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="field"><label>Payment Terms</label>
            <select name="payment_terms">
                <option value="30% deposit, balance on delivery">30% deposit, balance on delivery</option>
                <option value="50% deposit, balance on delivery">50% deposit, balance on delivery</option>
                <option value="Full payment upfront">Full payment upfront</option>
                <option value="Full payment on completion">Full payment on completion</option>
            </select>
        </div>
        <div class="field"><label>Details</label><textarea name="details" placeholder="Package breakdown, inclusions, terms..."></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">+ Create Quotation</button>
    </form>
</div>
<?php endif; ?>

<div class="tab-filter">
    <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
    <a href="?filter=pending" class="<?php echo $filter === 'pending' ? 'active' : ''; ?>">Pending</a>
    <a href="?filter=approved" class="<?php echo $filter === 'approved' ? 'active' : ''; ?>">Approved</a>
    <a href="?filter=rejected" class="<?php echo $filter === 'rejected' ? 'active' : ''; ?>">Rejected</a>
</div>

<?php if (empty($quotations)): ?>
    <div class="card empty-state">No quotations found for this filter.</div>
<?php else: foreach ($quotations as $q): ?>
    <div class="card" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
        <div>
            <span style="font-size:12px;color:var(--text-muted);">QT-<?php echo str_pad($q['quote_id'],3,'0',STR_PAD_LEFT); ?></span>
            <h3 style="margin:4px 0;"><?php echo h($q['client_name']); ?></h3>
            <p style="color:var(--text-muted);font-size:13px;"><?php echo h($q['event_type']); ?> &middot; <?php echo nice_date($q['event_date']); ?></p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:20px;font-weight:700;color:var(--navy);"><?php echo rand_format($q['amount']); ?></div>
            <span class="pill pill-<?php echo $q['status'] === 'sent' ? 'pending' : $q['status']; ?>">
                <?php echo $q['status'] === 'sent' ? 'Pending' : ucfirst($q['status']); ?>
            </span>
        </div>
    </div>
<?php endforeach; endif; ?>

<div class="grid grid-4" style="margin-top:26px;">
    <div class="card stat-card"><div class="num"><?php echo $approval_rate; ?>%</div><div class="label">Approval Rate</div></div>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
