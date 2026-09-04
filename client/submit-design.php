<?php
/**
 * client/submit-design.php
 * A client can describe their vision for our design team.
 * Stored as a booking "note" with event_type = 'Custom Design Request'.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('client');

$client_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['design_title'] ?? '');
    $event_date  = $_POST['event_date'] ?? date('Y-m-d');
    $palette     = trim($_POST['colour_palette'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $moodboard   = trim($_POST['moodboard_url'] ?? '');

    $notes = "Design Title: {$title}\nColour Palette: {$palette}\nDescription: {$description}\nMood board: {$moodboard}";

    $stmt = $pdo->prepare(
        "INSERT INTO bookings (client_id, event_type, event_date, venue, guest_count, notes, status)
         VALUES (?, 'Custom Design Request', ?, NULL, NULL, ?, 'pending')"
    );
    $stmt->execute([$client_id, $event_date, $notes]);

    set_flash('Your design brief has been submitted. Our team will be in touch soon!');
    redirect('dashboard.php');
}

$page_title = 'Submit Design';
$dash_role  = 'client';
$active     = 'submit';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Submit Your Design</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Have a vision? Share your ideas, inspiration boards, or design files with our team.</p>

<div class="card" style="max-width:640px;">
    <form method="POST" action="submit-design.php">
        <div class="form-grid">
            <div class="field"><label>Your Name</label><input type="text" value="<?php echo h($_SESSION['full_name']); ?>" disabled></div>
            <div class="field"><label>Event Date</label><input type="date" name="event_date" required></div>
        </div>
        <div class="field"><label>Design Title</label><input type="text" name="design_title" placeholder="e.g. Boho Garden Wedding Décor" required></div>
        <div class="field"><label>Colour Palette</label><input type="text" name="colour_palette" placeholder="e.g. Dusty rose, sage green, champagne gold"></div>
        <div class="field"><label>Design Description</label><textarea name="description" placeholder="Describe your vision in detail - the mood, key elements, inspiration sources..." required></textarea></div>
        <div class="field"><label>Reference Images / Mood Board URL</label><input type="text" name="moodboard_url" placeholder="Pinterest board link, Google Drive, etc."></div>
        <button type="submit" class="btn btn-primary btn-block">Submit Design Brief</button>
    </form>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
