<?php
// PHP SCRIPT START

// Bring in the necessary script to handle the database connection.
include 'db_connect.php';

// Check if a session has already been started.
// If no session is active, start a new one to manage user state.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize the shopping cart in the session.
// This ensures the 'cart' variable is an empty array if it doesn't exist yet.
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// Get the category filter from the URL safely.
// This prevents potential injection attacks by sanitizing the user input.
$categoryFilter = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

// Clean up the category filter if one was found.
// Decode any HTML entities to allow special characters in category names for the database query.
if ($categoryFilter) {
    $categoryFilter = html_entity_decode($categoryFilter, ENT_QUOTES);
}

// Determine the correct database query to execute.

// Check if a category filter has been provided.
if ($categoryFilter) {
    // If a filter is present, use a secure prepared statement.
    $sql = "SELECT product_id, product_name, product_price, product_emissions, image_url
            FROM products WHERE category = ?";
    // Prepare the SQL statement for execution.
    $stmt = $conn->prepare($sql);
    // Link the user's category value to the prepared statement.
    $stmt->bind_param("s", $categoryFilter);
    // Run the prepared statement against the database.
    $stmt->execute();
    // Get the results from the executed query.
    $result = $stmt->get_result(); 
} else {
    // If there is no filter, simply fetch all products from the table.
    $result = $conn->query("SELECT product_id, product_name, product_price, product_emissions, image_url FROM products");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DragonStone Store</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <style>

    /* General page body styling and layout setup */
    body {
        margin: 0;
        /* Use the Montserrat font as the default */
        font-family: 'Montserrat', sans-serif;
        /* Set a dark blue-grey background color for the main page */
        background-color: #2E3A4C;
        /* Add top padding to move content below the fixed header and navigation bar */
        padding-top: 310px;
        /* Add bottom padding for the fixed floating cart bar */
        padding-bottom: 200px;
        /* Set the default text color to white */
        color: #FFFFFF;
    }

    /* Styling for the fixed, primary header section */
    header {
        /* Use the brand's primary green color for the background */
        background-color: #1DB959;
        height: 180px;
        /* Use flexbox to align the logo and icons horizontally */
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        color: white;
        /* Fix the header to the top of the viewport */
        position: fixed; 
        top: 0; left: 0;
        width: 100%;
        /* Ensure the header sits on top of all other content */
        z-index: 1000; 
    }

    .header-logo {
        height: 120px;
    }

    .header-icons {
        /* Arrange navigation icons with a large gap between them */
        display: flex;
        align-items: center;
        gap: 50px;
        margin-right: 50px;
    }

    /* Styling for the secondary, fixed navigation bar */
    nav {
        height: 120px;
        /* Use a light mint green background color */
        background-color: #DCF2DA;
        display: flex;
        /* Distribute the navigation items evenly across the width */
        justify-content: space-around;
        align-items: center;
        border-bottom: 1px solid #A3ACB5;
        position: fixed;
        /* Position the nav bar directly below the main header */
        top: 180px; 
        width: 100%;
        /* Place it just beneath the header */
        z-index: 999; 
        /* Add a subtle shadow for depth */
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    }

    .nav-item {
        /* Layout the text and icon for a single navigation item */
        display: flex;
        align-items: center;
        gap: 10px;
        position: relative;
        cursor: pointer;
    }

    nav a {
        /* Remove the default underline from links */
        text-decoration: none;
        /* Set the default link color to the dark blue-grey */
        color: #2E3A4C;
        font-size: 38px;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    nav a.active {
        /* Use the brand green color to highlight the currently active link */
        color: #1DB959; 
        font-weight: 700;
    }

    .dropdown-icon {
        width: 45px;
        height:45px;
        margin-top: 3px;
    }


    /* Styling for the full-screen category selection menu */
    .category-menu {
        padding-top: 25px;
        position: fixed;
        /* Position it below the navigation bar */
        top: 300px; 
        left: 0;
        width: 100%;
        /* The menu takes up the rest of the screen height */
        height: 100%;
        /* Match the main page background color */
        background-color: #2E3A4C;
        /* Add a separator line using the brand green */
        border-top: 2px solid #1DB959;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        /* Ensure it's below the navigation but above the content */
        z-index: 998; 
        /* Allow categories to scroll if there are many */
        overflow-y: auto;
        /* Initially hide the menu and set up a smooth animation */
        max-height: 0;
        opacity: 0;
        transition: max-height 0.5s ease, opacity 0.5s ease;
    }

    .category-menu.open {
        /* Class applied by JavaScript to make the menu visible with animation */
        max-height: 100%; 
        opacity: 1;
    }

    .category-option {
        /* Layout for an individual category option */
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 25px 0 25px 15px;
        text-align: left;
        /* Use a subtle gray for the text color */
        color: #A3ACB5;
        font-size: 40px;
        font-weight: 500;
        cursor: pointer;
        /* Smooth transition for the hover effect */
        transition: background-color 0.2s;
    }

    .category-option img {
        width: 40px;
        height: 40px;
    }

    .category-option:hover {
        /* Darken the background slightly on mouse hover */
        background-color: #384558;
    }

    .category-option.active {
        /* Highlight the currently selected category with brand green */
        color: #1DB959; 
        font-weight: 600;
    }

    .clear-category {
        text-align: center;
        /* Use the brand green color */
        color: #1DB959;
        font-size: 40px;
        font-weight: 600;
        margin: 25px 0;
        cursor: pointer;
    }

    /* Styling for the title displayed when a category is filtered */
    .category-header {
        text-align: center;
        font-size: 50px;
        font-weight: 700;
        /* Use the brand green color */
        color: #1DB959;
        margin-top: 35px;
        margin-bottom: 20px;
    }

    /* Container for the main product layout */
    .product-grid {
        /* Arrange products in a two-column grid */
        display: grid;
        grid-template-columns: repeat(2, 1fr); 
        justify-content: center;
        /* Spacing between the columns and rows */
        column-gap: 25px;
        row-gap: 25px;
        padding: 25px 10px;
        /* Constrain the grid width and center it on the page */
        max-width: 900px;
        margin: 0 auto; 
    }

    /* Styling for individual product presentation boxes */
    .product-card {
        width: 442px;
        height: 702px;
        /* Brand green border for the card */
        border: 3px solid #1DB959;
        border-radius: 50px;
        /* Match the main page dark background */
        background-color: #2E3A4C;
        overflow: hidden;
        position: relative;
        cursor: pointer;
    }

    .product-image {
        width: 442px;
        height: 442px;
        /* Ensure the image covers the area without distortion */
        object-fit: cover;
        display: block;
    }

    .add-cart-icon {
        /* Position the cart icon in the top right corner of the image */
        position: absolute;
        top: 18px;
        right: 18px;
        width: 90px !important;
        height: 90px !important;
        object-fit: contain;
        cursor: pointer;
        /* Ensure the icon is clickable over the image */
        z-index: 5;
    }

    .product-info {
        position: relative;
        /* Calculate the height based on the card's total height minus the image height */
        height: calc(100% - 442px);
        padding: 14px 20px 24px 20px;
        box-sizing: border-box;
    }

    .product-name {
        margin-top: 10px;
        font-weight: 700;
        font-size: 42px;
        /* Use the brand green color for the name */
        color: #1DB959;
        /* Limit the name to two lines with an ellipsis if it's too long */
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2em;
        max-height: 2.4em;
    }

    .product-price {
        /* Position the price near the bottom left */
        position: absolute;
        bottom: 70px;
        left: 20px;
        font-weight: 700;
        font-size: 42px;
        /* Use white color for visibility */
        color: #FFFFFF;
    }

    .product-emission {
        /* Position the emissions information at the bottom left */
        position: absolute;
        bottom: 20px;
        left: 20px;
        font-weight: 500;
        font-size: 36px;
        /* Use a subtle gray for the emissions text */
        color: #A3ACB5;
    }

    .product-emission sub {
        /* Adjust the size for the CO2 subscript */
        font-size: 26px;
        vertical-align: baseline;
    }

    /* Styling for the temporary user feedback message */
    .popup {
        position: fixed;
        /* Center the popup exactly in the middle of the screen */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%); 
        /* Semi-transparent black background */
        background-color: rgba(0,0,0,0.7);
        color: #FFFFFF;
        padding: 20px 30px;
        border-radius: 10px;
        /* Hide the popup by default, controlled by JavaScript */
        display: none; 
        font-size: 40px;
        /* Ensure it appears above all other content */
        z-index: 2000;
    }

    /* Styling for the fixed, bottom-of-screen cart summary bar */
    .floating-cart {
        position: fixed;
        /* Center the cart bar horizontally */
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        /* Use the brand green color for the background */
        background-color: #1DB959;
        border-radius: 50px;
        width: 90%;
        height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        /* Add a clear shadow */
        box-shadow: 0 4px 8px rgba(0,0,0,0.5);
        cursor: pointer;
        color: #FFFFFF;
        font-size: 45px;
        font-weight: 500;
        /* Ensure it's the highest UI element */
        z-index: 1001; 
    }

    .floating-cart img {
        width: 70px;
        height: 70px;
    }

    </style>

<script>
/**
 * Handles adding or removing a product from the cart using an API call.
 * This function also updates the UI to reflect the new cart status.
 *
 * @param {string} productId - The unique identifier of the product.
 * @param {HTMLElement} iconElement - The specific image element that was clicked.
 * @param {Event} event - The click event used to stop propagation.
 */
function toggleCart(productId, iconElement, event) {
    // Prevent the click from activating the parent product card's link.
    event.stopPropagation();

    // Determine the action based on the current state of the product icon.
    const isInCart = iconElement.dataset.inCart === "true";
    const action = isInCart ? "remove" : "add";

    // Send an asynchronous request to update the cart on the server side.
    fetch("update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        // Pass the product ID and the desired action in the request body.
        body: new URLSearchParams({ product_id: productId, action })
    })
    .then(res => res.json())
    .then(data => {
        // Update the text in the fixed floating cart bar with the new total.
        if (data.cartCountMessage) {
            document.getElementById("cart-text").textContent = data.cartCountMessage;
        }

        // Change the product icon to show the new cart status.
        iconElement.src = action === "add"
            ? "assets/icons/removeCart_icon.png"
            : "assets/icons/addCart_icon.png";
        // Update the custom data attribute for tracking the state in JavaScript.
        iconElement.dataset.inCart = action === "add" ? "true" : "false";

        // Show a brief pop-up message to confirm the action to the user.
        const popup = document.getElementById("popup");
        popup.textContent = action === "add" ? "Added to cart" : "Removed from cart";
        popup.style.display = "block";
        // Automatically hide the popup after a short delay.
        setTimeout(() => popup.style.display = "none", 1200);
    })
    .catch(err => console.error("Cart update failed:", err));
}

