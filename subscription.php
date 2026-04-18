<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Start the session if one hasn't been initialized.
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if user not logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user subscription status.
$stmt = $conn->prepare("SELECT subscription_status FROM users WHERE users_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Check the current status and set the boolean flag.
$isSubscribed = ($user['subscription_status'] === 'subscribed');
$message = "";

// Handle Subscribe or Unsubscribe actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['toggle_subscription'])) {
        if ($isSubscribed) {
            // Logic to unsubscribe the user.
            $update = $conn->prepare("UPDATE users SET subscription_status = 'unsubscribed' WHERE users_id = ?");
            $update->bind_param("s", $user_id);
            // Set the feedback message based on the query result.
            $message = $update->execute() ? "You have successfully unsubscribed." : "Error unsubscribing.";
            $update->close();
            // Invert the subscription status flag.
            $isSubscribed = false;
        } else {
            // Logic to subscribe the user.
            $update = $conn->prepare("UPDATE users SET subscription_status = 'subscribed' WHERE users_id = ?");
            $update->bind_param("s", $user_id);
            // Set the feedback message based on the query result.
            $message = $update->execute() ? "Subscription successful Welcome to the DragonStone Box" : "Error subscribing.";
            $update->close();
            // Invert the subscription status flag.
            $isSubscribed = true;
        }
    }
}

// Fetch all products included in the subscription offer.
$products = $conn->query("SELECT product_name, product_price, image_url, required_quantity FROM subscriptions ORDER BY product_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subscription | DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    background-color: #2E3A4C;
    color: white;
    /* Add padding for the fixed header */
    padding-top: 220px;
    text-align: center;
}

/* HEADER */
header {
    background-color: #1DB959;
    height: 200px;
    display: flex;
    align-items: center;
    padding-left: 40px;
    position: fixed;
    top: 0;
    width: 100%;
    /* Ensure the header is the top layer */
    z-index: 1000;
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}
header img {
    height: 120px;
    cursor: pointer;
    margin-right: 25px;
}
header h1 {
    font-size: 2.8rem;
    font-weight: 700;
}

/* CONTAINER */
.container {
    width: 90%;
    max-width: 1100px;
    margin: 0 auto 100px;
    background-color: #384558;
    border: 2px solid #1DB959;
    border-radius: 25px;
    padding: 40px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
    text-align: left;
}

/* PRODUCT CARD */
.product {
    display: flex;
    align-items: center;
    background-color: #2E3A4C;
    border: 1px solid #1DB959;
    border-radius: 20px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
}
.product img {
    width: 160px;
    height: 160px;
    object-fit: cover;
    border-radius: 15px;
    margin-right: 30px;
}
.product-details {
    flex: 1;
}
.product-details h3 {
    font-size: 1.6rem;
    color: #1DB959;
    margin-bottom: 5px;
}
.product-details p {
    font-size: 16px;
    color: #E0E0E0;
    margin: 4px 0;
}
.product-details .price {
    font-size: 18px;
    color: #1DB959;
    font-weight: 600;
}

/* BUTTONS */
.subscribe-btn, .unsubscribe-btn {
    display: block;
    width: 100%;
    border: none;
    color: white;
    padding: 20px;
    border-radius: 15px;
    font-size: 22px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 30px;
    transition: background 0.3s;
}
.subscribe-btn { background-color: #1DB959; }
.subscribe-btn:hover { background-color: #18A14D; }
.unsubscribe-btn { background-color: #FF4747; }
.unsubscribe-btn:hover { background-color: #D63A3A; }

/* MESSAGE */
.message {
    font-size: 22px;
    color: #FFD966;
    margin-bottom: 20px;
    text-align: center;
}
</style>
</head>
<body>

<header>
    <img src="assets/icons/back_icon.png" alt="Back" onclick="window.history.back()">
    <h1>Subscription</h1>
</header>

<div class="container">
    <h2 style="color:#1DB959;">Your DragonStone Subscription Box</h2>
    <p style="font-size:18px; color:#E0E0E0; margin-bottom:30px;">
        Get eco friendly products delivered to your door monthly. See what is included below
    </p>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if ($products->num_rows > 0): ?>
        <?php while ($p = $products->fetch_assoc()): ?>
            <div class="product">
                <img src="<?= htmlspecialchars($p['image_url']); ?>" alt="<?= htmlspecialchars($p['product_name']); ?>">
                <div class="product-details">
                    <h3><?= htmlspecialchars($p['product_name']); ?></h3>
                    <p>Quantity per subscriber: <?= (int)$p['required_quantity']; ?></p>
                    <p class="price">R<?= number_format($p['product_price'], 2); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="font-size:20px; text-align:center;">No products are currently in the subscription.</p>
    <?php endif; ?>

    <form method="POST">
        <button type="submit" name="toggle_subscription" 
            class="<?= $isSubscribed ? 'unsubscribe-btn' : 'subscribe-btn'; ?>">
            <?= $isSubscribed ? 'Unsubscribe' : 'Subscribe Now'; ?>
        </button>
    </form>

    <?php if ($isSubscribed): ?>
        <div class="message" style="color:#1DB959; font-weight:600;">You are currently subscribed</div>
    <?php endif; ?>
</div>

</body>
</html>