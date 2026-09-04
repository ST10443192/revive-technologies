<?php
/**
 * freelancer/messages.php
 * Chat thread between the freelancer and Bee's Events & Decor management.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('freelancer');

$user_id = $_SESSION['user_id'];
$admin = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$admin_id = $admin['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && $admin_id) {
    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
    $stmt->execute([$user_id, $admin_id, trim($_POST['message'])]);
    redirect('messages.php');
}

if ($admin_id) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt->execute([$admin_id, $user_id]);
}

$messages = [];
if ($admin_id) {
    $stmt = $pdo->prepare(
        "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
         ORDER BY sent_at ASC"
    );
    $stmt->execute([$user_id, $admin_id, $admin_id, $user_id]);
    $messages = $stmt->fetchAll();
}

$page_title = 'Messages';
$dash_role  = 'freelancer';
$active     = 'messages';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Messages</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Communication from Bee's Events & Decor management.</p>

<div class="chat-box">
    <div class="chat-header">
        <span class="avatar" style="width:36px;height:36px;font-size:14px;margin:0;background:var(--navy);color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;">BE</span>
        <div>
            <strong>Bee's Events & Decor</strong>
            <div style="font-size:12px;color:var(--success);">&bull; Online</div>
        </div>
    </div>
    <div class="chat-messages">
        <?php if (empty($messages)): ?>
            <p style="text-align:center;color:var(--text-muted);">No messages yet.</p>
        <?php endif; ?>
        <?php foreach ($messages as $m): ?>
            <div class="msg <?php echo $m['sender_id'] == $user_id ? 'msg-me' : 'msg-them'; ?>">
                <?php echo h($m['message']); ?>
                <span class="msg-time"><?php echo date('D H:i', strtotime($m['sent_at'])); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="POST" action="messages.php" class="chat-input">
        <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
        <button type="submit" class="btn btn-primary"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg></button>
    </form>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
