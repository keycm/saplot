<?php
$conn = new mysqli("localhost", "root", "", "addproduct");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Kunin total revenue
$result = $conn->query("SELECT SUM(amount) as total_revenue FROM revenue");
$row = $result->fetch_assoc();
$total_revenue = $row['total_revenue'] ?? 0;

// Kunin lahat ng revenue records
$records = $conn->query("SELECT * FROM revenue ORDER BY date_created DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sales Dashboard</title>
  <link rel="stylesheet" href="revenue.css"/>
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
        <a href="Revenuepage.php">
          <div class="box">
            <img src="assets/financial-statement.png" alt="Revenue">Revenue
          </div>
        </a>
        <a href="sales.html">
          <div class="box">
            <img src="assets/sales (1).png" alt="Sales">Sales
          </div>
        </a>
        <a href="Orders.php">
          <div class="box">
            <img src="assets/completed-task.png" alt="Orders">Orders
          </div>
        </a>
        <a href="add_stock.php"><div class="box">Add Stock</div></a>
        <a href="practiceaddproduct.php"><div class="box">Add Product</div></a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="header">
        <a href="Dashboard.php">
          <img class="logo" src="assets/Media (2) 1.png" alt="logo">
        </a>   
      </header>

      <!-- Revenue Box -->
      <div class="revenue-box">
        <div class="revenue-header">
          <div>
            <h3>Sales</h3>
            <div class="revenue-amount">₱ <?= number_format($total_revenue, 2) ?></div>
            <div class="growth"></div>
          </div>
          <button onclick="window.location.href='sales.php'">View Report</button>
        </div>
        <p>Sales this month</p>
        <canvas id="revenueChart" height="100"></canvas>

        <div class="legend">
          <span><span class="dot blue-dot"></span> This Month</span>
          <span><span class="dot gray-dot"></span> Last Month</span>
        </div>
      </div>

    
  <!-- Chart -->
  <script>
  document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('revenueChart').getContext('2d');

    fetch('get_sales_data.php')
      .then(res => res.json())
      .then(data => {
        new Chart(ctx, {
          type: 'bar',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'Revenue',
              data: data.values,
              backgroundColor: '#556ee6'
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: true } },
            scales: { y: { beginAtZero: true } }
          }
        });
      });
  });
  </script>
</body>
</html>
<?php $conn->close(); ?>
