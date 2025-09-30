<?php
$conn = new mysqli("localhost", "root", "", "addproduct");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// ===== Get product list for dropdown =====
$products = $conn->query("SELECT id, name, stock FROM products ORDER BY name ASC");
if (!$products) {
    die("Query failed: " . $conn->error);
}

// ===== Add Stock Logic =====
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = intval($_POST['product_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);

    if ($product_id > 0 && $stock >= 1) {
        // Check current stock
        $check = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $check->bind_param("i", $product_id);
        $check->execute();
        $result = $check->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $currentStock = $row['stock'];
            $maxAddable = 20 - $currentStock;

            if ($maxAddable <= 0) {
                $message = "⚠️ This product already reached maximum stock of 20.";
            } elseif ($stock > $maxAddable) {
                $message = "⚠️ You can only add up to $maxAddable more units for this product.";
            } else {
                $newStock = $currentStock + $stock;
                $update = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
                $update->bind_param("ii", $newStock, $product_id);

                if ($update->execute()) {
                    $message = "✅ Stock updated successfully!";
                } else {
                    $message = "❌ Error updating stock: " . $conn->error;
                }
                $update->close();
            }
        } else {
            $message = "⚠️ Product not found! Use Add Product page to create new.";
        }

        $check->close();
    } else {
        $message = "⚠️ Please select a product and enter a valid stock quantity.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Stock - Saplot de Manila</title>
<style>
body, html {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: 'Helvetica', Arial, sans-serif;
    background: #f5f6fa;
}

.container {
    display: flex;
    height: 100vh;
}

/* --- Sidebar --- */
.sidebar {
    width: 250px;
    background: #fff; /* White sidebar */
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
    background: #f0f0f0; /* soft gray */
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
    margin-top: auto; /* push to bottom */
    background: #111; /* black background */
    color: #fff;      /* white text */
}
.sidebar .box.logout:hover {
    background: #333; /* darker gray on hover */
}
/* --- Main Content --- */
.main-content {
    flex: 1;
    display: flex;
    justify-content: center;  /* horizontal center */
    align-items: center;      /* vertical center */
}

form {
    width: 100%;
    max-width: 400px;
    padding: 30px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

form label {
    font-weight: 600;
    align-self: flex-start;
}

form select,
form input,
form button {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

form button {
    background: #28a745;
    color: #fff;
    border: none;
    cursor: pointer;
}

form button:hover {
    background: #218838;
}

.msg {
    text-align: center;
    font-weight: bold;
    margin-bottom: 15px;
}
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
        <div class="box logout" onclick="window.location.href='login.php'">Log out</div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
        <form method="POST">
            <h2 style="text-align:center;">Add Stock</h2>
            <?php if ($message): ?>
                <p class="msg"><?= $message ?></p>
            <?php endif; ?>

            <label>Select Product:</label>
            <select name="product_id" id="productSelect" required>
                <option value="">-- Choose Product --</option>
                <?php if ($products->num_rows > 0): ?>
                    <?php while ($p = $products->fetch_assoc()): ?>
                        <option value="<?= $p['id'] ?>" data-stock="<?= $p['stock'] ?>">
                            <?= htmlspecialchars($p['name']) ?> - Stock: <?= $p['stock'] ?>
                        </option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option value="">⚠️ No products found. Please add a product first.</option>
                <?php endif; ?>
            </select>

            <label>Add Stock Quantity:</label>
            <input type="number" name="stock" id="stockInput" min="1" max="20" required>

            <button type="submit">Update Stock</button>
        </form>
    </div>
</div>

<script>
const productSelect = document.getElementById('productSelect');
const stockInput = document.getElementById('stockInput');

// Update max stock and reset value when selecting a product
productSelect.addEventListener('change', () => {
    const selectedOption = productSelect.selectedOptions[0];
    const currentStock = parseInt(selectedOption.dataset.stock || 0);
    const maxAddable = 20 - currentStock;
    stockInput.max = maxAddable > 0 ? maxAddable : 0;
    stockInput.value = 1; // reset value to 1
});

// Force numbers only
stockInput.addEventListener('input', () => {
    stockInput.value = stockInput.value.replace(/[^0-9]/g, '');
});

// Optional: warn user if they enter more than allowed
stockInput.addEventListener('change', () => {
    let val = parseInt(stockInput.value) || 1;
    const maxVal = parseInt(stockInput.max) || 20;
    if (val < 1) val = 1; // still minimum 1
    // DO NOT auto-correct if val > maxVal, just keep it
    stockInput.value = val;
});
</script>


</body>
</html>
<?php $conn->close(); ?>