/** Toggles the category dropdown menu by simply adding or removing the 'open' class. */
function toggleCategoryMenu() {
    document.getElementById("category-menu").classList.toggle("open");
}

/**
 * Directs the user to the shop page with a specific category filter applied.
 * @param {string} category - The name of the category to filter by.
 */
function goToCategory(category) {
    // Construct the new URL by safely encoding the category name.
    window.location.href = "index.php?category=" + encodeURIComponent(category);
}

/** Navigates the user back to the main shop page, effectively clearing any active category filter. */
function clearCategory() {
    window.location.href = "index.php";
}
</script>
</head>
<body>

<header>
    <img src="assets/icons/DragonStone_logo.png" class="header-logo" alt="DragonStone Logo">
    
</header>

<nav>
    <div class="nav-item active" onclick="toggleCategoryMenu()">
        <a href="#" class="active">Shop</a>
        <img src="assets/icons/dropdown_icon_alt.png" class="dropdown-icon" alt="Dropdown Arrow" height="50px">
    </div>
    <div class="nav-item"><a href="community.php">Community</a></div>
    <div class="nav-item"><a href="profile.php">Profile</a></div>
</nav>

<div id="category-menu" class="category-menu">
<?php
// Define the list of available product categories.
$categories = [
    'Cleaning & Household Supplies',
    'Kitchen & Dining',
    'Home Décor & Living',
    'Bathroom & Personal Care',
    'Lifestyle & Wellness',
    'Kids & Pets',
    'Outdoor & Garden'
];

