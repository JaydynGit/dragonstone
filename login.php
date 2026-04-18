<?php
// Include the script to establish the database connection.
include 'db_connect.php';

// Check if a session is running; if not, start one for state management.
if (session_status() === PHP_SESSION_NONE) session_start();

// Initialize a variable to hold any login failure messages.
$error = ""; 

// Check if the form was submitted using the POST method.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Retrieve the email and remove any leading/trailing whitespace.
    $email = trim($_POST['email']);
    // Retrieve the raw password input.
    $password = $_POST['password'];

    // 1. Check if the submitted email address is in a valid format.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email.";
    } else {
        // 2. Prepare a secure statement to find the user by their email.
        $stmt = $conn->prepare("SELECT users_id, first_name, email, password FROM users WHERE email = ?");
        
        // Handle any errors that occur during the statement preparation.
        if (!$stmt) { 
            $error = "Database error: " . $conn->error; 
        } else {
            // Bind the sanitized email parameter to the prepared query.
            $stmt->bind_param("s", $email);
            // Execute the query to find the user.
            $stmt->execute();
            // Get the results from the executed query.
            $result = $stmt->get_result();

            // 3. Check if a user account was found with the provided email.
            if ($result && $result->num_rows > 0) {
                // Fetch the user's data, including their hashed password.
                $user = $result->fetch_assoc();
                
                // 4. Compare the submitted password against the stored hashed password.
                if (password_verify($password, $user['password'])) {
                    // Login successful: Store essential user data in the session.
                    $_SESSION['user_id'] = $user['users_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['email'] = $user['email'];
                    
                    // Send the user to the main shop page.
                    header("Location: index.php");
                    exit;
                } else {
                    // The password was incorrect.
                    $error = "Incorrect password.";
                }
            } else {
                // No account matched the entered email address.
                $error = "No account found with that email.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login - DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
		/* Set up the font, background, and center the content on the page */
		body { 
			font-family: 'Montserrat', sans-serif; 
			margin:0; 
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
		/* Header logo image size */
		header img { height:120px; }
		/* Container to hold the form, centered below the header */
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
		
		/* Style for the main login button */
		.button { 
			/* Use the brand green color */
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
		/* Style for the sign-up link text */
		.small-link { margin-top:30px; font-size:30px; color:#fff;}
		/* Style for the login error message */
		.error { color:red; font-size:20px; margin-top:15px; }
</style>
</head>
<body>
<header>
    <img src="assets/icons/DragonStone_login.png" alt="DragonStone">
</header>

<div class="form-wrap">
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button class="button" type="submit">Log in</button>
    </form>

    <div class="small-link">
        Don't have an account? 
        <a style="color:#1DB959; text-decoration:underline;" href="signup.php">Sign up</a>
    </div>

    <?php if (!empty($error)) echo "<div class='error'>{$error}</div>"; ?>
    
    <?php 
    if (isset($_GET['success'])) {
        echo "<div style='color:#1DB959; font-size:20px; margin-top:10px;'>Account created successfully — please log in.</div>"; 
    }
    ?>
</div>
</body>
</html>