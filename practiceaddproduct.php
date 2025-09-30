<?php
$conn = new mysqli("localhost", "root", "", "addproduct");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category']; 

    // 🚫 Block kung 0 o negative ang stock
   if ($name === '') {
        $errorMessage = " Product name is required.";
    } elseif ($price <= 0) {
        $errorMessage = " Price must be greater than 0.";
    } elseif ($stock <= 0) {
        $errorMessage = " Stock must be greater than 0.";
    } elseif ($category === '') {
        $errorMessage = " Category is required.";
     } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $imageName = basename($_FILES["image"]["name"]);
            $targetDir = "uploads/";
            $targetFile = $targetDir . uniqid() . "_" . $imageName;
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg','jpeg','png','gif'];

            if (in_array($imageFileType, $allowedTypes)) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
                    $stmt = $conn->prepare("INSERT INTO products (name, price, stock, image, category) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sdiss", $name, $price, $stock, $targetFile, $category);

                    if ($stmt->execute()) {
                        $successMessage = "✅ Product added successfully!";
                    } else {
                        $errorMessage = "❌ Error inserting product: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $errorMessage = "❌ Failed to upload image.";
                }
            } else {
                $errorMessage = "❌ Invalid image format. Allowed: jpg, jpeg, png, gif.";
            }
        } else {
            $errorMessage = "❌ Please upload a valid image.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="home.css"/>
<style>
body, html {
    margin: 0;
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
    padding: 30px 40px;
    overflow-y: auto;
}

.message {
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
    font-size: 18px;
}
.message.success { color: green; }
.message.error { color: red; }

form {
    max-width: 500px;
    margin: 0 auto;
    padding: 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

form label {
    font-weight: bold;
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
}

form input, form select, form button {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
}

form input:focus, form select:focus {
    border-color: #ff4d6d;
    box-shadow: 0 0 5px rgba(255,77,109,0.3);
}

form button {
    background: linear-gradient(135deg, #ff6b81, #ff4757);
    color: #fff;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255,77,109,0.3);
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
    <main class="main-content">
        <?php if($successMessage): ?>
            <div class="message success"><?= $successMessage ?></div>
        <?php elseif($errorMessage): ?>
            <div class="message error"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <label>Product Name:</label>
            <input type="text" name="name" id="product_name" required>

            <label>Price:</label>
            <input type="number" name="price" step="1.00" required>

            <label>Stock:</label>
            <input type="number" name="stock" id="stock" required>

            <label>Category:</label>
            <select name="category" required>
                <option value="">-- Select Category --</option>
                <option value="running">Running</option>
                <option value="basketball">Basketball</option>
                <option value="style">Styleshoes</option>
            </select>

            <label>Product Image:</label>
            <input type="file" name="image" accept="image/*" required>

            <button type="submit">Add Product</button>
        </form>
    </main>
</div>

<script>
const productNameInput = document.getElementById("product_name");
const stockInput = document.getElementById("stock");
const priceInput = document.querySelector("input[name='price']");

// Product Name: letters, numbers, spaces only
productNameInput.addEventListener("input", () => {
    productNameInput.value = productNameInput.value.replace(/[^A-Za-z0-9 ]/g, "");
});

// Stock: numbers only
stockInput.addEventListener("input", () => {
    stockInput.value = stockInput.value.replace(/[^0-9]/g, ""); // bawal letters or symbols

    if (stockInput.value !== "") {
        let value = parseInt(stockInput.value);

        if (value < 1) {
            stockInput.value = "";
            alert("Stock must be at least 1 (0 is not allowed).");
        } else if (value > 20) {
            stockInput.value = 20; // auto set to 20 kung sobra
            alert("Stock cannot exceed 20.");
        }
    }
});

// Price: numbers only with decimal
priceInput.addEventListener("input", () => {
    priceInput.value = priceInput.value.replace(/[^0-9.]/g, "");
    if (priceInput.value !== "" && parseFloat(priceInput.value) <= 0) {
        priceInput.value = "";
        alert(" Price must be greater than 0.");
    }
});
</script>
</body>
</html>