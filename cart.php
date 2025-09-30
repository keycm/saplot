<?php include 'session_check.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Checkout - Saplot de Manila</title>
  <link rel="stylesheet" href="cart.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <section class="cart-container">
    <h1><span class="back-arrow" onclick="back()"><img src="assets/back.png"></span> Checkout</h1>

    <div class="cart-content">
      <div class="cart-items" id="cartItemsContainer"></div>

      <div class="order-summary">
        <h2>Order Details</h2>
        <div class="summary-info" id="summaryInfo"></div>

        <div class="payment-method">
          <h3>Payment Method</h3>
          <label><input type="radio" name="payment" value="COD" checked> Cash On Delivery</label>
          <label><input type="radio" name="payment" value="GCash"> GCash</label>
        </div>

        <div class="total-section" id="totalSection"></div>

        <button class="checkout-btn" id="checkoutBtn">Checkout</button>
      </div>
    </div>
  </section>

  <!-- Delivery Modal -->
  <div class="modal" id="deliveryModal" style="display: none;">
    <div class="modal-content" style="max-width: 400px; background: #fff; padding: 20px; border-radius: 10px; position: relative;">
      <span onclick="closeModal()" style="position: absolute; top: 10px; right: 15px; font-size: 20px; cursor: pointer;">&times;</span>
      <h2 style="margin-top: 0;">Delivery Information</h2>
      <p style="color: red; font-weight: bold;">Note: Delivery is available for Pampanga only.</p>

      <form id="deliveryForm">
        <label for="fullname">Full Name</label>
        <input type="text" name="fullname" id="fullname" required style="width: 100%; padding: 8px; margin-bottom: 10px;" />

        <label for="contact">Contact Number</label>
        <input type="tel" name="contact" id="contact" required style="width: 100%; padding: 8px; margin-bottom: 10px;" />

        <label for="address">Address</label>
        <textarea name="address" id="address" rows="3" required style="width: 100%; padding: 8px; margin-bottom: 15px;"></textarea>

        <button type="submit" class="submit-order-btn" style="width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 5px;">Confirm Order</button>
      </form>
    </div>
  </div>

<script>
let cartItems = JSON.parse(localStorage.getItem("cart") || "[]");
const cartContainer = document.getElementById("cartItemsContainer");
const summaryInfo = document.getElementById("summaryInfo");
const totalSection = document.getElementById("totalSection");
const checkoutBtn = document.getElementById("checkoutBtn");

function back() {
  window.location.href = "product.php";
}
function closeModal() {
  document.getElementById("deliveryModal").style.display = "none";
}
function updateLocalStorage() {
  localStorage.setItem("cart", JSON.stringify(cartItems));
}
function formatPrice(value) {
  return "₱" + value.toLocaleString();
}

function showCart() {
  if (cartItems.length === 0) {
    cartContainer.innerHTML = "<p>No items in cart.</p>";
    summaryInfo.innerHTML = "";
    totalSection.innerHTML = "";
    checkoutBtn.disabled = true;
    checkoutBtn.style.opacity = "0.5";
    return 0;
  }

  let subtotal = 0;
  cartContainer.innerHTML = cartItems.map((item, index) => {
    const lineTotal = item.price * item.quantity;
    subtotal += lineTotal;
    return `
      <div class="cart-card" style="display:flex;align-items:center;gap:15px;margin-bottom:15px;border:1px solid #ddd;padding:10px;border-radius:8px;">
        <img src="${item.image || 'assets/no-image.png'}" alt="${item.name}" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
        <div style="flex:1;">
          <p><strong>${item.name}</strong></p>
          <p>${item.quantity} × ₱${item.price.toLocaleString()} = ₱${lineTotal.toLocaleString()}</p>
          <div class="qty-controls" style="display:flex;align-items:center;gap:10px;margin-top:5px;">
            <button onclick="updateQuantity(${index}, -1)" style="padding:3px 8px;">-</button>
            <span>${item.quantity}</span>
            <button onclick="updateQuantity(${index}, 1)" style="padding:3px 8px;">+</button>
          </div>
        </div>
      </div>
    `;
  }).join("");

  const shipping = 50;
  const discount = 20;
  const total = subtotal + shipping - discount;

  summaryInfo.innerHTML = `
    <p>Items Total <span>₱${subtotal.toLocaleString()}</span></p>
    <p>Shipping Fee <span>₱${shipping.toLocaleString()}</span></p>
    <p>Discount <span>-₱${discount.toLocaleString()}</span></p>
  `;
  totalSection.innerHTML = `<p>Total <span>₱${total.toLocaleString()}</span></p>`;
  checkoutBtn.disabled = false;
  checkoutBtn.style.opacity = "1";
  return total;
}

function updateQuantity(index, change) {
  let newQty = cartItems[index].quantity + change;
  if (newQty < 1) {
    cartItems.splice(index, 1);
  } else if (newQty > 10) {
    cartItems[index].quantity = 10;
    alert("Maximum of 10 items allowed per product.");
  } else {
    cartItems[index].quantity = newQty;
  }
  updateLocalStorage();
  finalTotal = showCart();
}

let finalTotal = showCart();

// Checkout button
checkoutBtn.addEventListener("click", () => {
  document.getElementById("deliveryModal").style.display = "block";
});

document.getElementById("deliveryForm").addEventListener("submit", async function(e) {
  e.preventDefault();
  const fullname = this.fullname.value;
  const contact = this.contact.value;
  const address = this.address.value;
  const payment = document.querySelector('input[name="payment"]:checked').value;

  try {
    const res = await fetch("save_order.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        fullname,
        contact,
        address,
        payment,
        cart: cartItems, // make sure each item has product id
        total: finalTotal
      })
    });
    const result = await res.json();
    if (result.success) {
      localStorage.removeItem("cart");
      alert("Order placed successfully!");
      window.location.href = "product.php";
    } else {
      alert("Error: " + result.error);
    }
  } catch (error) {
    console.error(error);
    alert("Failed to send order.");
  }
});

// GCash redirect
document.querySelector('input[value="GCash"]').addEventListener("change", function() {
  if (this.checked) {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) { alert("Cart is empty."); return; }
    const total = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "create_payment.php";
    const inputAmount = document.createElement("input");
    inputAmount.type = "hidden";
    inputAmount.name = "amount";
    inputAmount.value = total;
    form.appendChild(inputAmount);
    document.body.appendChild(form);
    form.submit();
  }
});
</script>
</body>
</html>
