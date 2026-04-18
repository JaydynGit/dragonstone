<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Start the session if one hasn't already been started.
if (session_status() === PHP_SESSION_NONE) session_start();

// Initialize the cart item ID list and quantities array in the session if they don't exist.
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if (!isset($_SESSION['cart_quantities'])) $_SESSION['cart_quantities'] = [];

// Safely retrieve the product ID and the desired action from the AJAX POST request.
$productId = $_POST['product_id'] ?? null;
$action = $_POST['action'] ?? null;

// Validate that both a product ID and an action were received.
if (!$productId || !$action) {
    // Return an error message as JSON and stop execution.
    echo json_encode(["error" => "Invalid request"]);
    exit;
}

// Check for a specific request to only return the current cart status.
if ($action === "status") {
    // Get the list of unique product IDs currently in the cart.
    $cart_ids = $_SESSION['cart'];
    // Count the number of unique items.
    $count = count($_SESSION['cart']);
    // Prepare the message for the floating cart bar.
    $cartCountMessage = $count > 0 ? "($count) items in Cart" : "Cart is empty";
    // Return the current cart data to the client and stop execution.
    echo json_encode([
        "cartCountMessage" => $cartCountMessage,
        "cart_ids" => $cart_ids
    ]);
    exit;
}

// Process the requested action (add or remove) on the cart.
switch ($action) {
    case "add":
        // If the product ID is not already in the main cart list, add it.
        if (!in_array($productId, $_SESSION['cart'])) $_SESSION['cart'][] = $productId;
        // Increase the quantity of the specific product by one.
        $_SESSION['cart_quantities'][$productId] = ($_SESSION['cart_quantities'][$productId] ?? 0) + 1;
        break;
    case "remove":
        // Check if the product exists in the quantity tracking array.
        if (isset($_SESSION['cart_quantities'][$productId])) {
            // Decrease the quantity by one.
            $_SESSION['cart_quantities'][$productId]--;
            // If the quantity drops to zero or less, remove the product entirely.
            if ($_SESSION['cart_quantities'][$productId] <= 0) {
                // Delete the quantity entry.
                unset($_SESSION['cart_quantities'][$productId]);
                // Remove the product ID from the main cart list.
                $_SESSION['cart'] = array_values(array_diff($_SESSION['cart'], [$productId]));
            }
        }
        break;
    case "set":
        // Set the quantity directly (used on the cart page for direct input).
        $qty = intval($_POST['quantity'] ?? 0);
        if ($qty > 0) {
            // Update the quantity directly.
            $_SESSION['cart_quantities'][$productId] = $qty;
            // Ensure the product is in the main list.
            if (!in_array($productId, $_SESSION['cart'])) $_SESSION['cart'][] = $productId;
        } else {
            // If the quantity is zero, remove the product completely.
            unset($_SESSION['cart_quantities'][$productId]);
            $_SESSION['cart'] = array_values(array_diff($_SESSION['cart'], [$productId]));
        }
        break;
}

// --- Recalculate Totals ---
$total = 0;
$totalItems = 0;

// Only proceed if there are any products remaining in the cart.
if (!empty($_SESSION['cart_quantities'])) {
    // Prepare a safe string list of all product IDs to query the database.
    $ids = "'" . implode("','", $_SESSION['cart']) . "'";
    // Query the database to get the latest prices for all items in the cart.
    $result = $conn->query("SELECT product_id, product_price FROM products WHERE product_id IN ($ids)");
    
    // Loop through the results to calculate the grand total.
    while ($row = $result->fetch_assoc()) {
        // Get the quantity for the current product.
        $qty = $_SESSION['cart_quantities'][$row['product_id']] ?? 0;
        // Add the cost of this product (price * quantity) to the total.
        $total += $qty * $row['product_price'];
        // Sum up the total number of items.
        $totalItems += $qty;
    }
}

// Prepare the updated message strings to send back to the client.
$checkoutMessage = $total > 0 ? "Checkout: R " . number_format($total, 2) : "Cart empty";
$cartCountMessage = $totalItems > 0 ? "($totalItems) items in Cart" : "Cart is empty";

// Return all the updated cart information as a JSON object.
echo json_encode([
    "checkoutMessage" => $checkoutMessage,
    "cartCountMessage" => $cartCountMessage,
    "quantities" => $_SESSION['cart_quantities'],
    "count" => $totalItems
]);
?>