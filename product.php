<?php
include 'session_check.php';
// Database connection
$conn = new mysqli("localhost", "root", "", "addproduct"); 

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Kunin ang category mula sa URL default ay 'all'
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sql = "SELECT * FROM products WHERE stock > 0";
$result = $conn->query($sql);

// SQL query depende sa category
if ($category === 'all') {
    $sql = "SELECT * FROM products";
} else {
    $sql = "SELECT * FROM products WHERE category = '" . $conn->real_escape_string($category) . "'";
}

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Saplot de Manila - Products</title>
  <link rel="stylesheet" href="product.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <header class="navbar">
    <div class="logo">
      <img src="assets/Media (2) 1.png">
    </div>
    <nav>
      <ul class="nav-links">
        <li><a href="index.html">Home</a></li>
        <li><a href="product.php?category=all">Shop</a></li>
        <li><a href="index.html">About us</a></li>
        <li><a href="customer service.html">Customer service</a></li>
      </ul>
    </nav>
    <div class="nav-icons">
      <img src="assets/shopping-cart 1.png" alt="Cart" id="cartBtn">
      <a href="login.php" class="logout">Log out</a>
    </div>
  </header>

  <!----------------- Breadcrumb -->
  <div class="breadcrumb">
    <p>Home &gt; <?php echo ucfirst($category); ?> Products</p>
  </div>

  <main class="container">
    <!----------Sidebar -->
    <aside class="sidebar">
      <h3>Browse By</h3>
      <ul>
        <li><a href="product.php?category=running">Running Shoes</a></li>
        <li><a href="product.php?category=basketball">Basketball Shoes</a></li>
        <li><a href="product.php?category=style">Style Shoes</a></li>
        <li><a href="product.php?category=all">All Products</a></li>
      </ul>

      <h3>Filter By</h3>
      <label for="price">Price</label>
      <div class="price-slider">
        <input type="range" min="1000" max="3000" value="3000" id="price">
        <div class="price-labels">
          <span>₱1,000</span>
          <span>₱3,000</span>
        </div>
      </div>
    </aside>

    <!----------------- Products Section -->
    <section class="products">
      <h2><?php echo ucfirst($category); ?> Products</h2>
      <div class="product-grid">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '
                <div class="product-card">
                    <img src="' . $row["image"] . '" alt="Shoe Image">
                    <h4>' . $row["name"] . '</h4>
                    <p>₱' . number_format($row["price"], 2) . '</p>
                    <div class="stars">' . 
                        str_repeat("★", $row["rating"]) . 
                        str_repeat("☆", 5 - $row["rating"]) . 
                    '</div>
                    <p class="' . ($row["stock"] <= 5 ? 'low-stock' : 'normal-stock') . '">
                        <strong>Stock:</strong> ' . $row["stock"] . '
                    </p>
                    <div class="buttons">
                        <button class="view" onclick="viewProduct(\'' . addslashes($row["name"]) . '\',' . $row["price"] . ',\'' . $row["image"] . '\',' . $row["rating"] . ')">View Product</button>
                    </div>
                </div>
                ';
            }
        } else {
            echo "<p>No products found.</p>";
        }
        ?>
      </div>
    </section>
  </main>

  <!------------ CART SIDEBAR --------->
  <div class="cart-sidebar" id="cartSidebar">
    <div class="cart-header">
      <h3>Cart</h3>
      <button id="closeCart"><img src="assets/close.png"></button>
    </div>
    <div id="cartItems"></div>
    <div class="cart-footer">
      <p>Total: ₱<span id="cartTotal">0.00</span></p>
      <button class="checkout-btn" onclick="goTocart()">Checkout</button>
      <button class="clear-cart-btn" id="clearCart">Clear Cart</button>
    </div>
  </div>

  <script>
    function goTocart() {
      window.location.href = "cart.php";
    }

    function viewProduct(name, price, image, rating) {
      const productData = { name, price, image, rating };
      localStorage.setItem('selectedProduct', JSON.stringify(productData));
      window.location.href = 'quantity.html';
    }
  </script>
  <script src="product.js"></script>
</body>
</html>

<?php $conn->close(); ?>