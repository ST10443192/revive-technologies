<?php
/**
 * client/quotations.php
 * Shows quotations sent by admin for this client's bookings,
 * and lets the client Accept or Reject each one.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('client');

$client_id = $_SESSION['user_id'];

// Accept / reject action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quote_id'], $_POST['decision'])) {
    $quote_id = (int)$_POST['quote_id'];
    $decision = $_POST['decision'] === 'accepted' ? 'accepted' : 'rejected';

    $stmt = $pdo->prepare('SELECT * FROM quotations WHERE quote_id = ? AND client_id = ?');
    $stmt->execute([$quote_id, $client_id]);
    $quote = $stmt->fetch();

    if ($quote) {
        $stmt = $pdo->prepare('UPDATE quotations SET status = ? WHERE quote_id = ?');
        $stmt->execute([$decision, $quote_id]);

        // If accepted, move the booking to "confirmed"
        if ($decision === 'accepted') {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'confirmed' WHERE booking_id = ?");
            $stmt->execute([$quote['booking_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE booking_id = ?");
            $stmt->execute([$quote['booking_id']]);
        }

        set_flash('Quotation ' . $decision . ' successfully.');
    }
    redirect('quotations.php');
}

$stmt = $pdo->prepare(
    "SELECT q.*, b.event_type, b.event_date, b.venue
     FROM quotations q JOIN bookings b ON q.booking_id = b.booking_id
     WHERE q.client_id = ? ORDER BY q.created_at DESC"
);
$stmt->execute([$client_id]);
$quotations = $stmt->fetchAll();

$page_title = 'My Quotations';
$dash_role  = 'client';
$active     = 'quote';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>My Quotations</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Review quotations sent by our team and accept or decline.</p>

<?php if (empty($quotations)): ?>
    <div class="card empty-state">No quotations yet. <a href="request-quote.php">Request a quotation</a> to get started.</div>
<?php else: foreach ($quotations as $q): ?>
    <div class="card" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <div>
                <h3><?php echo h($q['event_type']); ?></h3>
                <p style="color:var(--text-muted);font-size:13px;">
                    <?php echo nice_date($q['event_date']); ?> &middot; <?php echo h($q['venue'] ?: 'Venue TBC'); ?>
                </p>
                <p><?php echo nl2br(h($q['details'])); ?></p>
            </div>
            <div style="text-align:right;">
                <div style="font-size:22px;font-weight:700;color:var(--navy);"><?php echo rand_format($q['amount']); ?></div>
                <span class="pill pill-<?php echo h($q['status']); ?>"><?php echo ucfirst(h($q['status'])); ?></span>
            </div>
        </div>
        <?php if ($q['status'] === 'sent'): ?>
        <form method="POST" action="quotations.php" style="display:flex;gap:10px;margin-top:16px;">
            <input type="hidden" name="quote_id" value="<?php echo (int)$q['quote_id']; ?>">
            <button type="submit" name="decision" value="accepted" class="btn btn-success btn-sm">Accept Quotation</button>
            <button type="submit" name="decision" value="rejected" class="btn btn-danger btn-sm">Decline</button>
        </form>
        <?php endif; ?>
    </div>
<?php endforeach; endif; ?>

<?php include '../includes/footer_dashboard.php'; ?>
