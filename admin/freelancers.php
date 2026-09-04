<?php
/**
 * admin/freelancers.php
 * Lists all freelancers, lets admin suspend/activate accounts,
 * and approve/reject their pending payment claims.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

// Approve / reject a payment claim
if (isset($_GET['approve_payment'])) {
    $stmt = $pdo->prepare("UPDATE payments SET status = 'approved' WHERE payment_id = ?");
    $stmt->execute([(int)$_GET['approve_payment']]);
    set_flash('Payment claim approved.');
    redirect('freelancers.php');
}
if (isset($_GET['reject_payment'])) {
    $stmt = $pdo->prepare("UPDATE payments SET status = 'rejected' WHERE payment_id = ?");
    $stmt->execute([(int)$_GET['reject_payment']]);
    set_flash('Payment claim rejected.');
    redirect('freelancers.php');
}

// Toggle suspend/activate
if (isset($_GET['toggle_status'])) {
    $stmt = $pdo->prepare("UPDATE users SET status = IF(status='active','suspended','active') WHERE user_id = ? AND role = 'freelancer'");
    $stmt->execute([(int)$_GET['toggle_status']]);
    set_flash('Freelancer status updated.');
    redirect('freelancers.php');
}

$search = trim($_GET['q'] ?? '');
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT f.*, u.full_name, u.email, u.phone, u.status FROM freelancers f JOIN users u ON f.user_id = u.user_id WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= ' AND u.full_name LIKE ?'; $params[] = "%{$search}%"; }
if ($filter === 'active')   $sql .= " AND u.status = 'active'";
if ($filter === 'unavailable') $sql .= " AND u.status = 'suspended'";
$sql .= ' ORDER BY u.full_name ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$freelancers = $stmt->fetchAll();

$total_freelancers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='freelancer'")->fetchColumn();
$available_now = $pdo->query(
    "SELECT COUNT(*) FROM users u JOIN freelancers f ON u.user_id=f.user_id
     WHERE u.status='active' AND f.freelancer_id NOT IN (SELECT freelancer_id FROM bookings WHERE freelancer_id IS NOT NULL AND status='confirmed')"
)->fetchColumn();
$on_assignment = $pdo->query("SELECT COUNT(DISTINCT freelancer_id) FROM bookings WHERE status='confirmed' AND freelancer_id IS NOT NULL")->fetchColumn();
$pending_claims = $pdo->query("SELECT COUNT(*) FROM payments WHERE status='pending'")->fetchColumn();

$claims = $pdo->query(
    "SELECT p.*, u.full_name, b.event_type FROM payments p
     JOIN freelancers f ON p.freelancer_id = f.freelancer_id
     JOIN users u ON f.user_id = u.user_id
     JOIN bookings b ON p.booking_id = b.booking_id
     WHERE p.status = 'pending' ORDER BY p.claim_date DESC"
)->fetchAll();

$page_title = 'Freelancers';
$dash_role  = 'admin';
$active     = 'freelancers';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div><h2>Freelancer Management</h2><p>Manage tasks, schedules, and payment claims</p></div>
</div>

<div class="grid grid-4" style="margin-bottom:28px;">
    <div class="card stat-card"><div class="num"><?php echo (int)$total_freelancers; ?></div><div class="label">Total Freelancers</div></div>
    <div class="card stat-card"><div class="num" style="color:var(--success);"><?php echo (int)$available_now; ?></div><div class="label">Available Now</div></div>
    <div class="card stat-card"><div class="num" style="color:var(--info);"><?php echo (int)$on_assignment; ?></div><div class="label">On Assignment</div></div>
    <div class="card stat-card"><div class="num" style="color:var(--warning);"><?php echo (int)$pending_claims; ?></div><div class="label">Pending Claims</div></div>
</div>

<form method="GET" action="freelancers.php" class="search-box">
    <input type="text" name="q" placeholder="Search freelancers..." value="<?php echo h($search); ?>">
    <button type="submit" class="btn btn-outline">Search</button>
</form>
<div class="tab-filter">
    <a href="?filter=all" class="<?php echo $filter==='all'?'active':''; ?>">All</a>
    <a href="?filter=active" class="<?php echo $filter==='active'?'active':''; ?>">Active</a>
    <a href="?filter=unavailable" class="<?php echo $filter==='unavailable'?'active':''; ?>">Unavailable</a>
</div>

<div class="two-col">
    <div>
        <?php if (empty($freelancers)): ?>
            <div class="card empty-state">No freelancers found.</div>
        <?php else: foreach ($freelancers as $f): ?>
        <div class="card" style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div style="display:flex;gap:14px;">
                    <div class="avatar" style="width:52px;height:52px;font-size:16px;background:var(--navy);color:#fff;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <?php
                        $parts = explode(' ', $f['full_name']);
                        echo strtoupper(substr($parts[0],0,1) . substr($parts[count($parts)-1] ?? '',0,1));
                        ?>
                    </div>
                    <div>
                        <strong><?php echo h($f['full_name']); ?></strong><br>
                        <span style="font-size:13px;color:var(--text-muted);"><?php echo h($f['skill']); ?></span><br>
                        <span class="stars">★ <?php echo number_format($f['rating'],1); ?></span>
                        &nbsp; <span style="font-size:13px;">💲 <?php echo rand_format($f['hourly_rate']); ?>/hour</span><br>
                        <span style="font-size:13px;color:var(--text-muted);">📞 <?php echo h($f['phone']); ?> &nbsp; ✉️ <?php echo h($f['email']); ?></span>
                        <?php if (!empty($f['portfolio_files'])): ?>
                        <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                            <?php foreach (array_slice(explode(',', $f['portfolio_files']), 0, 6) as $img): ?>
                                <a href="../<?php echo h($img); ?>" target="_blank">
                                    <img src="../<?php echo h($img); ?>" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                                </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div style="text-align:right;">
                    <span class="pill pill-<?php echo $f['status']==='active'?'active':'suspended'; ?>"><?php echo ucfirst($f['status']); ?></span><br><br>
                    <a href="?toggle_status=<?php echo (int)$f['user_id']; ?>" class="btn btn-outline btn-sm">
                        <?php echo $f['status']==='active' ? 'Suspend' : 'Activate'; ?>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>

    <div class="card">
        <h3>Pending Payment Claims</h3>
        <?php if (empty($claims)): ?>
            <p class="empty-state">No pending claims.</p>
        <?php else: foreach ($claims as $c): ?>
            <div style="border-bottom:1px solid var(--border);padding:14px 0;">
                <div style="display:flex;justify-content:space-between;">
                    <strong><?php echo h($c['full_name']); ?></strong>
                    <span class="pill pill-pending">Pending</span>
                </div>
                <p style="font-size:13px;color:var(--text-muted);margin:4px 0;"><?php echo h($c['event_type']); ?></p>
                <div style="font-size:18px;font-weight:700;color:var(--navy);margin-bottom:8px;"><?php echo rand_format($c['amount']); ?></div>
                <a href="?approve_payment=<?php echo (int)$c['payment_id']; ?>" class="btn btn-success btn-sm">Approve</a>
                <a href="?reject_payment=<?php echo (int)$c['payment_id']; ?>" class="btn btn-danger btn-sm">Reject</a>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
