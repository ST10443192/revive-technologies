<?php
/**
 * client/request-quote.php
 * Client picks a package tier and submits event details.
 * This creates a new booking with status 'pending' for admin to quote.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('client');

$client_id = $_SESSION['user_id'];

$packages = [
    'starter'  => ['Starter',  'R 10 000 - R 15 000', 'Up to 50 guests'],
    'classic'  => ['Classic',  'R 15 000 - R 30 000', 'Up to 150 guests'],
    'premium'  => ['Premium',  'R 30 000 - R 60 000', 'Up to 300 guests'],
    'bespoke'  => ['Bespoke',  'From R 60 000',        'Unlimited guests'],
];

$prefill = [
    'package'     => $_GET['package'] ?? '',
    'event_type'  => $_GET['event_type'] ?? '',
    'guest_count' => $_GET['guest_count'] ?? '',
    'notes'       => $_GET['notes'] ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package     = $_POST['package'] ?? 'classic';
    $event_type  = trim($_POST['event_type'] ?? 'Event');
    $event_date  = $_POST['event_date'] ?? date('Y-m-d');
    $venue       = trim($_POST['venue'] ?? '');
    $guests      = (int)($_POST['guest_count'] ?? 0);
    $notes       = trim($_POST['notes'] ?? '');
    $elements    = $_POST['elements'] ?? [];

    $full_notes = "Package selected: " . ($packages[$package][0] ?? $package) . "\n";
    if (!empty($elements)) {
        $full_notes .= "Requested elements: " . implode(', ', array_map('trim', $elements)) . "\n";
    }
    $full_notes .= $notes;

    $stmt = $pdo->prepare(
        "INSERT INTO bookings (client_id, event_type, event_date, venue, guest_count, notes, status)
         VALUES (?, ?, ?, ?, ?, ?, 'pending')"
    );
    $stmt->execute([$client_id, $event_type, $event_date, $venue, $guests, $full_notes]);

    set_flash('Your quotation request has been submitted. Our team will prepare a custom quote within 24 hours.');
    redirect('quotations.php');
}

$page_title = 'Request Quote';
$dash_role  = 'client';
$active     = 'quote';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Request a Quotation</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Browse our packages and submit your event details for a personalised quote.</p>

<form method="POST" action="request-quote.php">
    <div class="grid grid-4" style="margin-bottom:28px;">
        <?php foreach ($packages as $key => $info): ?>
        <label class="role-option" style="text-align:left; <?php echo ($key === 'classic' && $prefill['package'] === '') || $prefill['package'] === $key ? 'border-color:var(--gold);' : ''; ?>">
            <?php if ($key === 'classic' && $prefill['package'] === ''): ?><span class="pill" style="background:var(--gold);color:var(--navy);">Most Popular</span><?php endif; ?>
            <input type="radio" name="package" value="<?php echo $key; ?>" style="display:none;" <?php echo ($prefill['package'] === $key) || ($prefill['package'] === '' && $key === 'classic') ? 'checked' : ''; ?>>
            <h3 style="margin-top:10px;"><?php echo h($info[0]); ?></h3>
            <p style="font-weight:700;color:var(--navy);"><?php echo h($info[1]); ?></p>
            <p style="font-size:12px;color:var(--text-muted);">✓ <?php echo h($info[2]); ?></p>
        </label>
        <?php endforeach; ?>
    </div>

    <div class="card" style="max-width:700px;">
        <div class="form-grid">
            <div class="field"><label>Event Type</label><input type="text" name="event_type" placeholder="e.g. Wedding, Birthday, Corporate Gala" value="<?php echo h($prefill['event_type']); ?>" required></div>
            <div class="field"><label>Event Date</label><input type="date" name="event_date" required></div>
        </div>
        <div class="form-grid">
            <div class="field"><label>Venue / Location</label><input type="text" name="venue" placeholder="Venue name &amp; city"></div>
            <div class="field"><label>Guest Count</label><input type="number" name="guest_count" placeholder="e.g. 150" value="<?php echo h($prefill['guest_count']); ?>"></div>
        </div>
        <div class="field">
            <label>Décor Elements You'd Like (select all that apply)</label>
            <div class="grid grid-2" style="gap:8px;">
                <?php
                $elements_list = ['Floral arrangements', 'Balloon styling', 'Fairy/string lighting', 'Draping & backdrops', 'Table & chair styling', 'Stage/podium design', 'Photo booth backdrop', 'Signage & welcome sign'];
                foreach ($elements_list as $el):
                ?>
                <label style="display:flex;align-items:center;gap:8px;font-weight:400;text-transform:none;font-size:14px;">
                    <input type="checkbox" name="elements[]" value="<?php echo h($el); ?>"> <?php echo h($el); ?>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="field"><label>Additional Notes</label><textarea name="notes" placeholder="Anything else our team should know..."><?php echo h($prefill['notes']); ?></textarea></div>
        <button type="submit" class="btn btn-primary btn-block">Complete Quotation Form</button>
    </div>
</form>

<?php include '../includes/footer_dashboard.php'; ?>
