<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Start the session if one hasn't been initialized.
if (session_status() === PHP_SESSION_NONE) session_start();

// Initialize the cart ID list in the session if it doesn't exist.
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Check if a product ID was provided in the URL.
if (!isset($_GET['id'])) die("Product not found.");
$product_id = $_GET['id'];

// Prepare a query to fetch the specific product details by ID.
$stmt = $conn->prepare("SELECT product_id, product_name, product_price, product_emissions, product_description, image_url FROM products WHERE product_id = ?");
$stmt->bind_param("s", $product_id);
$stmt->execute();
// Fetch the product data.
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Stop the script if no product was found with that ID.
if (!$product) die("Product not found.");

// Check if the current product is already in the user's shopping cart.
$inCart = in_array($product_id, $_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['product_name']); ?> - DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            /* Use a dark blue-grey background color. */
            background-color: #2E3A4C;
        }

        /* HEADER */
        header {
            background-color: #1DB959;
            color: white;
            height: 160px;
            display: flex;
            align-items: center;
            padding: 0 40px;
            position: fixed;
            top: 0;
            width: 100%;
            /* Ensure header is on top */
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        header img {
            width: 65px;
            margin-right: 25px;
            cursor: pointer;
        }
        header h1 {
            font-size: 48px;
            font-weight: 600;
        }

        /* PRODUCT SECTION */
        /* Container for all the product information */
        .product-section {
            /* Padding to clear the fixed header */
            margin-top: 200px;
            padding: 40px;
            display: flex;
            flex-direction: column;
            align-items: Right;
        }
        /* Container for the main product image */
        .product-image-container {
            width: 90%%;
            max-width: 1000px;
            /* Maintain a 1:1 aspect ratio for the image area */
            aspect-ratio: 1 / 1;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        /* Style for the product image itself */
        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        /* Style for the product name */
        .product-title {
            font-size: 65px;
            font-weight: 700;
            /* Brand green color */
            color: #1DB959;
            text-align: Left;
            margin: 20px 0 10px;
        }
        /* Style for the product price */
        .product-price {
            font-size: 70px;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 10px;
        }
        /* Style for the carbon emission display */
        .product-emission {
            font-size: 30px;
            /* Light green color */
            color: #60CE8A;
            font-weight: 600;
            margin-bottom: 30px;
            margin-top: 15px;
        }
        /* Style for the product description box */
        .product-description {
            width: 90%;
            max-width: 900px;
            /* Dark green background for contrast */
            background-color: #184A35;
            border: 2px solid #1DB959;
            border-radius: 25px;
            padding: 30px;
            font-size: 40px;
            /* Light grey text for readability */
            color: #D0D0D0;
            line-height: 1.6;
            box-shadow: 0 6px 16px rgba(0,0,0,0.05);
        }


        /* FLOATING CART BUTTON */
        .floating-cart {
            position: fixed;
            bottom: 25px;
            left: 50%;
            transform: translateX(-50%);
            width: 90%;
            background-color: #1DB959;
            color: #fff;
            border-radius: 60px;
            height: 125px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            font-size: 40px;
            font-weight: 600;
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
            cursor: pointer;
            /* Ensure the button is clickable above other elements */
            z-index: 20;
            transition: background-color 0.3s;
        }
        .floating-cart:hover {
            background-color: #18A14D;
        }
        .floating-cart img {
            width: 80px;
            height: 80px;
        }

        /* TOAST MESSAGE */
        /* Hidden floating message for user feedback */
        #toast {
            visibility: hidden;
            position: fixed;
            bottom: 150px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0,0,0,0.75);
            color: #fff;
            border-radius: 20px;
            padding: 20px 40px;
            font-size: 28px;
            font-weight: 500;
            opacity: 0;
            /* Smooth animation for showing and hiding */
            transition: opacity 0.5s ease, bottom 0.5s ease;
        }
        /* Class to show the toast message */
        #toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 180px;
        }
</style>

<script>
/**
 * Toggles the product's presence in the shopping cart using an AJAX request.
 * Updates the button text, icon, and shows a toast message.
 * @param {string} productId - The ID of the product.
 */
function toggleCart(productId) {
    const button = document.querySelector('.floating-cart span');
    const icon = document.querySelector('.floating-cart img');
    // Determine the action based on the current button text.
    const currentAction = button.textContent.includes('Remove') ? 'remove' : 'add';

    // Send an asynchronous request to update the cart session.
    fetch('update_cart.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId + '&action=' + currentAction
    })
    .then(r => r.json())
    .then(data => {
        // Update button text and icon based on the action performed.
        if (currentAction === 'add') {
            button.textContent = 'Remove from Cart';
            icon.src = 'assets/icons/removeCart_icon_alt.png';
            showToast('Added to Cart');
        } else {
            button.textContent = 'Add to Cart';
            icon.src = 'assets/icons/addCart_icon.png';
            showToast('Removed from Cart');
        }

        // Set a session flag to force the index page to refresh the cart status when the user navigates back.
        sessionStorage.setItem('cartUpdated', 'true');
    })
    .catch(() => showToast('Error processing cart request'));
}

/**
 * Shows the temporary floating toast message.
 * @param {string} message - The text to display in the toast.
 */
function showToast(message) {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.classList.add('show');
    // Hide the toast message after a short duration.
    setTimeout(() => toast.classList.remove('show'), 1800);
}
</script>

</head>

<body>
<header>
    <img src="assets/icons/back_icon.png" alt="Back" onclick="history.back()">
    <h1>Product</h1>
</header>

<div class="product-section">
    <div class="product-image-container">
        <img src="<?= $product['image_url']; ?>" class="product-image" alt="<?= htmlspecialchars($product['product_name']); ?>">
    </div>

    <div class="product-title"><?= htmlspecialchars($product['product_name']); ?></div>
    <div class="product-price">R<?= number_format($product['product_price'], 2); ?></div>
    <div class="product-description"><?= nl2br(htmlspecialchars($product['product_description'])); ?></div>
    <div class="product-emission">CO₂ Emission: <?= $product['product_emissions']; ?> kg</div>
</div>

<div class="floating-cart" onclick="toggleCart('<?= $product_id; ?>')">
    <img src="<?= $inCart ? 'assets/icons/removeCart_icon_alt.png' : 'assets/icons/addCart_icon.png'; ?>" alt="Cart Icon">
    <span><?= $inCart ? 'Remove from Cart' : 'Add to Cart'; ?></span>
</div>

<div id="toast"></div>


</body>
</html>