// Iterate over the categories to generate the menu options.
foreach ($categories as $cat) {
    // Check if the current category is the one actively being filtered.
    $isActive = ($cat === $categoryFilter) ? "active" : "";

    // Output a clickable option for each category.
    echo "<div class='category-option $isActive' onclick=\"goToCategory('".htmlspecialchars($cat, ENT_QUOTES)."')\">
              <img src='assets/icons/go_icon.png' alt='Go'>
              <span>".htmlspecialchars($cat)."</span>
          </div>";
}
?>
    <div class="clear-category" onclick="clearCategory()">Clear Category</div>
</div>

<?php if (!empty($categoryFilter)): ?>
<div class="category-header" style="margin-top:30px;"><?php echo htmlspecialchars($categoryFilter); ?></div>
<?php endif; ?>

<div class="product-grid">
<?php
// Verify that the database query returned results.
if ($result && $result->num_rows > 0) {
    // Loop through each row (product) returned from the database.
    while ($row = $result->fetch_assoc()) {
        // Determine if the current product is already in the session cart.
        $inCart = in_array($row['product_id'], $_SESSION['cart']);
        // Select the appropriate cart icon image based on the product's status.
        $cartIcon = $inCart ? "removeCart_icon.png" : "addCart_icon.png";
        // Set a string flag for JavaScript to track the cart status.
        $inCartState = $inCart ? "true" : "false";

        // Output the HTML structure for a single product card.
        echo "
        <div class='product-card' onclick=\"window.location.href='product.php?id={$row['product_id']}'\">
            <img src='".htmlspecialchars($row['image_url'])."' alt='".htmlspecialchars($row['product_name'])."' class='product-image'>
            <img src='assets/icons/{$cartIcon}' class='add-cart-icon' data-in-cart='{$inCartState}' onclick='toggleCart(\"{$row['product_id']}\", this, event)'>
            <div class='product-info'>
                <div class='product-name'>".htmlspecialchars($row['product_name'])."</div>
                <div class='product-price'>R".htmlspecialchars($row['product_price'])."</div>
                <div class='product-emission'>CO<sub>2</sub>: ".htmlspecialchars($row['product_emissions'])." kg</div>
            </div>
        </div>";
    }
} else {
    // Show a message if no products were found.
    echo "<p style='text-align:center;color:#A3ACB5;'>No products available</p>";
}
?>
</div>

