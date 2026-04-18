<?php
// Bring in the script that establishes the database connection object.
include 'db_connect.php';

// Start a new user session if one is not already running.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Build the SQL query to fetch all community posts.
// The query joins the posts table with the users table to get the author's full name.
// Posts are then organized by the time they were posted, showing the newest first.
$query = "
    SELECT cp.post_title, cp.post_caption, cp.post_link, cp.posted_at,
            u.first_name, u.last_name
    FROM community_posts cp
    JOIN users u ON cp.user_id = u.users_id
    ORDER BY cp.posted_at DESC
";
// Execute the query against the database.
$result = $conn->query($query);

// Check if there was an issue running the database query.
if (!$result) {
    // Record the specific error detail for developers to see later.
    error_log("Community post query failed: " . $conn->error);
    // Stop execution and show a friendly error message to the user.
    die('An error occurred while fetching posts. Please try again later.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Community Feed - DragonStone</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
        /* General page setup and required fixed element spacing */
        body {
            margin: 0;
            font-family: 'Montserrat', sans-serif;
            /* Use a dark blue-grey for the background color. */
            background-color: #2E3A4C;
            /* Push content down below the fixed header and navigation bar. */
            padding-top: 320px;
            /* Provide space at the bottom for the fixed 'Add Post' button. */
            padding-bottom: 200px;
            /* Set the default text color to white. */
            color: #FFFFFF;
        }

        /* Styling for the fixed green header bar */
        header {
            background-color: #1DB959;
            height: 180px;
            /* Use flexbox to organize contents horizontally. */
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            color: white;
            /* Keep the header fixed at the top of the viewport. */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            /* Ensure the header is the top-most layer. */
            z-index: 1000;
        }
        .header-logo { height: 120px; }
        .header-icons {
            /* Arrange the icons with a gap. */
            display: flex;
            align-items: center;
            gap: 50px;
            margin-right: 50px;
        }

        /* Styling for the secondary, fixed navigation bar */
        nav {
            height: 120px;
            /* Use a light mint green background color. */
            background-color: #DCF2DA;
            display: flex;
            /* Distribute navigation items evenly. */
            justify-content: space-around;
            align-items: center;
            border-bottom: 1px solid #A3ACB5;
            position: fixed;
            /* Place the nav bar directly beneath the header. */
            top: 180px; 
            width: 100%;
            /* Position slightly below the header. */
            z-index: 999;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            cursor: pointer;
        }

        nav a {
            /* Remove the default underline. */
            text-decoration: none;
            /* Set the link color to the dark blue-grey. */
            color: #2E3A4C;
            font-size: 38px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        nav a.active {
            /* Highlight the active link with the brand green color. */
            color: #1DB959;
            font-weight: 700;
        }

        .dropdown-icon {
            width: 45px;
            height:45px;
            margin-top: 3px;
        }

        /* Container for the main feed content */
        .content {
            /* Limit the content width and center it on the page. */
            max-width: 900px;
            margin: 0 auto;
            padding: 50px 20px;
        }
        /* Spacing for a single community post */
        .post {
            margin-bottom: 60px;
        }
        .post-title {
            /* Use the brand green for the post title. */
            color: #1DB959;
            font-weight: 700;
            font-size: 55px;
            margin-bottom: 10px;
        }
        .post-caption {
            font-size: 40px;
            /* Use white text to stand out against the dark background. */
            color: #FFFFFF;
            margin: 10px 0;
            /* Ensure the text wraps within the container. */
            max-width: 100%;
            word-wrap: break-word;
        }
        .post-link {
            /* Style the link with a green border. */
            border: 2px solid #1DB959;
            border-radius: 25px;
            padding: 20px 20px;
            display: inline-block;
            /* Use the brand green for the link text. */
            color: #1DB959;
            font-size: 35px;
            text-decoration: underline;
            max-width: 100%;
            word-wrap: break-word;
            /* Use a slightly lighter dark background for the link box. */
            background-color: #384558;
            margin-top: 15px;
        }
        .timestamp {
            /* Use a subtle gray for the author and time text. */
            color: #A3ACB5;
            font-size: 30px;
            margin-top: 25px;
        }
        .separator {
            /* A line to visually separate posts. */
            width: 80%;
            margin: 40px auto;
            border-bottom: 1px solid #A3ACB5;
        }

        /* Styling for the fixed button at the bottom of the screen */
        .add-post-button {
            position: fixed;
            /* Center the button horizontally. */
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            /* Use the brand green background and white text. */
            background-color: #1DB959;
            color: white;
            border: none;
            border-radius: 50px;
            width: 90%;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.5);
            cursor: pointer;
            font-size: 45px;
            font-weight: 500;
            /* Ensure it appears above all other content. */
            z-index: 1001;
            /* Add a smooth transition for the hover effect. */
            transition: background-color 0.3s;
        }
        .add-post-button:hover {
            /* Change color slightly on hover for feedback. */
            background-color: #0592d1;
        }
</style>
</head>

<body>
<header>
    <img src="assets/icons/DragonStone_logo.png" alt="DragonStone Logo" class="header-logo">
</header>

<nav>
    <div class="nav-item">
        <a href="index.php">Shop</a>
    </div>
    <div class="nav-item active">
        <a href="#" class="active">Community</a>
    </div>
    <div class="nav-item">
        <a href="profile.php">Profile</a>
    </div>
</nav>

<div class="content">

<?php
// Verify that the query succeeded and returned posts.
if ($result && $result->num_rows > 0) {
    // Start looping through the set of posts one by one.
    while ($row = $result->fetch_assoc()) {
        
        // Clean and prepare all post data for display to prevent code injection.
        $title = htmlspecialchars($row['post_title']);
        $caption = htmlspecialchars($row['post_caption']);
        $link = htmlspecialchars($row['post_link']);
        $firstName = htmlspecialchars($row['first_name']);
        // Clean and prepare the last name, ensuring no extra whitespace.
        $lastName = htmlspecialchars(trim($row['last_name']));
        
        // Take the post time from the database and format it nicely for the user.
        $timestamp = strtotime($row['posted_at']);
        // Format the time as "Hour:Minute, Month Day, Year" or show an error if parsing failed.
        $timeFormatted = $timestamp ? date("H:i, F jS, Y", $timestamp) : 'Unknown date';

        // Start creating the HTML structure for the post.
        echo "
        <div class='post'>
            <div class='post-title'>{$title}</div>
            <div class='post-caption'>{$caption}</div>";
            
        // Only display the link element if the link field in the database is not empty.
        if (!empty($link)) {
            // Check if the link is a properly formatted URL; if not, use a dummy hash link.
            $displayLink = filter_var($link, FILTER_VALIDATE_URL) ? $link : '#';
            // Output the link, opening it in a new tab for convenience and security.
            echo "<a href='{$displayLink}' class='post-link' target='_blank' rel='noopener noreferrer'>{$link}</a>";
        }
        
        // Display the author's name and the formatted time of posting.
        echo "
            <div class='timestamp'>Posted by {$firstName} {$lastName} — {$timeFormatted}</div>
            <div class='separator'></div>
        </div>";
    }
} else {
    // Show this message if the database query returned zero posts.
    echo "<p style='text-align:center; color:#A3ACB5; font-size:45px; margin-top: 100px;'>
            No posts yet. Be the first to share something!
          </p>";
}

// Release the memory that was used to store the database results.
if (isset($result) && is_object($result)) {
    $result->free();
}

// Close the connection to the database to free up resources.
$conn->close();
?>

</div>

<button class="add-post-button" onclick="window.location.href='add_post.php'">
    <span>+</span> Add Post
</button>
</body>
</html>