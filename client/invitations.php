<?php
/**
 * client/invitations.php
 * Client requests custom invitation card designs.
 * Stored as a booking with event_type = 'Invitation Cards: <style>'.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('client');

$client_id = $_SESSION['user_id'];

$card_styles = [
    'wedding'    => ['Wedding Invitation',      'From R 350/set of 50'],
    'birthday'   => ['Birthday Party Invite',   'From R 250/set of 50'],
    'babyshower' => ['Baby Shower Invite',      'From R 280/set of 50'],
    'reveal'     => ['Gender Reveal Invite',    'From R 300/set of 50'],
    'traditional'=> ['Traditional Ceremony',    'From R 400/set of 50'],
    'corporate'  => ['Corporate Event Invite',  'From R 500/set of 50'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $style       = $_POST['card_style'] ?? 'wedding';
    $styleLabel  = $card_styles[$style][0] ?? 'Invitation Cards';
    $host_names  = trim($_POST['host_names'] ?? '');
    $event_date  = $_POST['event_date'] ?? date('Y-m-d');
    $venue       = trim($_POST['venue'] ?? '');
    $colour      = trim($_POST['colour_scheme'] ?? '');
    $quantity    = trim($_POST['quantity'] ?? '');
    $extra_info  = trim($_POST['additional_info'] ?? '');

    $notes = "Card style: {$styleLabel}\nHost(s): {$host_names}\nColour/Theme: {$colour}\nQuantity: {$quantity}\nNotes: {$extra_info}";

    $stmt = $pdo->prepare(
        "INSERT INTO bookings (client_id, event_type, event_date, venue, guest_count, notes, status)
         VALUES (?, ?, ?, ?, NULL, ?, 'pending')"
    );
    $stmt->execute([$client_id, 'Invitation Cards: ' . $styleLabel, $event_date, $venue, $notes]);

    set_flash('Your invitation card request has been sent to our design team.');
    redirect('dashboard.php');
}

$page_title = 'Invitation Cards';
$dash_role  = 'client';
$active     = 'invitations';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Request Invitation Cards</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Let us design beautiful, personalised invitation cards for your event. Choose a style and fill in your details.</p>

<form method="POST" action="invitations.php">
    <p style="color:var(--gold);font-weight:700;font-size:13px;text-transform:uppercase;">1. Choose a Card Style</p>
    <div class="grid grid-3" style="margin-bottom:28px;">
        <?php foreach ($card_styles as $key => $info): ?>
        <label class="role-option" style="text-align:left;">
            <input type="radio" name="card_style" value="<?php echo $key; ?>" style="display:none;" <?php echo $key === 'wedding' ? 'checked' : ''; ?>>
            <strong><?php echo h($info[0]); ?></strong>
            <p style="font-size:12px;color:var(--text-muted);margin:6px 0;">Personalised design, printed and ready for your event.</p>
            <span style="color:var(--success);font-weight:700;font-size:13px;"><?php echo h($info[1]); ?></span>
        </label>
        <?php endforeach; ?>
    </div>

    <p style="color:var(--gold);font-weight:700;font-size:13px;text-transform:uppercase;">2. Your Event Details</p>
    <div class="card" style="max-width:700px;">
        <div class="form-grid">
            <div class="field"><label>Your Name</label><input type="text" value="<?php echo h($_SESSION['full_name']); ?>" disabled></div>
            <div class="field"><label>Phone / WhatsApp</label><input type="tel" name="phone" placeholder="+27 XX XXX XXXX"></div>
        </div>
        <div class="field"><label>Event / Host Name(s)</label><input type="text" name="host_names" placeholder="e.g. Thabo &amp; Nomvula, or Sipho's 30th"></div>
        <div class="form-grid">
            <div class="field"><label>Event Date</label><input type="date" name="event_date" required></div>
            <div class="field"><label>Venue / Location</label><input type="text" name="venue" placeholder="Venue name &amp; city"></div>
        </div>
        <div class="field"><label>Colour Scheme / Theme</label><input type="text" name="colour_scheme" placeholder="e.g. Dusty rose &amp; gold, Blue &amp; white"></div>
        <div class="field"><label>Quantity Needed</label><input type="number" name="quantity" placeholder="e.g. 100"></div>
        <div class="field"><label>Additional Information</label><textarea name="additional_info" placeholder="Any special wording, RSVP details, dress code..."></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">✉️ Submit Card Request</button>
    </div>
</form>

<?php include '../includes/footer_dashboard.php'; ?>
