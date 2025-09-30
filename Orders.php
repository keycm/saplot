<?php
$conn = new mysqli("localhost", "root", "", "addproduct");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM cart ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Orders Dashboard</title>
<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
<style>
  body { font-family: 'Poppins', sans-serif; margin:0; background:#f4f4f4; }
  .sidebar {
    width: 250px; background: #fff; padding: 20px; box-sizing: border-box;
    display: flex; flex-direction: column; align-items: center; border-right: 1px solid #e0e0e0;
  }
  .sidebar .logo { width: 120px; margin-bottom: 30px; filter: brightness(1.5); }
  .sidebar .box {
    padding: 12px 15px; margin-bottom: 15px; background: #f0f0f0; color: #333;
    border-radius: 12px; cursor: pointer; display: flex; align-items: center;
    gap: 12px; font-weight: 600; width: 100%; transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }
  .sidebar .box:hover { background: #e0e0e0; transform: translateY(-2px); }
  .sidebar .box img { width: 24px; height: 24px; }
  .sidebar .box.logout { margin-top: auto; background: #111; color: #fff; }
  .sidebar .box.logout:hover { background: #333; }
  .main-content { flex: 1; padding: 30px 40px; overflow-y: auto; }
  .container { display: flex; height: 100vh; }
  .order-container {
    background:white; padding: 70px; border-radius:8px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1); flex:1; overflow:auto;
  }
  .order-header { margin-bottom:20px; }
  .order-table { width:100%; border-collapse:collapse; }
  .order-table th, .order-table td {
    padding:12px; border-bottom:1px solid #ddd; text-align:left;
  }
  .order-table th { background:#f0f0f0; }
  .status-badge {
    padding:5px 10px; border-radius:5px; color:white;
    font-weight:bold; font-size:0.9em; display:inline-block;
  }
  .status-pending { background:#3b82f6; }
  .status-cancelled { background:#ef4444; }
  .status-completed { background:#10b981; }

  .status-select {
    padding: 6px 10px; border-radius: 6px; border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif; font-size: 0.9em; cursor: pointer;
  }
  .status-select:disabled { background: #e5e5e5; color: #888; cursor: not-allowed; }
  .status-select option[value="completed"] { background:#10b981; color:white; }
  .status-select option[value="cancel"] { background:#ef4444; color:white; }
  .status-select option[value="delete"] { background:#ef4444; color:white; }
</style>
<script>
function handleAction(selectElem, form, currentStatus) {
  const selected = selectElem.value;

  // Kung "delete", may confirmation popup
  if (selected === "delete") {
    if (confirm("Are you sure you want to delete this order?")) {
      form.submit();
    } else {
      selectElem.value = ""; // reset dropdown
    }
  }
  // Kung "cancel" at currentStatus ay Cancelled, dropdown stays enabled pero only delete allowed
  else if (selected === "cancel" && currentStatus === "Cancelled") {
    alert("This order is already cancelled.");
    selectElem.value = ""; 
  } 
  else {
    form.submit();
  }
}
</script>
</head>
<body>
<div class="container">
  <!-- Sidebar -->
  <aside class="sidebar">
    <img class="logo" src="assets/Media (2) 1.png" alt="Logo"
      onclick="window.location.href='Dashboard.php'" style="cursor:pointer;">

    <div class="box" onclick="window.location.href='Revenuepage.html'">
      <img src="assets/financial-statement.png" alt="">Revenue
    </div>
    <div class="box" onclick="window.location.href='Sales.html'">
      <img src="assets/sales (1).png" alt="">Sales
    </div>
    <div class="box" onclick="window.location.href='Orders.php'">
      <img src="assets/completed-task.png" alt="">Orders
    </div>
    <div class="box" onclick="window.location.href='add_stock.php'">Add Stock</div>
    <div class="box add-product" onclick="window.location.href='practiceaddproduct.php'">Add Product</div>
    <div class="box" onclick="window.location.href='recently_deleted.php'">Recently Deleted</div>
    <div class="box logout" onclick="window.location.href='login.php'">Log out</div>
  </aside>

  <div class="order-container">
    <div class="order-header">
      <h2>Orders Dashboard</h2>
    </div>

    <table class="order-table">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Full Name</th>
          <th>Contact</th>
          <th>Address</th>
          <th>Products</th>
          <th>Total</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()) : ?>
          <?php
            $cart_items = json_decode($row['cart'], true);
            $product_list = [];
            if ($cart_items && is_array($cart_items)) {
              foreach ($cart_items as $item) {
                $name = isset($item['name']) ? $item['name'] : '';
                $qty = isset($item['quantity']) ? intval($item['quantity']) : 1;
                $product_list[] = "$name x$qty";
              }
            }
            $products_display = implode(", ", $product_list);
            $status_class = isset($row['status']) ? strtolower($row['status']) : 'pending';
            $status_text = ucfirst($status_class);
          ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['contact']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($products_display) ?></td>
            <td>₱<?= number_format($row['total'], 2) ?></td>
            <td><span class="status-badge status-<?= $status_class ?>"><?= $status_text ?></span></td>
            <td>
              <form method="POST" action="update_order.php" class="action-form">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <select name="action" class="status-select"
                        onchange="handleAction(this, this.form, '<?= $row['status'] ?>')">
                  <option value="">-- Select Action --</option>
                  <?php if ($row['status'] !== 'Cancelled' && $row['status'] !== 'Completed') : ?>
                    <option value="completed">Completed</option>
                    <option value="cancel">Cancel</option>
                  <?php endif; ?>
                  <?php if ($row['status'] === 'Cancelled') : ?>
                    <option value="delete">Delete</option>
                  <?php endif; ?>
                  <?php if ($row['status'] === 'Completed') : ?>
                    <!-- Completed orders wala nang ibang option -->
                  <?php endif; ?>
                </select>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
