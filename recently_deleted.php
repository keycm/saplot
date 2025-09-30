<?php
$conn = new mysqli("localhost", "root", "", "addproduct");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
$sql = "SELECT * FROM recently_deleted ORDER BY deleted_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Recently Deleted Orders</title>
<link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
<style>
  body { font-family: 'Poppins', sans-serif; margin:0; background:#f4f4f4; }
  .container {
    display: flex;
    height: 100vh;
  }
  .sidebar {
    width: 250px;
    background: #fff;
    padding: 20px;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    align-items: center;
    border-right: 1px solid #e0e0e0;
  }
  .sidebar .logo {
    width: 120px;
    margin-bottom: 30px;
    filter: brightness(1.5);
  }
  .sidebar .box {
    padding: 12px 15px;
    margin-bottom: 15px;
    background: #f0f0f0;
    color: #333;
    border-radius: 12px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    width: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }
  .sidebar .box:hover {
    background: #e0e0e0;
    transform: translateY(-2px);
  }
  .sidebar .box img {
    width: 24px;
    height: 24px;
  }
  .sidebar .box.logout {
    margin-top: auto;
    background: #111;
    color: #fff;
  }
  .sidebar .box.logout:hover {
    background: #333;
  }

  .deleted-container {
    flex: 1;
    background:white;
    padding:30px;
    border-radius:8px;
    margin:20px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
    overflow-y: auto;
  }
  table { width:100%; border-collapse:collapse; margin-top:20px; }
  th, td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
  th { background:#f0f0f0; }
  .status-select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-family: 'Poppins', sans-serif;
    font-size: 0.9em;
    cursor: pointer;
  }
  .status-select option[value="restore"] { background:#10b981; color:white; }
  .status-select option[value="permanent"] { background:#ef4444; color:white; }
</style>
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

  <!-- Main Content -->
  <div class="deleted-container">
    <h2>Recently Deleted Orders</h2>
    <table>
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Full Name</th>
          <th>Contact</th>
          <th>Address</th>
          <th>Products</th>
          <th>Total</th>
          <th>Status</th>
          <th>Deleted At</th>
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
                $name = $item['name'] ?? '';
                $qty = intval($item['quantity'] ?? 1);
                $product_list[] = "$name x$qty";
              }
            }
            $products_display = implode(", ", $product_list);
          ?>
          <tr>
            <td><?= $row['order_id'] ?></td>
            <td><?= htmlspecialchars($row['fullname']) ?></td>
            <td><?= htmlspecialchars($row['contact']) ?></td>
            <td><?= htmlspecialchars($row['address']) ?></td>
            <td><?= htmlspecialchars($products_display) ?></td>
            <td>₱<?= number_format($row['total'], 2) ?></td>
            <td><?= ucfirst($row['status']) ?></td>
            <td><?= $row['deleted_at'] ?></td>
            <td>
              <form method="POST" action="restore_delete.php">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <select name="action" class="status-select" onchange="this.form.submit()" required>
                  <option value="">-- Select --</option>
                  <option value="restore">Restore</option>
                  <option value="permanent">Permanent Delete</option>
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
