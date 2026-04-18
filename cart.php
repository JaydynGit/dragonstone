<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Start the session if one hasn't been initialized.
if (session_status() === PHP_SESSION_NONE) session_start();

// Initialize the cart item ID list in the session if it doesn't exist.
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
// Initialize the cart quantities array in the session if it doesn't exist.
if (!isset($_SESSION['cart_quantities'])) $_SESSION['cart_quantities'] = [];

// Get the list of unique product IDs currently in the cart.
$cartItems = $_SESSION['cart'];
// Initialize the total price variable.
$total = 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart | DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* Global Styles */
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            /* Use a dark blue-grey background color. */
            background-color: #2E3A4C;
            /* Set the default text color to white. */
            color: #FFFFFF;
        }

        /* Header Top Navigation Bar */
        header {
            background-color: #1DB959; /* Primary Green color */
            height: 180px;
            display: flex;
            align-items: center;
            padding: 0 40px;
            color: white; 
        }

        header img {
            width: 80px;
            margin-right: 20px;
            cursor: pointer;
        }

        header h1 {
            font-size: 60px;
            font-weight: 600;
            margin: 0;
        }

        /* Cart Content Area */
        .cart-container {
            padding: 40px;
            /* Extra padding at the bottom to accommodate the fixed checkout bar */
            margin-bottom: 180px;
        }

        /* Styles for a single product entry in the cart */
        .cart-card {
            display: flex;
            align-items: center;
            border: 3px solid #1DB959;
            border-radius: 40px;
            margin-bottom: 25px;
            padding: 30px;
            /* Cart card background color */
            background-color: #2E3A4C; 
        }

        .cart-card img {
            width: 340px;
            height: 340px;
            border-radius: 30px;
            /* Ensure the image covers the area */
            object-fit: cover;
        }

        .cart-info {
            flex: 1;
            padding-left: 30px;
        }

        .cart-name {
            font-weight: 700;
            font-size: 42px;
            /* Brand highlight color */
            color: #1DB959; 
            margin-bottom: 10px;
        }

        .cart-price {
            font-weight: 700;
            font-size: 40px;
            color: #FFFFFF;
            margin-bottom: 20px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 40px;
        }

        .quantity-control img {
            width: 60px;
            height: 60px;
            cursor: pointer;
        }

        .quantity-number {
            font-size: 40px;
            font-weight: 500;
            /* Text color inherited from body */
        }

        /* Floating Checkout Bar Action Button */
        .checkout-bar {
            background-color: #1DB959; 
            position: fixed;
            bottom: 20px;
            /* Center the bar horizontally */
            left: 50%;
            transform: translateX(-50%);
            border-radius: 50px;
            width: 90%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            color: #FFFFFF; 
            font-size: 45px;
            font-weight: 500;
            cursor: pointer;
            /* Ensure it is visible above other content */
            z-index: 1000;
        }

        .checkout-bar img {
            width: 80px;
            height: 80px;
        }

        /* Message shown when cart is empty */
        .empty {
            text-align: center;
            /* Light grey for low contrast text */
            color: #A3ACB5; 
            font-size: 40px;
            margin-top: 150px;
        }
</style>

<script>
/**
 * Updates the quantity of a product in the cart via an AJAX request.
 * @param {string} productId - The ID of the product to modify.
 * @param {string} action - 'add' or 'remove' quantity.
 */
function updateQuantity(productId, action) {
    // Send an asynchronous request to the server to modify the cart.
    fetch('update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        // Pass the product ID and the action requested.
        body: 'product_id=' + productId + '&action=' + action
    })
    .then(res => res.json())
    .then(data => {
        // Update the visible quantity number on the cart card.
        if (data.quantities && data.quantities[productId]) {
            document.getElementById('qty-' + productId).textContent = data.quantities[productId];
        } else {
            // If the quantity is zero, remove the entire cart card from the display.
            document.getElementById('card-' + productId)?.remove();
        }

        // Update the total price displayed in the checkout bar.
        if (data.checkoutMessage) {
            document.getElementById('checkout-text').textContent = data.checkoutMessage;
        }

        // Check if the cart is now completely empty.
        if (Object.keys(data.quantities).length === 0) {
            // Replace the cart contents with an empty message.
            document.querySelector('.cart-container').innerHTML = "<p class='empty'>Your cart is empty.</p>";
        }
    });
}
</script>
</head>

<body>
<header>
    <a href="index.php"><img src="assets/icons/back_icon.png" alt="Back"></a>
    <h1>Your Cart</h1>
</header>

<div class="cart-container">
<?php
// Check if the cart ID list is empty.
if (empty($cartItems)) {
    // Display the empty cart message.
    echo "<p class='empty'>Your cart is empty.</p>";
} else {
    // Prepare a comma separated list of product IDs for the SQL IN clause.
    $ids = "'" . implode("','", $cartItems) . "'";
    // Query the database to get details for all products currently in the cart.
    $result = $conn->query("SELECT product_id, product_name, product_price, image_url FROM products WHERE product_id IN ($ids)");
    
    // Loop through each product retrieved from the database.
    while ($row = $result->fetch_assoc()) {
        // Get the quantity of this specific product from the session, defaulting to 1.
        $qty = $_SESSION['cart_quantities'][$row['product_id']] ?? 1;
        // Calculate the subtotal for this item and add it to the running grand total.
        $total += $qty * $row['product_price'];
        
        // Output the HTML structure for a single cart product card.
        echo "
        <div class='cart-card' id='card-{$row['product_id']}'>
            <img src='{$row['image_url']}' alt='{$row['product_name']}'>
            <div class='cart-info'>
                <div class='cart-name'>{$row['product_name']}</div>
                <div class='cart-price'>R{$row['product_price']}</div>
                <div class='quantity-control'>
                    <img src='assets/icons/remove_icon.png' onclick=\"updateQuantity('{$row['product_id']}', 'remove')\" alt='Remove'>
                    <span id='qty-{$row['product_id']}' class='quantity-number'>{$qty}</span>
                    <img src='assets/icons/add_icon.png' onclick=\"updateQuantity('{$row['product_id']}', 'add')\" alt='Add'>
                </div>
            </div>
        </div>";
    }
}
?>
</div>

<div class="checkout-bar" onclick="window.location.href='checkout.php'">
    <img src="assets/icons/Cart_icon.png" alt="Cart Icon">
    <span id="checkout-text">
        <?php 
        // Display the total price or an "empty" message.
        echo $total > 0 ? "Checkout: R " . number_format($total, 2) : "Cart empty"; 
        ?>
    </span>
</div>
</body>
</html>