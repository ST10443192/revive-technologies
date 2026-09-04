<?php
/**
 * client/messages.php
 * Simple chat thread between the client and the Bee's Events & Decor
 * admin/management team.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('client');

$client_id = $_SESSION['user_id'];

// Find an admin to chat with (in a real system a client could message an assigned coordinator)
$admin = $pdo->query("SELECT user_id, full_name FROM users WHERE role = 'admin' LIMIT 1")->fetch();
$admin_id = $admin['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && $admin_id) {
    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
    $stmt->execute([$client_id, $admin_id, trim($_POST['message'])]);
    redirect('messages.php');
}

// Mark messages from admin as read
if ($admin_id) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt->execute([$admin_id, $client_id]);
}

// Load conversation
$messages = [];
if ($admin_id) {
    $stmt = $pdo->prepare(
        "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
         ORDER BY sent_at ASC"
    );
    $stmt->execute([$client_id, $admin_id, $admin_id, $client_id]);
    $messages = $stmt->fetchAll();
}

$page_title = 'Messages';
$dash_role  = 'client';
$active     = 'messages';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Messages</h2>
<p style="color:var(--text-muted);margin-bottom:24px;">Chat directly with the Bee's Events & Decor team.</p>

<div class="chat-box">
    <div class="chat-header">
        <span class="freelancer-card"><span class="avatar" style="width:36px;height:36px;font-size:14px;margin:0;">BE</span></span>
        <div>
            <strong>Bee's Events & Decor</strong>
            <div style="font-size:12px;color:var(--success);">&bull; Online</div>
        </div>
    </div>
    <div class="chat-messages">
        <?php if (empty($messages)): ?>
            <p style="text-align:center;color:var(--text-muted);">Say hello to start the conversation!</p>
        <?php endif; ?>
        <?php foreach ($messages as $m): ?>
            <div class="msg <?php echo $m['sender_id'] == $client_id ? 'msg-me' : 'msg-them'; ?>">
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
