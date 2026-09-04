<?php
/**
 * admin/messages.php
 * Admin sees a list of conversations (one per client/freelancer who
 * has messaged in) and can open one to reply.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

$admin_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message']) && !empty($_POST['to'])) {
    $stmt = $pdo->prepare('INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)');
    $stmt->execute([$admin_id, (int)$_POST['to'], trim($_POST['message'])]);
    redirect('messages.php?with=' . (int)$_POST['to']);
}

// List everyone who has exchanged a message with the admin
$conversations = $pdo->prepare(
    "SELECT DISTINCT u.user_id, u.full_name, u.role,
        (SELECT message FROM messages m2 WHERE (m2.sender_id=u.user_id AND m2.receiver_id=?) OR (m2.sender_id=? AND m2.receiver_id=u.user_id) ORDER BY m2.sent_at DESC LIMIT 1) AS last_message,
        (SELECT sent_at FROM messages m3 WHERE (m3.sender_id=u.user_id AND m3.receiver_id=?) OR (m3.sender_id=? AND m3.receiver_id=u.user_id) ORDER BY m3.sent_at DESC LIMIT 1) AS last_time
     FROM users u
     WHERE u.user_id IN (
        SELECT sender_id FROM messages WHERE receiver_id = ?
        UNION
        SELECT receiver_id FROM messages WHERE sender_id = ?
     )
     ORDER BY last_time DESC"
);
$conversations->execute([$admin_id, $admin_id, $admin_id, $admin_id, $admin_id, $admin_id]);
$conversations = $conversations->fetchAll();

$with = isset($_GET['with']) ? (int)$_GET['with'] : ($conversations[0]['user_id'] ?? null);

$messages = [];
$with_user = null;
if ($with) {
    $stmt = $pdo->prepare('SELECT full_name, role FROM users WHERE user_id = ?');
    $stmt->execute([$with]);
    $with_user = $stmt->fetch();

    $stmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $stmt->execute([$with, $admin_id]);

    $stmt = $pdo->prepare(
        "SELECT * FROM messages WHERE (sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?) ORDER BY sent_at ASC"
    );
    $stmt->execute([$admin_id, $with, $with, $admin_id]);
    $messages = $stmt->fetchAll();
}

$page_title = 'Messages';
$dash_role  = 'admin';
$active     = 'messages';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<h2>Messages</h2>
<div class="two-col" style="align-items:stretch;margin-top:20px;">
    <div class="card" style="padding:0;">
        <div style="padding:14px;border-bottom:1px solid var(--border);font-size:12px;text-transform:uppercase;color:var(--text-muted);font-weight:700;">Conversations</div>
        <?php if (empty($conversations)): ?>
            <p class="empty-state">No conversations yet.</p>
        <?php else: foreach ($conversations as $c): ?>
            <a href="?with=<?php echo (int)$c['user_id']; ?>" style="display:block;padding:14px;border-bottom:1px solid var(--border); <?php echo $with == $c['user_id'] ? 'background:var(--cream);' : ''; ?>">
                <strong><?php echo h($c['full_name']); ?></strong>
                <span style="font-size:11px;color:var(--text-muted);float:right;"><?php echo $c['last_time'] ? date('D', strtotime($c['last_time'])) : ''; ?></span>
                <div style="font-size:12px;color:var(--text-muted);"><?php echo ucfirst($c['role']); ?></div>
                <div style="font-size:13px;color:var(--text-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?php echo h($c['last_message']); ?></div>
            </a>
        <?php endforeach; endif; ?>
    </div>

    <div class="chat-box" style="max-width:none;">
        <?php if ($with_user): ?>
        <div class="chat-header">
            <strong><?php echo h($with_user['full_name']); ?></strong>
            <span style="font-size:12px;color:var(--text-muted);">(<?php echo ucfirst($with_user['role']); ?>)</span>
        </div>
        <div class="chat-messages">
            <?php foreach ($messages as $m): ?>
                <div class="msg <?php echo $m['sender_id'] == $admin_id ? 'msg-me' : 'msg-them'; ?>">
                    <?php echo h($m['message']); ?>
                    <span class="msg-time"><?php echo date('D H:i', strtotime($m['sent_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <form method="POST" action="messages.php" class="chat-input">
            <input type="hidden" name="to" value="<?php echo (int)$with; ?>">
            <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
            <button type="submit" class="btn btn-primary btn-sm">Send</button>
        </form>
        <?php else: ?>
            <div class="empty-state" style="padding:60px;">Select a conversation</div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
