<?php
// Include the script for the database connection.
include 'db_connect.php';
// Start the session if it hasn't been started yet.
if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure the user is logged in before they can view order details.
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
}

// Get the user ID from the session.
$uid = $_SESSION['user_id'];
// Retrieve the order ID from the URL parameter and ensure it's an integer.
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
// Stop the script if no valid order ID was provided in the URL.
if ($order_id === 0) die("Invalid order ID.");

// --- Fetch Order Details ---
// Prepare a query to get the specific order details, but only if it belongs to the logged-in user.
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND users_id = ?");
$stmt->bind_param("is", $order_id, $uid);
$stmt->execute();
$res = $stmt->get_result();
// Stop the script if the order doesn't exist or doesn't belong to the user.
if ($res->num_rows == 0) die("Order not found or access denied.");
// Fetch the single order record.
$order = $res->fetch_assoc();
$stmt->close(); // Close the first statement.

// --- Fetch Order Items ---
// Prepare a query to retrieve all products associated with this order.
$itst = $conn->prepare("
    SELECT oi.*, p.product_name, p.image_url
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$itst->bind_param("i", $order_id);
$itst->execute();
// Fetch all the product items for the order.
$items = $itst->get_result()->fetch_all(MYSQLI_ASSOC);
$itst->close(); // Close the second statement.
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Order #<?= htmlspecialchars($order['order_ref']); ?> | DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* Basic page and font setup */
body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    /* Use a dark blue-grey background color. */
    background-color: #2E3A4C;
    color: white;
    /* Add padding for the fixed header. */
    padding-top: 220px;
}

/* Styling for the fixed header bar */
header {
    /* Use the brand green color. */
    background-color: #1DB959;
    height: 180px;
    display: flex;
    align-items: center;
    padding: 0 40px;
    position: fixed;
    top: 0;
    width: 100%;
    /* Ensure the header is the top-most element. */
    z-index: 1000;
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}
/* Style for the back button icon */
header img {
    height: 65px;
    margin-right: 25px;
    cursor: pointer;
}
/* Style for the header title */
header h1 {
    font-size: 38px;
    font-weight: 600;
}

/* Container for the main order content */
.container {
    /* Center the content and limit its width. */
    width: 90%;
    max-width: 1000px;
    margin: 0 auto 80px;
    /* Use a slightly lighter dark background for the container box. */
    background-color: #384558;
    /* Brand green border for emphasis. */
    border: 3px solid #1DB959;
    border-radius: 25px;
    padding: 40px;
    box-shadow: 0 6px 12px rgba(0,0,0,0.4);
}

/* General order information display */
.order-info {
    margin-bottom: 25px;
    font-size: 20px;
}
/* Styling for the order status pill/badge */
.status-badge {
    padding: 8px 18px;
    border-radius: 20px;
    font-weight: 700;
    color: white;
}
/* Specific colors for different order statuses */
.status-pending { background-color: #FFB84D; color: #2E3A4C; } /* Orange for pending */
.status-delivered { background-color: #1DB959; } /* Green for delivered */
.status-cancelled { background-color: #FF4747; } /* Red for cancelled */

/* Styling for an individual product item in the order */
.item {
    display: flex;
    align-items: center;
    gap: 20px;
    /* Use the main dark background color for item rows. */
    background-color: #2E3A4C;
    padding: 15px 20px;
    border-radius: 15px;
    margin-bottom: 15px;
    border: 1px solid #1DB959;
}
/* Item product image styling */
.item img {
    width: 100px;
    height: 100px;
    border-radius: 15px;
    object-fit: cover;
    background-color: #455366;
}
/* Container for the item's name, price, and quantity */
.item-details {
    flex: 1;
}
.item-details h3 {
    margin: 0;
    font-size: 22px;
    /* Use the brand green color for the product name. */
    color: #1DB959;
}
/* Style for price and quantity text */
.item-details .price,
.item-details .qty {
    font-size: 18px;
    /* Use a subtle gray color. */
    color: #A3ACB5;
    margin-top: 6px;
}
/* Style for the subtotal price of the item */
.item-subtotal {
    font-size: 20px;
    font-weight: 600;
    text-align: right;
}

/* Styling for the financial summary block */
.summary {
    /* Use the main dark background color for the summary. */
    background-color: #2E3A4C;
    border: 2px solid #1DB959;
    border-radius: 20px;
    padding: 25px 30px;
    margin-top: 30px;
}
.summary h2 {
    margin-top: 0;
    /* Use the brand green color for the summary title. */
    color: #1DB959;
    /* Add a green line separator below the title. */
    border-bottom: 2px solid #1DB959;
    padding-bottom: 10px;
    font-size: 26px;
}
/* Layout for each line item in the summary */
.summary-line {
    display: flex;
    justify-content: space-between;
    font-size: 20px;
    margin: 10px 0;
}
/* Highlighted style for the final total */
.summary-line strong {
    font-size: 22px;
}

/* Media queries for smaller screens (responsiveness) */
@media (max-width: 768px) {
    /* Stack item details vertically on small screens. */
    .item { flex-direction: column; align-items: flex-start; }
    .item-subtotal { text-align: left; margin-top: 10px; }
    .summary-line { font-size: 18px; }
    header h1 { font-size: 28px; }
}
</style>
</head>
<body>

<header>
    <img src="assets/icons/back_icon.png" alt="Back" onclick="window.location.href='customer_orders.php'">
    <h1>Order Details</h1>
</header>

<div class="container">
    <div class="order-info">
        <h2>Order #<?= htmlspecialchars($order['order_ref']); ?></h2>
        <p>Status: 
            <span class="status-badge status-<?= htmlspecialchars($order['status']); ?>">
                <?= ucfirst(htmlspecialchars($order['status'])); ?>
            </span>
        </p>
        <p>Placed: <?= date("d M Y H:i", strtotime($order['created_at'])); ?></p>
    </div>

    <h2 style="color:#1DB959;">Items</h2>
    <?php if (empty($items)): ?>
        <p>No items found for this order.</p>
    <?php else: ?>
        <?php foreach ($items as $it): ?>
            <div class="item">
                <img src="<?= htmlspecialchars($it['image_url'] ?: 'assets/images/default.png'); ?>" alt="<?= htmlspecialchars($it['product_name']); ?>">
                <div class="item-details">
                    <h3><?= htmlspecialchars($it['product_name']); ?></h3>
                    <div class="price">Price: R <?= number_format($it['price'], 2); ?></div>
                    <div class="qty">Quantity: <?= (int)$it['quantity']; ?></div>
                </div>
                <div class="item-subtotal">
                    Subtotal: R <?= number_format($it['subtotal'], 2); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div class="summary">
        <h2>Order Summary</h2>
        <div class="summary-line">
            <span>Subtotal</span>
            <span>R <?= number_format($order['subtotal'], 2); ?></span>
        </div>
        <div class="summary-line">
            <span>EcoPoints Used</span>
            <span><?= (int)$order['ecopoints_used']; ?> (R <?= number_format($order['ecopoints_value'], 2); ?>)</span>
        </div>
        <hr style="border: 1px dashed #1DB959;">
        <div class="summary-line">
            <strong>Total Paid</strong>
            <strong>R <?= number_format($order['total'], 2); ?></strong>
        </div>
    </div>
</div>

</body>
</html>