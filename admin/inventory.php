<?php
/**
 * admin/inventory.php
 * Full CRUD for décor inventory items: add, edit stock, delete.
 */
require_once '../includes/functions.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_role('admin');

// ---- Add new item ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stmt = $pdo->prepare(
        'INSERT INTO inventory (item_name, category, total_stock, available_stock, unit_price) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        trim($_POST['item_name']),
        trim($_POST['category']),
        (int)$_POST['total_stock'],
        (int)$_POST['total_stock'],
        (float)$_POST['unit_price'],
    ]);
    set_flash('Item added to inventory.');
    redirect('inventory.php');
}

// ---- Restock (bump available_stock back to total_stock) ----
if (isset($_GET['restock'])) {
    $stmt = $pdo->prepare('UPDATE inventory SET available_stock = total_stock WHERE item_id = ?');
    $stmt->execute([(int)$_GET['restock']]);
    set_flash('Item restocked.');
    redirect('inventory.php');
}

// ---- Delete item ----
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare('DELETE FROM inventory WHERE item_id = ?');
    $stmt->execute([(int)$_GET['delete']]);
    set_flash('Item removed from inventory.');
    redirect('inventory.php');
}

$search = trim($_GET['q'] ?? '');
$sql = 'SELECT * FROM inventory';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE item_name LIKE ?';
    $params[] = "%{$search}%";
}
$sql .= ' ORDER BY item_name ASC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll();

$total_value    = $pdo->query('SELECT COALESCE(SUM(available_stock * unit_price),0) FROM inventory')->fetchColumn();
$total_items    = $pdo->query('SELECT COUNT(*) FROM inventory')->fetchColumn();
$low_stock      = $pdo->query('SELECT COUNT(*) FROM inventory WHERE available_stock < total_stock * 0.5 AND available_stock >= total_stock * 0.25')->fetchColumn();
$critical_stock = $pdo->query('SELECT COUNT(*) FROM inventory WHERE available_stock < total_stock * 0.25')->fetchColumn();

function stock_status($available, $total) {
    if ($total == 0) return ['label' => 'In Stock', 'class' => 'active'];
    $ratio = $available / $total;
    if ($ratio < 0.25) return ['label' => 'Critical', 'class' => 'rejected'];
    if ($ratio < 0.5)  return ['label' => 'Low Stock', 'class' => 'pending'];
    return ['label' => 'In Stock', 'class' => 'active'];
}

$page_title = 'Inventory';
$dash_role  = 'admin';
$active     = 'inventory';
$base       = '../';
include '../includes/header_dashboard.php';
?>

<div class="page-head">
    <div><h2>Inventory Management</h2><p>Track and manage all décor items and equipment</p></div>
</div>

<div class="grid grid-4" style="margin-bottom:28px;">
    <div class="card stat-card"><div class="num"><?php echo rand_format($total_value); ?></div><div class="label">Total Inventory Value</div></div>
    <div class="card stat-card"><div class="num"><?php echo (int)$total_items; ?></div><div class="label">Total Items</div></div>
    <div class="card stat-card"><div class="num" style="color:var(--warning);"><?php echo (int)$low_stock; ?></div><div class="label">Low Stock Alerts</div></div>
    <div class="card stat-card"><div class="num" style="color:var(--danger);"><?php echo (int)$critical_stock; ?></div><div class="label">Critical Stock</div></div>
</div>

<h3>Add New Item</h3>
<div class="card" style="max-width:640px;margin-bottom:28px;">
    <form method="POST" action="inventory.php">
        <input type="hidden" name="action" value="add">
        <div class="form-grid">
            <div class="field"><label>Item Name</label><input type="text" name="item_name" required></div>
            <div class="field"><label>Category</label><input type="text" name="category" placeholder="Furniture, Décor, Tableware, Linens" required></div>
        </div>
        <div class="form-grid">
            <div class="field"><label>Total Stock</label><input type="number" name="total_stock" required></div>
            <div class="field"><label>Unit Cost (R)</label><input type="number" step="0.01" name="unit_price" required></div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">+ Add Item</button>
    </form>
</div>

<form method="GET" action="inventory.php" class="search-box">
    <input type="text" name="q" placeholder="Search inventory items..." value="<?php echo h($search); ?>">
    <button type="submit" class="btn btn-outline">Search</button>
</form>

<div class="table-wrap">
<table class="data-table">
    <tr><th>Item</th><th>Category</th><th>Quantity</th><th>Min Stock</th><th>Unit Cost</th><th>Total Value</th><th>Status</th><th>Actions</th></tr>
    <?php if (empty($items)): ?>
        <tr><td colspan="8" class="empty-state">No inventory items found.</td></tr>
    <?php else: foreach ($items as $item):
        $status = stock_status($item['available_stock'], $item['total_stock']);
        $min_stock = (int)round($item['total_stock'] * 0.5);
    ?>
        <tr>
            <td><strong><?php echo h($item['item_name']); ?></strong><br><span style="font-size:12px;color:var(--text-muted);">INV-<?php echo str_pad($item['item_id'],3,'0',STR_PAD_LEFT); ?></span></td>
            <td><?php echo h($item['category']); ?></td>
            <td><?php echo (int)$item['available_stock']; ?></td>
            <td><?php echo $min_stock; ?></td>
            <td><?php echo rand_format($item['unit_price']); ?></td>
            <td><?php echo rand_format($item['available_stock'] * $item['unit_price']); ?></td>
            <td><span class="pill pill-<?php echo $status['class']; ?>"><?php echo $status['label']; ?></span></td>
            <td>
                <a href="?restock=<?php echo (int)$item['item_id']; ?>" class="btn btn-outline btn-sm">Restock</a>
                <a href="?delete=<?php echo (int)$item['item_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this item?');">Delete</a>
            </td>
        </tr>
    <?php endforeach; endif; ?>
</table>
</div>

<?php include '../includes/footer_dashboard.php'; ?>
