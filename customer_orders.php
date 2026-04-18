<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Start the session if one hasn't already been initialized.
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect the user if they are not currently logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Get the unique user ID from the session.
$uid = $_SESSION['user_id'];

// Prepare a secure query to fetch all orders associated with this user ID.
// The results are sorted with the most recent order first.
$stmt = $conn->prepare("SELECT order_id, order_ref, status, total, created_at FROM orders WHERE users_id = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $uid);
$stmt->execute();
// Get the result set from the executed query.
$res = $stmt->get_result();
// Fetch all retrieved order rows into a single associative array.
$orders = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close(); // Close the prepared statement.

// Check the URL for a 'created' parameter, which indicates a newly placed order.
$order_created_ref = $_GET['created'] ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Your Orders - DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* Basic page and font setup */
body {
    font-family: 'Montserrat', sans-serif;
    margin: 0;
    /* Use a dark blue-grey background color. */
    background: #2E3A4C;
    color: white;
    /* Add padding for the fixed header. */
    padding-top: 220px;
}

/* Styling for the fixed header bar */
header {
    /* Use the brand green color. */
    background: #1DB959;
    height: 180px;
    display: flex;
    align-items: center;
    padding: 0 40px;
    color: white;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    /* Ensure the header is on top of other content. */
    z-index: 1000;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}
/* Style for the logo inside the header */
header img { height: 120px; }
/* Style for the back button icon */
.back-icon {
    height: 65px;
    margin-right: 25px;
    cursor: pointer;
}

/* Container for the main order list content */
.container {
    /* Center the content and limit its width. */
    max-width: 1000px;
    margin: 0 auto;
    padding: 40px;
}

/* Styling for the temporary message shown after a successful order is placed */
.success-message {
    /* Light background with green text and border. */
    background-color: #DCF2DA;
    color: #1DB959;
    border: 2px solid #1DB959;
    padding: 20px;
    border-radius: 15px;
    margin-bottom: 25px;
    font-size: 22px;
    text-align: center;
}

/* Styling for each individual order card */
.order {
    /* Use a slightly lighter dark background for the card. */
    background-color: #384558;
    /* Brand green border for the card. */
    border: 2px solid #1DB959;
    border-radius: 20px;
    padding: 25px 30px;
    margin-bottom: 25px;
    /* Use flexbox to align details and the button horizontally. */
    display: flex;
    justify-content: space-between;
    align-items: center;
    /* Smooth transition for the hover effect. */
    transition: transform 0.2s ease;
}
/* Lift the card slightly on hover for visual feedback. */
.order:hover {
    transform: translateY(-3px);
}
/* Container for the order details on the left */
.order-left {
    flex: 1;
}
/* Style for the bold order reference number */
.order-ref {
    font-size: 28px;
    font-weight: 700;
    /* Use the brand green color. */
    color: #1DB959;
}
/* Style for smaller text like date and status details */
.small {
    font-size: 20px;
    /* Use a subtle gray color. */
    color: #A3ACB5;
    margin-top: 6px;
}

/* Specific background and text colors for different order statuses */
.order-status {
    font-weight: 700;
    padding: 6px 15px;
    border-radius: 12px;
}
.order-status.pending { background: #FFB84D; color: #2E3A4C; } /* Orange for pending */
.order-status.delivered { background: #1DB959; color: white; } /* Green for delivered */
.order-status.cancelled { background: #FF4747; color: white; } /* Red for cancelled */

/* Styling for the 'View Details' button */
.view-btn {
    /* Use a blue color to differentiate it from the primary green. */
    background: #08A9F2;
    color: white;
    padding: 14px 28px;
    border-radius: 50px;
    /* Remove default link underline. */
    text-decoration: none;
    font-size: 22px;
    font-weight: 600;
    transition: background 0.3s ease;
}
/* Button hover effect */
.view-btn:hover {
    background: #0794D6;
}

/* Message shown when there are no orders */
.empty-message {
    text-align: center;
    font-size: 26px;
    color: #A3ACB5;
    margin-top: 80px;
}

/* Media queries for smaller screens (responsiveness) */
@media (max-width: 768px) {
    header {
        height: 160px;
    }
    header img {
        height: 100px;
    }
    .container {
        padding: 25px;
    }
    .order {
        /* Stack the order details and the button vertically. */
        flex-direction: column;
        align-items: flex-start;
    }
    .order-ref { font-size: 24px; }
    /* Make the view button full width on small screens. */
    .view-btn { margin-top: 15px; width: 100%; text-align: center; }
}
</style>
</head>
<body>

<header>
    <img src="assets/icons/back_icon.png" alt="Back" class="back-icon" onclick="window.location.href='profile.php'">
    <img src="assets/icons/DragonStone_your_orders.png" alt="Your Orders Logo">
</header>

<div class="container">

    <?php if ($order_created_ref): ?>
        <div class="success-message">
            ✅ Order <strong>#<?= htmlspecialchars($order_created_ref); ?></strong> has been successfully placed!
        </div>
    <?php endif; ?>

    <?php if (empty($orders)): ?>
        <div class="empty-message">
            You don’t have any orders yet.<br>
            Start shopping to see your history here!
        </div>
    <?php else: ?>
        <?php foreach ($orders as $o): ?>
            <div class="order">
                <div class="order-left">
                    <div class="order-ref">Order #<?= htmlspecialchars($o['order_ref']); ?></div>
                    <div class="small">
                        Status:
                        <span class="order-status <?= htmlspecialchars($o['status']); ?>">
                            <?= ucfirst(htmlspecialchars($o['status'])); ?>
                        </span>
                    </div>
                    <div class="small">Date: <?= date("d M Y H:i", strtotime($o['created_at'])); ?></div>
                    <div class="small">Total: R <?= number_format($o['total'], 2); ?></div>
                </div>
                <a href="order.php?id=<?= htmlspecialchars($o['order_id']); ?>" class="view-btn">View Details</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
</body>
</html>