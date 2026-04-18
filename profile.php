<?php
// Include the script that establishes the database connection.
include 'db_connect.php';
// Start the session if one hasn't been started yet.
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if the user is logged in by looking for their ID in the session.
if (!isset($_SESSION['user_id'])) {
    // If not logged in, send them to the login page immediately.
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID from the session variable.
$user_id = $_SESSION['user_id'];

// Prepare a secure query to fetch all required profile details for the user.
$stmt = $conn->prepare("SELECT users_id, first_name, last_name, email, cell_number, ecopoints, street_address, suburb, city, province, postal_code FROM users WHERE users_id = ?");
// Bind the user ID variable to the prepared statement to prevent SQL injection.
$stmt->bind_param("s", $user_id);
// Execute the prepared statement.
$stmt->execute();
// Get the result set from the executed query.
$res = $stmt->get_result();

// Check if the query failed or if no user was found with that ID.
if (!$res || $res->num_rows == 0) {
    // Stop the script and show an error message.
    die("User not found.");
}
// Fetch the single user row as an associative array.
$user = $res->fetch_assoc();
// Close the prepared statement to free up resources.
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Profile - DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

<style>
/* General body and background styles */
body {
    margin: 0;
    font-family: 'Montserrat', sans-serif;
    /* Use a dark blue-grey background color. */
    background-color: #2E3A4C;
    /* Set the default text color to white. */
    color: #FFFFFF;
    /* Add padding to account for fixed header and navigation bar. */
    padding-top: 320px;
}

/* Styling for the fixed green header bar */
header {
    background: #1DB959;
    height: 180px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    color: white;
    position: fixed;
    top: 0;
    width: 100%;
    /* Ensure the header is the top-most element. */
    z-index: 1000;
}
.header-logo { height: 120px; }

/* Styling for the fixed navigation bar */
nav {
    height: 120px;
    /* Use a light mint green background color. */
    background-color: #DCF2DA;
    display: flex;
    justify-content: space-around;
    align-items: center;
    border-bottom: 1px solid #A3ACB5;
    position: fixed;
    /* Position directly below the header. */
    top: 180px;
    width: 100%;
    /* Position below the header but above content. */
    z-index: 999;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}
.nav-item {
    display: flex;
    align-items: center;
    gap: 5px;
    position: relative;
    cursor: pointer;
}
nav a {
    text-decoration: none;
    /* Set the default link color to dark blue-grey. */
    color: #2E3A4C;
    font-size: 38px;
    font-weight: 500;
}
nav a.active {
    /* Highlight the active link with the brand green color. */
    color: #1DB959;
    font-weight: 700;
}

/* Styling for the main profile content area */
.profile-container {
    /* Limit the content width and center it on the page. */
    max-width: 1100px;
    margin: 0 auto;
    padding: 40px 60px;
}
/* Styling for the section showing the user's name and points */
.profile-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 80px;
}
.profile-header h2 {
    font-size: 90px;
    /* Use the brand green color for the user's name. */
    color: #1DB959;
    font-weight: 600;
    margin: 0;
}
/* Styling for the EcoPoints display badge */
.ecopoints-badge {
    /* Use the dark background with a green border for the badge. */
    background: #2E3A4C;
    border: 2px solid #1DB959;
    color: #1DB959;
    font-size: 30px;
    font-weight: 600;
    padding: 20px 45px;
    border-radius: 50px;
    margin-top: 30px;
}

/* Styles for major content blocks */
.section { margin-bottom: 60px; }
/* Title for each profile section */
.section-title {
    font-size: 36px;
    color: #1DB959;
    font-weight: 600;
    margin-bottom: 25px;
    /* Add a green vertical bar on the left for emphasis. */
    border-left: 6px solid #1DB959;
    padding-left: 15px;
}
/* Styling for a single data field (label and value) */
.profile-field {
    margin: 15px 0;
    font-size: 28px;
    line-height: 1.6;
}
/* Styling for the label (e.g., 'Email:') */
.profile-label {
    font-weight: 600;
    /* Use a light green color for the labels. */
    color: #60CE8A;
    width: 200px;
    display: inline-block;
}
/* Styling for the actual data value */
.profile-value {
    font-weight: 500;
    color: #FFFFFF;
}

