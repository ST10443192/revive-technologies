<?php
/**
 * admin/calendar.php
 * Simple month-view calendar built from the bookings table,
 * so admin can see which dates already have events booked
 * (helps prevent double-booking).
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');

$first_day    = mktime(0, 0, 0, $month, 1, $year);
$days_in_month = (int)date('t', $first_day);
$start_weekday = (int)date('w', $first_day); // 0 = Sunday

$stmt = $pdo->prepare(
    "SELECT b.*, u.full_name AS client_name FROM bookings b JOIN users u ON b.client_id = u.user_id
     WHERE MONTH(b.event_date) = ? AND YEAR(b.event_date) = ? AND b.status IN ('confirmed','quoted','completed')"
);
$stmt->execute([$month, $year]);
$events_by_day = [];
foreach ($stmt->fetchAll() as $b) {
    $day = (int)date('j', strtotime($b['event_date']));
    $events_by_day[$day][] = $b;
}

$stmt = $pdo->prepare(
    "SELECT b.*, u.full_name AS client_name FROM bookings b JOIN users u ON b.client_id = u.user_id
     WHERE b.event_date >= CURDATE() AND b.status IN ('confirmed','quoted')
     ORDER BY b.event_date ASC LIMIT 6"
);
$stmt->execute();
$upcoming = $stmt->fetchAll();

$prev_month = $month - 1; $prev_year = $year; if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
$next_month = $month + 1; $next_year = $year; if ($next_month > 12) { $next_month = 1; $next_year++; }

$page_title = 'Calendar';
$dash_role  = 'admin';
$active     = 'calendar';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div><h2>Event Calendar</h2><p>Manage bookings and prevent scheduling conflicts</p></div>
</div>

<div class="two-col">
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3><?php echo date('F Y', $first_day); ?></h3>
            <div>
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="btn btn-outline btn-sm">&larr;</a>
                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="btn btn-outline btn-sm">&rarr;</a>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;text-align:center;font-size:12px;color:var(--text-muted);font-weight:700;margin-bottom:6px;">
            <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
        </div>
        <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:6px;">
            <?php for ($i = 0; $i < $start_weekday; $i++): ?>
                <div></div>
            <?php endfor; ?>
            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                <div style="border:1px solid var(--border);border-radius:8px;padding:6px;min-height:60px;font-size:12px;">
                    <div style="font-weight:700;"><?php echo $day; ?></div>
                    <?php if (!empty($events_by_day[$day])): foreach ($events_by_day[$day] as $ev): ?>
                        <div style="background:var(--success-bg);color:var(--success);border-radius:4px;padding:2px 4px;margin-top:3px;font-size:10px;">
                            <?php echo h(mb_strimwidth($ev['client_name'], 0, 12, '…')); ?>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="card">
        <h3>Upcoming Events</h3>
        <?php if (empty($upcoming)): ?>
            <p class="empty-state">No upcoming events.</p>
        <?php else: foreach ($upcoming as $u): ?>
            <div class="list-item">
                <div>
                    <div style="font-size:20px;font-weight:700;color:var(--navy);"><?php echo date('d', strtotime($u['event_date'])); ?></div>
                    <strong><?php echo h($u['event_type']); ?> - <?php echo h($u['client_name']); ?></strong><br>
                    <span style="font-size:12px;color:var(--text-muted);"><?php echo h($u['venue'] ?: 'Venue TBC'); ?></span>
                </div>
                <span class="pill pill-<?php echo h($u['status']); ?>"><?php echo ucfirst(h($u['status'])); ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
