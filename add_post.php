<?php
// Include the script that handles the database connection.
include 'db_connect.php'; 
// Start the session if one hasn't been initialized.
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect if the user is not logged in.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Function to generate a unique post ID.
function generatePostId($conn) {
    // Select the last post ID inserted to find the highest number used.
    $result = $conn->query("SELECT post_id FROM community_posts ORDER BY post_id DESC LIMIT 1");
    
    // Check if any previous posts exist in the table.
    if ($row = $result->fetch_assoc()) {
        // Extract the numerical part of the last ID.
        $last = intval(substr($row['post_id'], 3));
        // Increment the number for the new post.
        $new = $last + 1;
    } else {
        // If no posts exist, start the ID count at 1.
        $new = 1;
    }
    
    // Format the new ID: "PST" followed by the padded number.
    return "PST" . str_pad($new, 5, "0", STR_PAD_LEFT);
}

// Form Submission Handling
// Check if the page received a form submission via the POST method.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and clean up form data by removing leading or trailing spaces.
    $title = trim($_POST['title']);
    $caption = trim($_POST['caption']);
    $link = trim($_POST['link']);
    // Get the logged in user's ID from the session.
    $user_id = $_SESSION['user_id'];
    
    // Generate the unique ID for the new post before insertion.
    $post_id = generatePostId($conn);

    // Data Sanitization
    // Clean title and caption against HTML injection risks.
    $title = htmlspecialchars($title, ENT_QUOTES);
    $caption = htmlspecialchars($caption, ENT_QUOTES);
    
    // Clean and validate the URL input for safety.
    $link = filter_var($link, FILTER_SANITIZE_URL);

    // Prepare the secure SQL INSERT statement.
    $stmt = $conn->prepare("INSERT INTO community_posts (post_id, user_id, post_title, post_caption, post_link) VALUES (?, ?, ?, ?, ?)");
    // Bind all five parameters to the statement.
    $stmt->bind_param("sssss", $post_id, $user_id, $title, $caption, $link);

    // Execute the insertion query.
    if ($stmt->execute()) {
        // Success: Redirect to the main community page.
        header("Location: community.php");
        exit;
    } else {
        // Failure: Store the database error message.
        $error = "Error creating post: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Post | DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
        /* Basic Page Setup */
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            /* Use a dark blue-grey background color. */
            background: #2E3A4C;
            /* Set the default text color to white. */
            color: #FFFFFF;
            text-align: center;
            /* Padding to clear the fixed header */
            padding-top: 200px;
        }

        /* Fixed Header Bar */
        header {
            /* Brand green color for the background. */
            background: #1DB959;
            height: 180px;
            display: flex;
            align-items: center;
            padding: 0 40px;
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            /* Ensure the header is the top layer. */
            z-index: 1000;
        }

        header img.back-icon { 
            /* Set consistent size for back icon */
            height: 65px; 
            margin-right: 15px;
        }
        
        header img.logo-icon { 
            /* Specific class for the page logo */
            height: 120px;
            margin-right: 15px;
        }

        header a {
            color: white;
            font-size: 38px;
            text-decoration: none;
            font-weight: 500;
            margin-left: 15px;
            text-align: left;
        }

        /* Form Layout */
        form {
            width: 90%;
            max-width: 700px;
            margin: 0 auto;
            text-align: left;
        }

        /* Input Fields and Textarea Styles */
        input, textarea {
            width: 100%;
            padding: 25px;
            margin: 15px 0;
            /* Border color adjusted for visibility on dark background */
            border: 1px solid #455366; 
            border-radius: 20px;
            font-size: 28px;
            font-family: 'Montserrat', sans-serif;
            /* Input background changed to lighter dark color */
            background-color: #384558;
            /* Input text color changed to White */
            color: #FFFFFF;
        }
        
        /* Ensure placeholders are visible on dark background */
        input::placeholder, textarea::placeholder {
            color: #A3ACB5; 
            opacity: 1; 
        }

        textarea {
            height: 180px;
            /* Prevent user from resizing the textarea */
            resize: none; 
        }

        /* Fixed Floating Submit Button */
        button {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            /* Blue button color for posting */
            background: #08A9F2;
            border: none;
            border-radius: 50px;
            width: 90%;
            max-width: 700px;
            height: 120px;
            color: white;
            font-size: 45px;
            font-weight: 500;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
        }

        button:hover {
            background: #0592d1;
        }

        /* Error Message Style */
        .error {
            color: red;
            font-size: 22px;
            margin-top: 10px;
            text-align: center;
        }
</style>
</head>

<body>
<header>
    <a href="community.php">
        <img src="assets/icons/back_icon.png" alt="Back" class="back-icon">
    </a>
    <img src="assets/icons/DragonStone_post.png" class="logo-icon" alt="DragonStone Post Logo">
    
</header>

<form method="POST" action="">
    <input type="text" name="title" placeholder="Title" required>
    <textarea name="caption" placeholder="Caption" required></textarea>
    <input type="url" name="link" placeholder="Link (optional)">
    
    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    
    <button type="submit">Post to Community</button>
</form>

</body>
</html>