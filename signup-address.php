<?php
// Include the script to establish the database connection.
include 'db_connect.php';

// Start the session if one isn't running to access temporary user data.
if (session_status() === PHP_SESSION_NONE) session_start();

// --- Step 1: Session Check ---
// Verify that the necessary data from the first signup page exists in the session.
if (!isset($_SESSION['signup_user'])) {
    // If the data is missing, redirect the user back to the first signup step.
    header("Location: signup.php");
    exit;
}

// Get the user data that was temporarily saved in the session.
$signup_user = $_SESSION['signup_user'];
// Initialize an empty string to hold any potential error messages.
$error = "";

// --- Step 2: Handle Form Submission (POST Request) ---
// Check if the address form was submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and clean the address details from the submitted form.
    $street = trim($_POST['street']);
    $suburb = trim($_POST['suburb']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $postal = trim($_POST['postal_code']);

    // Retrieve the personal and login info saved from the previous page.
    $user_id = $signup_user['user_id'];
    $first = $signup_user['first'];
    $last = $signup_user['last'];
    $email = $signup_user['email'];
    $cell = $signup_user['cell'];
    // The password is the hashed version already prepared in the previous step.
    $password = $signup_user['hashed']; 
    // Set the initial EcoPoints balance to zero for a new account.
    $ecopoints = 0; 

    // --- Step 3: Insert Final User Record ---
    // Prepare a secure SQL statement to insert the complete user record into the database.
    $stmt = $conn->prepare("INSERT INTO users
        (users_id, first_name, last_name, email, cell_number, password, street_address, suburb, city, province, postal_code, ecopoints, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

    // Check if the prepared statement failed to initialize.
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind all 12 parameters to the prepared statement. 
    // The 's' indicates string, and 'i' indicates integer for the ecopoints value.
    $stmt->bind_param(
        "sssssssssssi",
        $user_id, $first, $last, $email, $cell, $password,
        $street, $suburb, $city, $province, $postal, $ecopoints
    );

    // Execute the final database insertion query.
    if ($stmt->execute()) {
        // Success: The account is created, so remove the temporary data from the session.
        unset($_SESSION['signup_user']); 
        
        // Redirect the user to the login page and include a success flag in the URL.
        header("Location: login.php?success=1");
        exit;
    } else {
        // Failure: Capture and display the specific database error.
        $error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up (Address) | DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
        /* Basic Page Setup */
        body { 
            font-family: 'Montserrat', sans-serif; 
            margin:0; 
            /* Use a dark blue-grey background color. */
            background:#2E3A4C; 
            padding-top:200px; 
            text-align:center; 
        }
        /* Fixed Header Bar at the top */
        header { 
            background:#1DB959; 
            height:180px; 
            display:flex; 
            align-items:center; 
            justify-content:space-between; 
            padding:0 40px; 
            color:#fff; 
            position:fixed; 
            top:0; 
            width:100%; 
        }
        /* Header logo size */
        header img { height:120px; }
        /* Container to hold the form */
        .form-wrap { max-width:700px; margin:120px auto 0; padding:30px; }
        /* Styling for all text input fields */
        input { 
            width:90%; 
            max-width:700px; 
            padding:25px; 
            margin:15px 0; 
            border:1px solid #A3ACB5; 
            border-radius:20px; 
            font-size:28px; 
            /* Dark background for input fields */
            background : #384558;
            color: #FFFFFF;
        }
        
        /* Style the placeholder text within input fields */
        input::placeholder {
            color: #A3ACB5; 
            opacity: 1; 
        }
        
        /* General button style (not used for form submit button, but kept) */
        .button { 
            background:#1DB959; 
            border:none; 
            border-radius:50px; 
            width:90%; 
            max-width:700px; 
            height:120px; 
            color:white; 
            font-size:45px; 
            font-weight:500; 
            cursor:pointer; 
            margin-top:20px; 
        }
        
        /* Style for displaying error messages */
        .error { color:red; font-size:20px; margin-top:15px; }

        /* Style for the form submission button */
        button {
            background:#1DB959;
            border:none;
            border-radius:50px;
            width:90%;
            max-width:700px;
            height:120px;
            color:white;
            font-size:45px;
            font-weight:500;
            margin-top:30px;
            cursor:pointer;
        }
        
        /* Style for the link to the login page */
        a.green-link {
            color:#1DB959;
            text-decoration:underline;
        }
</style>
</head>
<body>
<header>
    <img src="assets/icons/DragonStone_signup.png" alt="Logo">
</header>

<form method="POST" action="">
    <input name="street" placeholder="Street number & name" required>
    <input name="suburb" placeholder="Suburb" required>
    <input name="city" placeholder="City" required>
    <input name="province" placeholder="Province" required>
    <input name="postal_code" placeholder="Postal Code" required>

    <div style="margin-top:50px;font-size:30px;color:#fff;">
        Already have an account? <a class="green-link" href="login.php">Log in</a>
    </div>
    
    <button type="submit">Complete</button>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
</form>
</body>
</html>