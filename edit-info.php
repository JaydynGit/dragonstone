<?php
// Include the script responsible for connecting to the database.
include 'db_connect.php';
// Start the session if it hasn't been started yet.
if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure the user is authenticated before allowing access to this page.
if (!isset($_SESSION['user_id'])) {
    // If no user ID is found in the session, redirect to the login page.
    header("Location: login.php");
    exit;
}

// Get the unique ID of the current user.
$user_id = $_SESSION['user_id'];
// Initialize variables for displaying feedback messages.
$message = "";
$error = "";

// Prepare a secure query to fetch all the current profile data for the form fields.
$stmt = $conn->prepare("SELECT first_name, last_name, email, cell_number, street_address, suburb, city, province, postal_code FROM users WHERE users_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
// Fetch the user's current information as an associative array.
$user = $stmt->get_result()->fetch_assoc();

// Check if the form has been submitted to update the profile.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and clean all submitted data from the form.
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $cell = trim($_POST['cell_number']);
    $street = trim($_POST['street_address']);
    $suburb = trim($_POST['suburb']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $postal = trim($_POST['postal_code']);

    // First validation: check for a correctly formatted email address.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Second validation: ensure the new email is not already taken by another user.
        $check = $conn->prepare("SELECT users_id FROM users WHERE email = ? AND users_id != ?");
        $check->bind_param("ss", $email, $user_id);
        $check->execute();
        // Check if a different user was found with the same email.
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            // Stop the process if the email is already in use by someone else.
            $error = "This email is already in use by another account.";
        } else {
            // All validations passed; prepare the statement to update the user's record.
            $update = $conn->prepare("
                UPDATE users 
                SET first_name = ?, last_name = ?, email = ?, cell_number = ?, 
                    street_address = ?, suburb = ?, city = ?, province = ?, postal_code = ?
                WHERE users_id = ?
            ");
            // Bind all the new data and the user ID to the update statement.
            $update->bind_param("ssssssssss", $first, $last, $email, $cell, $street, $suburb, $city, $province, $postal, $user_id);

            // Execute the update query.
            if ($update->execute()) {
                // Show a success notification to the user.
                $message = "Information updated successfully!";
                // Re-fetch the updated data from the database to refresh the form fields.
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
                $update->close();
            } else {
                // Show a database error if the update failed.
                $error = "Error updating information: " . $conn->error;
            }
        }
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Information | DragonStone</title>
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
    padding-top: 230px;
}

/* Styling for the fixed header bar */
header {
    /* Use the brand green color. */
    background-color: #1DB959;
    height: 200px;
    display: flex;
    align-items: center;
    padding-left: 40px;
    position: fixed;
    top: 0;
    width: 100%;
    /* Ensure the header is the top-most element. */
    z-index: 1000;
    box-shadow: 0 6px 12px rgba(0,0,0,0.3);
}
/* Style for the back button icon */
header img {
    height: 120px;
    cursor: pointer;
    margin-right: 25px;
}
/* Style for the header title */
header h1 {
    font-size: 2.6rem;
    font-weight: 700;
}

/* Container for the main form content */
.container {
    width: 90%;
    max-width: 900px;
    margin: 0 auto 100px;
    /* Use a slightly lighter dark background for the container box. */
    background-color: #384558;
    /* Brand green border for emphasis. */
    border: 2px solid #1DB959;
    border-radius: 25px;
    padding: 40px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.4);
}

/* Layout for the form elements */
form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
/* Styling for all input fields */
input {
    padding: 20px;
    border-radius: 15px;
    border: 2px solid #1DB959;
    /* Use the main dark background color inside the inputs. */
    background-color: #2E3A4C;
    color: white;
    font-size: 18px;
    width: 100%;
}
/* Style for the input placeholder text */
input::placeholder {
    color: #A3ACB5;
}
/* Styling for the Save Changes button */
button {
    /* Use the brand green color. */
    background-color: #1DB959;
    border: none;
    color: white;
    padding: 18px;
    border-radius: 15px;
    font-size: 20px;
    font-weight: 600;
    cursor: pointer;
    /* Smooth transition for the hover effect. */
    transition: background-color 0.3s;
}
/* Button hover effect */
button:hover {
    background-color: #18A14D;
}
/* Style for success messages */
.message {
    font-size: 20px;
    /* Use the brand green for success. */
    color: #1DB959;
    text-align: center;
    margin-top: 15px;
}
/* Style for error messages */
.error {
    font-size: 20px;
    /* Use a red color for errors. */
    color: #FF4D4D;
    text-align: center;
    margin-top: 15px;
}
</style>
</head>
<body>

<header>
    <img src="assets/icons/back_icon.png" alt="Back" onclick="window.location.href='profile.php'">
    <h1>Edit Information</h1>
</header>

<div class="container">
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($user['first_name']); ?>" required>
        <input type="text" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($user['last_name']); ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($user['email']); ?>" required>
        <input type="text" name="cell_number" placeholder="Cell Number" value="<?= htmlspecialchars($user['cell_number']); ?>" maxlength="13" required>

        <input type="text" name="street_address" placeholder="Street Address" value="<?= htmlspecialchars($user['street_address']); ?>" required>
        <input type="text" name="suburb" placeholder="Suburb" value="<?= htmlspecialchars($user['suburb']); ?>" required>
        <input type="text" name="city" placeholder="City" value="<?= htmlspecialchars($user['city']); ?>" required>
        <input type="text" name="province" placeholder="Province" value="<?= htmlspecialchars($user['province']); ?>" required>
        <input type="text" name="postal_code" placeholder="Postal Code" value="<?= htmlspecialchars($user['postal_code']); ?>" required>

        <button type="submit">Save Changes</button>
    </form>

    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>
</div>

</body>
</html>