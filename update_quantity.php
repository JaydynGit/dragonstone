<?php
// Include the script that handles the database connection.
include 'db_connect.php';

// Start the session if one hasn't been started already.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the product ID and the requested action from the POST request.
$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? null;

// Validate that both the product ID and the action were received.
if (!$productId || !$action) {
    // Return an error message as JSON and stop execution.
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

// Update the database stock level based on the requested cart action.
if ($action === "add") {
    // Decrease the stock quantity by one when a product is added to the cart,
    // only if the current stock is greater than zero.
    $conn->query("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_id = '$productId' AND stock_quantity > 0");
} elseif ($action === "remove") {
    // Increase the stock quantity by one when a product is removed from the cart,
    // effectively putting the item back into inventory.
    $conn->query("UPDATE products SET stock_quantity = stock_quantity + 1 WHERE product_id = '$productId'");
}
?>