/* Container for profile action buttons */
.buttons {
    display: flex;
    justify-content: left;
    gap: 30px;
    flex-wrap: wrap;
    margin-top: 60px;
}
/* General button styling */
.btn {
    background: #1DB959;
    border: none;
    color: #fff;
    padding: 30px 50px;
    border-radius: 50px;
    font-size: 35px;
    font-weight: 500;
    cursor: pointer;
    /* Smooth transition for hover effects. */
    transition: all 0.2s ease;
}
/* Button hover effect */
.btn:hover {
    background: #18a14d;
    transform: scale(1.03);
}
/* Specific styling for the Orders button */
.orders-btn {
    display: block;
    width: 100%;
    /* Use a blue color for this button to make it stand out. */
    background: #08A9F2;
    margin-top: 60px;
}
/* Hover effect for the Orders button */
.orders-btn:hover {
    background: #0794d6;
}

/* Styling for the dedicated Logout button */
.logout-btn {
    display: block;
    width: 100%;
    /* Use a red color to clearly indicate a destructive action. */
    background: #FF4747;
    color: white;
    border: none;
    border-radius: 50px;
    padding: 35px 0;
    font-size: 36px;
    font-weight: 600;
    margin-top: 80px;
    cursor: pointer;
    transition: all 0.3s ease;
}
/* Hover effect for the Logout button */
.logout-btn:hover {
    background: #D63A3A;
    transform: scale(1.02);
}
</style>
</head>

<body>
<header>
    <img src="assets/icons/DragonStone_logo.png" class="header-logo" alt="DragonStone Logo">
</header>

<nav>
    <div class="nav-item"><a href="index.php">Shop</a></div>
    <div class="nav-item"><a href="community.php">Community</a></div>
    <div class="nav-item"><a href="#" class="active">Profile</a></div>
</nav>

<div class="profile-container">
    <div class="profile-header">
        <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
        <div class="ecopoints-badge">🌿 <?= (int)$user['ecopoints']; ?> EcoPoints</div>
    </div>

    <div class="section">
        <div class="section-title">Contact Information</div>
        <div class="profile-field"><span class="profile-label">Cell:</span> <span class="profile-value"><?= htmlspecialchars($user['cell_number']); ?></span></div>
        <div class="profile-field"><span class="profile-label">Email:</span> <span class="profile-value"><?= htmlspecialchars($user['email']); ?></span></div>
    </div>

    <div class="section">
        <div class="section-title">Address</div>
        <div class="profile-field"><span class="profile-label">Street:</span> <span class="profile-value"><?= htmlspecialchars($user['street_address']); ?></span></div>
        <div class="profile-field"><span class="profile-label">Suburb:</span> <span class="profile-value"><?= htmlspecialchars($user['suburb']); ?></span></div>
        <div class="profile-field"><span class="profile-label">City:</span> <span class="profile-value"><?= htmlspecialchars($user['city']); ?></span></div>
        <div class="profile-field"><span class="profile-label">Province:</span> <span class="profile-value"><?= htmlspecialchars($user['province']); ?></span></div>
        <div class="profile-field"><span class="profile-label">Postal Code:</span> <span class="profile-value"><?= htmlspecialchars($user['postal_code']); ?></span></div>
    </div>

    <div class="buttons">
        <button class="btn half" onclick="window.location='subscription.php'">Subscribe</button>
        <button class="btn half" onclick="window.location='edit-info.php'">Edit Information</button>
    </div>

    <button class="btn orders-btn" onclick="window.location='customer_orders.php'">Orders & Order History</button>

    <button class="logout-btn" onclick="logoutUser()">Logout</button>
</div>

<script>
// JavaScript function to handle the logout process via an AJAX call.
function logoutUser() {
    // Send a POST request to the logout script on the server.
    fetch('logout.php', { method: 'POST' })
        // After the server processes the logout, redirect the user to the login page.
        .then(() => window.location.href = 'login.php');
}
</script>

</body>
</html>