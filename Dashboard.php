<?php
include 'session_check.php';
$conn = new mysqli("localhost", "root", "", "addproduct");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Kunin total sales
$result = $conn->query("SELECT SUM(amount) as total_revenue FROM revenue");
$row = $result->fetch_assoc();
$total_revenue = $row['total_revenue'] ?? 0;

// Kunin total ang orders
$result = $conn->query("SELECT COUNT(*) as total_orders FROM cart");
$row = $result->fetch_assoc();

if (isset($row['total_orders'])) {
    $total_orders = $row['total_orders']; 
} else {
    $total_orders = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Saplot Inventory Dashboard</title>
  <link rel="stylesheet" href="home.css"/>
  <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <div class="container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <a href="#" id="toggleSidebar">
        <img src="assets/menu.png" alt="Menu">
      </a>
      <div class="sidebar-boxes">
        <a href="Sales.php">
          <div class="box">
            <img src="assets/financial-statement.png" alt="Revenue">Sales
          </div>
        </a>
        <a href="Sales.html">
          <div class="box">
            <img src="assets/sales (1).png" alt="Sales">Sales
          </div>
        </a>
        <a href="Orders.php">
          <div class="box">
            <img src="assets/completed-task.png" alt="Orders">Orders
          </div>
        </a>
         <a href="add_stock.php">
        <div class="box">Add Stock</div></a>
        
        <a href="practiceaddproduct.php">    
        <div class="box">Add Product</div></a>
   
        
      </div>      
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="header">
        <a href="Dashboard.php">
          <img class="logo" src="assets/Media (2) 1.png" alt="logo">
        </a>   
        <div class="user-info">
          <a href="logout.php" class="user-name">Log out</a>
        </div>
      </header>

      <!-- Dashboard Metrics -->
     <section class="dashboard-metrics">
  <!-- Revenue Metric -->
  <div class="metric">
    <p class="metric-title">Sales</p>
    <img src="assets/financial-statement.png" alt="Revenue" class="metric-icon">
    <p class="metric-value">
      ₱ <strong><?= number_format($total_revenue, 2) ?></strong>
    </p>
  </div>
       
<div class="metric">
  <p class="metric-title">Orders</p>
  <img src="assets/completed-task.png" alt="Orders" class="metric-icon">
  <p class="metric-value"><span class="plus-sign">+</span> <strong>
  <?php echo number_format($total_orders); ?></strong></p>
  </div>
        <div class="metric">
          <p class="metric-title">Profit</p>
          <img src="assets/profit.png" alt="Profit" class="metric-icon">
          <p class="metric-value"><span class="plus-sign">+</span> <strong>30,000</strong></p>
        </div>
      </section>  

      <div class="content-wrapper">
      <section class="stock-alert" id="stock-alert">
    <table>
        <caption>Stock Alert</caption>
        <thead>
            <tr>
                <th>Product No.</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Action</th> <!-- New column for delete button -->
            </tr>
        </thead>
        <tbody>
            <!-- Filled dynamically using get_stock.php -->
        </tbody>
    </table>
</section>

        <section class="top-products" id="top-products">
          <h2>Top Selling Products</h2>
          <canvas id="topProductsChart"></canvas>
        </section>

        <section class="top-product-table" id="top-product-table">
          <table>
            <caption>Top Selling Products</caption>
            <thead>
              <tr>
                
                <th>Name</th>
                <th>Price</th>
              </tr>
            </thead>
            <tbody>
              <!-- Filled dynamically using get_top_selling.php -->
            </tbody>
          </table>    
        </section>
      </div>
    </main>
  </div>

  <!-- Scripts -->
 <script>
document.addEventListener('DOMContentLoaded', () => {
  loadStockTable();
  loadTopProducts();

  // ===== STOCK ALERT =====
  function loadStockTable() {
    fetch('get_stock.php')
      .then(res => res.json())
      .then(data => {
        const tbody = document.querySelector('#stock-alert tbody');
        tbody.innerHTML = '';
        data.forEach(p => {
          tbody.innerHTML += `
            <tr>
              <td>${p.product_no}</td>
              <td>${p.name}</td>
              <td>₱${parseFloat(p.price).toFixed(2)}</td>
              <td>${p.quantity}</td>
              <td style="color: ${p.status === 'In Stock' ? 'green' : 'red'}">${p.status}</td>
              <td>
                <button class="delete-btn" data-id="${p.product_no}" 
                        style="background-color:red; color:white; border:none; padding:5px 10px; border-radius:5px; cursor:pointer;">
                  Delete
                </button>
              </td>
            </tr>
          `;
        });

        // Delete button event
        document.querySelectorAll('.delete-btn').forEach(btn => {
          btn.addEventListener('click', () => {
            const id = btn.getAttribute('data-id');
            if (confirm("Are you sure you want to delete this product?")) {
              fetch(`delete_product.php?id=${id}`)
                .then(res => res.json())
                .then(resp => {
                  if (resp.success) loadStockTable();
                  else alert('Failed to delete product');
                });
            }
          });
        });
      });
  }

  // ===== TOP PRODUCTS =====
  function loadTopProducts() {
    fetch('get_top_selling.php')
      .then(res => res.json())
      .then(response => {
        if (!response.success) {
          console.error("Error fetching top products:", response.error);
          return;
        }

        const data = response.data;

        // Fill table
        const tbody = document.querySelector('#top-product-table tbody');
        tbody.innerHTML = '';
        data.forEach(p => {
          tbody.innerHTML += `
            <tr>
              <td>${p.product_name}</td>
              <td>₱${parseFloat(p.price).toFixed(2)}<br> (${p.total_sold} sold)</td>
            </tr>
          `;
        });

        // Chart
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(ctx, {
          type: 'pie',
          data: {
            labels: data.map(p => p.product_name),
            datasets: [{
              data: data.map(p => p.total_sold),
              backgroundColor: [
                '#F94144', '#F3722C', '#F8961E',
                '#F9C74F', '#90BE6D', '#43AA8B',
                '#577590', '#277DA1'
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false
          }
        });
      });
  }
});

// ===== SIDEBAR TOGGLE =====
const toggleBtn = document.getElementById("toggleSidebar");
const sidebar = document.getElementById("sidebar");

toggleBtn.addEventListener("click", function(e) {
  e.preventDefault(); 
  sidebar.classList.toggle("expanded");
});
</script>
</body>
</html>