<div id="popup" class="popup"></div>

<div class="floating-cart" onclick="window.location.href='cart.php'">
    <img src="assets/icons/Cart_icon.png" alt="Cart Icon">
    <span id="cart-text">
        <?php
        // Count the unique products in the cart array.
        $count = count($_SESSION['cart']);
        // Display the count or a message indicating an empty cart.
        echo $count > 0 ? "($count) items in Cart" : "Cart is empty";
        ?>
    </span>
</div>

<script>
// Event listener that runs when the tab receives focus.
window.addEventListener('focus', () => {
    // Retrieve a potential cart update message stored in temporary session storage.
    const updatedMsg = sessionStorage.getItem('cartCountMessage');
    if (updatedMsg) {
        // Update the floating cart text if a message exists.
        const el = document.getElementById('cart-text');
        if (el) el.textContent = updatedMsg;
        // Remove the temporary message after using it.
        sessionStorage.removeItem('cartCountMessage');
    }
});
</script>

<script>
// Event listener to handle returning to this page from a different one.
window.addEventListener('pageshow', (event) => {
    // Check if a flag was set indicating the cart was modified on the product page.
    if (sessionStorage.getItem('cartUpdated') === 'true') {
        // Clear the flag after confirming the update.
        sessionStorage.removeItem('cartUpdated');

        // Dynamically fetch the latest cart totals without reloading the page.
        fetch('update_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            // Send a request to get the current status of the cart.
            body: 'action=status'
        })
        .then(res => res.json())
        .then(data => {
            // Update the text in the floating cart bar.
            if (data.cartCountMessage) {
                const cartText = document.getElementById('cart-text');
                if (cartText) cartText.textContent = data.cartCountMessage;
            }

            // Also update the individual cart icons on each product card for accuracy.
            if (data.cart_ids) {
                // Loop through all product icons on the page.
                document.querySelectorAll('.add-cart-icon').forEach(icon => {
                    // Extract the product ID from the product card's onclick attribute.
                    const match = icon.closest('.product-card').getAttribute('onclick').match(/id=(\d+)/);
                    if (!match) return; // Skip if no ID is found
                    const pid = match[1];

                    // Check if the product ID is in the updated list of cart IDs.
                    const inCart = data.cart_ids.includes(pid);
                    
                    // Set the correct icon image and state attribute.
                    icon.src = inCart ? 'assets/icons/removeCart_icon.png' : 'assets/icons/addCart_icon.png';
                    icon.dataset.inCart = inCart ? 'true' : 'false';
                });
            }
        });
    }
});
</script>


</body>
</html>
<?php