<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Check if a session is active; if not, start one to store temporary signup data.
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Generates a unique, formatted user ID (e.g., USR00001).
 * It finds the last used ID in the database and increments it.
 * @param mysqli $conn The database connection object.
 * @return string The newly generated, formatted user ID.
 */
function generateUserId($conn) {
    // Fetch the largest existing user ID from the database.
    $result = $conn->query("SELECT users_id FROM users ORDER BY users_id DESC LIMIT 1");
    // Check if any previous users exist.
    if ($row = $result->fetch_assoc()) {
        // Extract the numeric part of the last ID (e.g., '00001' from 'USR00001').
        $lastId = intval(substr($row['users_id'], 3));
        // Increment the number for the new user ID.
        $newId = $lastId + 1;
    } else {
        // If this is the very first user, start the ID count at 1.
        $newId = 1;
    }
    // Format the new ID with a "USR" prefix and zero-pad it to 5 digits.
    return "USR" . str_pad($newId, 5, "0", STR_PAD_LEFT);
}

// Variables to hold feedback messages for the user.
$error = "";
$message = "";

// Check if the page received a form submission via the POST method.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and clean the data submitted by the user.
    $first = trim($_POST['first_name']);
    $last = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $cell = trim($_POST['cell']);
    $password = trim($_POST['password']);

    // --- Validation Checks ---

    // 1. Check if the provided email address is already in use.
    $stmt = $conn->prepare("SELECT users_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    // Check if the query returned any rows (meaning the email exists).
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close(); // Close the statement after checking

    if ($exists) {
        // Stop if the email is already registered.
        $error = "An account with this email already exists.";
    // 2. Validate the password complexity using a regular expression.
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+])[A-Za-z\d!@#$%^&*()_+]{8,}$/', $password)) {
        // Stop if the password doesn't meet the security requirements.
        $error = "Password must be at least 8 characters long, contain a capital letter, a number, and a symbol.";
    } else {
        // --- Successful Validation ---
        
        // Generate a new unique ID for the user.
        $user_id = generateUserId($conn);
        // Hash the password for secure storage later in the database.
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        
        // Temporarily store all gathered user data in the session.
        // This is done before the second signup step (address collection).
        $_SESSION['signup_user'] = compact('user_id', 'first', 'last', 'email', 'cell', 'hashed');
        
        // Redirect the user to the next step of the registration process.
        header("Location: signup-address.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign Up - DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
/* General body and background styles */
body { 
    font-family: 'Montserrat', sans-serif; 
    margin:0; 
    /* Use the dark blue-grey background color. */
    background:#2E3A4C; 
    padding-top:200px; 
    text-align:center; 
    color:white;
}
/* Fixed green header bar style */
header { 
    background:#1DB959; 
    height:180px; 
    display:flex; 
    align-items:center; 
    justify-content:space-between; 
    padding:0 40px; 
    position:fixed; 
    top:0; 
    width:100%; 
}
/* Header logo image size */
header img { height:120px; }

/* Form container styles */
form { 
    width:90%; 
    max-width:700px; 
    margin:0 auto; 
    text-align:left; 
}

/* Styles for all text input fields */
input { 
    width:100%; 
    padding:25px; 
    margin:15px 0; 
    border:1px solid grey; 
    border-radius:20px; 
    font-size:28px; 
    /* Use a slightly lighter dark background for input fields. */
    background:#384558;
    color:white;
}
/* Style the placeholder text within input fields */
input::placeholder { color:#A3ACB5; }

/* Container for the password field and eye icon */
.password-field {
    position: relative;
}
/* Eye icon for toggling password visibility */
.password-field img {
    position: absolute;
    right: 20px;
    top: 28px;
    width: 40px;
    cursor: pointer;
    /* Make the icon white for visibility on the dark background. */
    filter: brightness(0) invert(1);
}

/* Style for the submission button */
button {
    /* Use the brand green color */
    background:#1DB959;
    border:none;
    border-radius:50px;
    width:100%;
    height:120px;
    color:white;
    font-size:45px;
    font-weight:500;
    margin-top:30px;
    cursor:pointer;
}
/* Button hover effect */
button:hover { background:#18A14D; }

/* Style for displaying error messages */
.error { color:red; font-size:24px; margin-top:15px; text-align:center; }
/* Style for general success messages */
.message { color:#1DB959; font-size:24px; margin-top:15px; text-align:center; }

/* Styles for the password requirement information box */
.password-info {
    margin-top: 20px;
    font-size: 22px;
    color: #A3ACB5;
}
/* List styling for requirements */
.password-info ul {
    list-style-type: disc;
    text-align: left;
    margin-left: 40px;
}
.password-info li { margin: 8px 0; }

/* Style for the link to the login page */
a.green-link { color:#1DB959; text-decoration:underline; }
</style>
</head>
<body>
<header>
    <img src="assets/icons/DragonStone_signup.png" alt="Logo">
</header>

<form method="POST" action="">
    <input name="first_name" placeholder="First name" required>
    <input name="last_name" placeholder="Last name" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="cell" placeholder="Cell number" maxlength="13" required>

    <div class="password-field">
        <input id="password" name="password" type="password" placeholder="Password" required>
        <img src="assets/icons/visible_icon.png" alt="Show Password" id="togglePassword">
    </div>

    <div class="password-info">
        <strong>Password Requirements:</strong>
        <ul>
            <li>Minimum 8 characters</li>
            <li>At least 1 uppercase letter (A–Z)</li>
            <li>At least 1 number (0–9)</li>
            <li>At least 1 symbol (!@#$%^&amp;*)</li>
        </ul>
    </div>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <?php if (!empty($message)) echo "<div class='message'>$message</div>"; ?>

    <div style="margin-top:50px;font-size:30px;text-align:center;">
        Already have an account? <a class="green-link" href="login.php">Log in</a>
    </div>
    
    <button type="submit">Next: Address</button>
</form>

<script>
// Add a listener to the eye icon to change the password field type.
document.getElementById('togglePassword').addEventListener('click', function() {
    const passField = document.getElementById('password');
    // Check the current type of the input field.
    if (passField.type === 'password') {
        // Change from hidden text to visible text.
        passField.type = 'text';
        // Adjust the icon's appearance slightly to indicate visibility.
        this.style.opacity = 0.7;
    } else {
        // Change from visible text back to hidden password type.
        passField.type = 'password';
        // Reset the icon's appearance.
        this.style.opacity = 1;
    }
});
</script>
</body>
</html>