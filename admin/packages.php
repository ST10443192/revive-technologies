<?php
/**
 * admin/packages.php
 * -------------------------------------------------
 * Shows the sample packages that clients see on their Portfolio page.
 * NOTE: the packages are currently hard-coded (in client/dashboard.php's
 * PACKAGES JS object), not stored in the database - so this page is
 * read-only for now. To make these fully editable, we'd add a
 * `packages` table (name, price, description, image) and pull from it
 * on both this page and the client dashboard. Happy to build that
 * migration if you want real add/edit/delete here.
 * -------------------------------------------------
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

$page_title = 'Manage Packages';
$dash_role  = 'admin';
$active     = 'dashboard';
$base       = '../';
include '../includes/header_dashboard.php';

$packages = [
    ['title' => 'Floral Arch Wedding', 'tag' => 'Wedding', 'price' => 'From R 18 000', 'img' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600'],
    ['title' => 'Balloon Luxury Install', 'tag' => 'Party', 'price' => 'From R 12 000', 'img' => 'https://images.unsplash.com/photo-1530103862676-de8c9debad1d?w=600'],
    ['title' => 'Grand Ballroom Gala', 'tag' => 'Corporate', 'price' => 'From R 35 000', 'img' => 'https://images.unsplash.com/photo-1511578314322-379afb476865?w=600'],
];
?>

<div class="page-head">
    <div>
        <h2>Manage Packages</h2>
        <p>The sample packages currently shown to clients on their Portfolio page.</p>
    </div>
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Back to Dashboard</a>
</div>

<div class="alert alert-success">
    These packages are currently fixed in the code, not database-driven yet — this page is read-only for now. Let us know if you'd like full add/edit/delete support here.
</div>

<div class="grid grid-3">
    <?php foreach ($packages as $p): ?>
    <div class="card" style="padding:0;overflow:hidden;">
        <img src="<?php echo h($p['img']); ?>" style="width:100%;height:180px;object-fit:cover;">
        <div style="padding:18px;">
            <span class="pill pill-quoted"><?php echo h($p['tag']); ?></span>
            <h3 style="margin-top:12px;"><?php echo h($p['title']); ?></h3>
            <p style="color:var(--gold);font-weight:700;"><?php echo h($p['price']); ?></p>
            <button type="button" class="btn btn-outline btn-sm" disabled style="opacity:.5;cursor:not-allowed;">Edit (coming soon)</button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
