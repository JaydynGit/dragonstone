<?php
// Include the script that handles the database connection.
include 'db_connect.php';
// Start the session if one hasn't been initialized.
if (session_status() === PHP_SESSION_NONE) session_start();

// Ensure the user is logged in before proceeding.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// Fetch user's current EcoPoints balance.
$stmt = $conn->prepare("SELECT ecopoints FROM users WHERE users_id = ?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
// Safely cast the user's points to an integer.
$user_ecopoints = (int)($user['ecopoints'] ?? 0);

// Load cart items and quantities from the session.
$cart = $_SESSION['cart'] ?? [];
$quantities = $_SESSION['cart_quantities'] ?? [];

// Initialize variables for processing the cart.
$items = [];
$subtotal = 0.00;

// Process cart items if the cart is not empty.
if (!empty($cart)) {
    // Prepare a safe SQL string of product IDs for the database query.
    $ids_sql = "'" . implode("','", array_map([$conn, 'real_escape_string'], $cart)) . "'";
    // Query the database to get details for all cart products.
    $r = $conn->query("SELECT product_id, product_name, product_price, image_url, stock_quantity FROM products WHERE product_id IN ($ids_sql)");
    
    // Loop through the results to build the checkout summary.
    while ($row = $r->fetch_assoc()) {
        $pid = $row['product_id'];
        $qty = $quantities[$pid] ?? 1;
        // Calculate the subtotal for the current line item.
        $line_sub = $row['product_price'] * $qty;
        
        // Store item details in an array.
        $items[] = [
            'product_id' => $pid,
            'name' => $row['product_name'],
            'price' => $row['product_price'],
            'image' => $row['image_url'],
            'qty' => $qty,
            'line_sub' => $line_sub
        ];
        // Add the line item subtotal to the grand subtotal.
        $subtotal += $line_sub;
    }
}

// Calculate shipping cost, which is 10 percent of the subtotal.
$shipping = round($subtotal * 0.10, 2);

/**
 * Generates a unique order reference number.
 * @param mysqli $conn The database connection object.
 * @return string The new, formatted order reference.
 */
function generateOrderRef($conn) {
    // Fetch the largest existing order reference number.
    $res = $conn->query("SELECT order_ref FROM orders ORDER BY order_id DESC LIMIT 1");
    // Determine the next number in the sequence.
    if ($row = $res->fetch_assoc()) {
        // Extract the numerical part of the last reference.
        $last = intval(substr($row['order_ref'], 3));
        $new = $last + 1;
    } else $new = 1;
    // Format the new reference: "ORD" followed by the padded number.
    return "ORD" . str_pad($new, 6, "0", STR_PAD_LEFT);
}

// Initialize variable for displaying a notice or error.
$notice = "";

// Check if the order confirmation form has been submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    // Determine if the user chose to apply EcoPoints.
    $use_ecopoints = $_POST['use_ecopoints'] === 'yes';
    // Get the amount of EcoPoints the user attempted to use.
    $ecopoints_to_use = intval($_POST['ecopoints_amount'] ?? 0);

    // Validate that the user has enough EcoPoints to cover the requested amount.
    if ($use_ecopoints && $ecopoints_to_use > $user_ecopoints) {
        $notice = "You do not have these EcoPoints.";
    } else {
        // Calculate the monetary value of the EcoPoints used (100 points equals 1 Rand).
        $ecopoints_value = $use_ecopoints ? ($ecopoints_to_use / 100.0) : 0;
        // Calculate the new subtotal after applying the EcoPoints discount.
        $total_before_shipping = max(0.0, $subtotal - $ecopoints_value);
        // Recalculate shipping and final total (for safety, even though they were calculated above).
        $shipping = round($subtotal * 0.10, 2);
        $total = $total_before_shipping + $shipping;

        // Generate a new, unique order reference number.
        $order_ref = generateOrderRef($conn);
        // Prepare the SQL statement to insert the new order record.
        $stmt = $conn->prepare("INSERT INTO orders (order_ref, users_id, status, subtotal, shipping, ecopoints_used, ecopoints_value, total) 
                                 VALUES (?, ?, 'pending', ?, ?, ?, ?, ?)");
        // Bind all the parameters for the orders table insertion.
        $stmt->bind_param("ssddidd", $order_ref, $user_id, $subtotal, $shipping, $ecopoints_to_use, $ecopoints_value, $total);

        // Execute the order insertion.
        if (!$stmt->execute()) {
            // Display an error if the order record could not be created.
            $notice = "Error creating order: " . $stmt->error;
        } else {
            // Get the auto generated ID of the new order record.
            $order_id = $stmt->insert_id;
            // Prepare a statement to insert the individual items for the order.
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
            
            // Loop through each product item in the cart.
            foreach ($items as $it) {
                // Bind parameters for the order item insertion.
                $stmt_item->bind_param("issdii", $order_id, $it['product_id'], $it['name'], $it['price'], $it['qty'], $it['line_sub']);
                // Execute the item insertion.
                $stmt_item->execute();
            }

            // Update the user's EcoPoints balance in the database.
            if ($use_ecopoints && $ecopoints_to_use > 0) {
                // Subtract the used points from the user's balance.
                $conn->query("UPDATE users SET ecopoints = ecopoints - $ecopoints_to_use WHERE users_id = '$user_id'");
            }
            // Calculate new EcoPoints earned from the order total (one point per Rand spent, rounded down).
            $earned = floor($total);
            if ($earned > 0) {
                // Add the earned points to the user's balance.
                $conn->query("UPDATE users SET ecopoints = ecopoints + $earned WHERE users_id = '$user_id'");
            }

            // Clear the cart from the session after successful order placement.
            $_SESSION['cart'] = [];
            $_SESSION['cart_quantities'] = [];
            
            // Redirect the user to their orders history with a success reference.
            header("Location: customer_orders.php?created=" . urlencode($order_ref));
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Checkout - DragonStone</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
<style>
body {
    font-family:'Montserrat',sans-serif;
    margin:0;
    /* Add padding for the fixed header */
    padding-top:220px;
    background:#2E3A4C;
    color:#FFFFFF;
    font-size: 1.5rem;
}
.header {
    position:fixed;
    top:0;
    width:100%;
    /* Set header height to a consistent 200px */
    height:200px;
    background:#1DB959;
    display:flex;
    align-items:center;
    padding:0 40px;
    color:#fff;
    z-index:1000;
}
.header img { height:130px; }
.container { max-width:1000px; margin:40px auto; padding:20px; }
.item {
    display:flex;
    gap:30px;
    border-bottom:1px solid #455366;
    padding:20px 0;
    align-items:center;
}
.item img {
    width:180px;
    height:180px;
    border-radius:20px;
    object-fit:cover;
}
.summary {
    margin-top:30px;
    padding:20px;
    border:3px solid #1DB959;
    border-radius:25px;
    background:#384558;
    font-size:1.4rem;
}
.row {
    display:flex;
    justify-content:space-between;
    padding:10px 0;
}
.button {
    background:#1DB959;
    color:#fff;
    border:none;
    padding:25px 40px;
    border-radius:50px;
    font-size:2rem;
    cursor:pointer;
    display:block;
    width:100%;
    margin-top:20px;
    text-align:center;
}
.button:hover { background:#18A14D; }
.notice { color:#FF5C5C; font-weight:600; margin-top:20px; font-size:1.4rem; }

/* PAYMENT POPUP */
/* Full screen overlay for dimming the background */
#overlay {
    display:none;
    position:fixed;
    top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.6);
    z-index:1500;
}
/* The actual payment method selection box */
.popup {
    position:fixed;
    top:50%; left:50%;
    transform:translate(-50%,-50%);
    width:90%;
    max-width:700px;
    background:#2E3A4C;
    border-radius:30px;
    box-shadow:0 8px 25px rgba(0,0,0,0.3);
    padding:40px;
    display:none;
    z-index:2000;
    text-align:center;
}
.popup h3 { font-size:2.5rem; color:#1DB959; margin-bottom:25px; }
.pay-icons img {
    width:200px; height:130px;
    object-fit:contain;
    margin:15px;
    cursor:pointer;
    opacity:0.6;
    border:4px solid transparent;
    border-radius:20px;
    transition:all 0.3s ease;
}
.pay-icons img.active {
    opacity:1;
    border-color:#1DB959;
    transform:scale(1.05);
}
.small { color:#A3ACB5; font-size:1.2rem; }

input, label { font-size:1.5rem; }
input[type="number"] {
    color:#fff;
    background:#455366;
    border:2px solid #1DB959;
    border-radius:10px;
    padding:15px;
    width:250px;
}
</style>
</head>
<body>
<div class="header">
    <a href="cart.php"><img src="assets/icons/back_icon.png" alt="Back" style="margin-right:20px;"></a>
    <img src="assets/icons/DragonStone_checkout.png" alt="Checkout">
</div>

<div class="container">
    
    <?php if (empty($items)): ?>
        <p>Your cart is empty. <a href="index.php">Continue Shopping</a></p>
    <?php else: ?>
        <?php foreach ($items as $it): ?>
        <div class="item">
            <img src="<?= htmlspecialchars($it['image']); ?>" alt="<?= htmlspecialchars($it['name']); ?>">
            <div>
                <div style="font-size:2rem; font-weight:700;"><?= htmlspecialchars($it['name']); ?></div>
                <div>Price: R<?= number_format($it['price'],2); ?></div>
                <div>Qty: <?= (int)$it['qty']; ?></div>
                <div>Item Total: R<?= number_format($it['line_sub'],2); ?></div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="summary">
            <div class="row"><div>Subtotal</div><div>R<?= number_format($subtotal,2); ?></div></div>
            <div class="row"><div>Shipping (10 percent)</div><div>R<?= number_format($shipping,2); ?></div></div>
            <hr>

            <form method="POST">
                <div style="margin-top:10px; font-weight:600;">EcoPoints (You have <?= $user_ecopoints; ?>)</div>
                <div class="small">100 EcoPoints equals R1.00</div>
                <label><input type="radio" name="use_ecopoints" value="no" checked> No</label>
                <label><input type="radio" name="use_ecopoints" value="yes"> Yes</label>

                <div id="ecoAmountWrap" style="display:none; margin-top:15px;">
                    <input type="number" name="ecopoints_amount" id="ecopoints_amount" placeholder="Enter points">
                    <div id="ecoMsg" class="small"></div>
                </div>

                <button type="button" class="button" id="choosePaymentBtn">Choose Payment</button>

                <div id="totalsBlock" style="margin-top:20px;">
                    <div class="row"><div>Subtotal</div><div>R<?= number_format($subtotal,2); ?></div></div>
                    <div class="row"><div>EcoPoints Applied</div><div id="epApplied">R0.00</div></div>
                    <div class="row"><div>Shipping</div><div>R<?= number_format($shipping,2); ?></div></div>
                    <hr>
                    <div class="row" style="font-weight:700;"><div>Total</div><div id="totalFinal">R<?= number_format($subtotal + $shipping,2); ?></div></div>
                </div>

                <button type="submit" name="confirm_order" class="button">Confirm & Pay</button>
            </form>
        </div>

        <?php if ($notice): ?><div class="notice"><?= $notice; ?></div><?php endif; ?>
    <?php endif; ?>
</div>

<div id="overlay"></div>
<div id="payPopup" class="popup">
    <h3>Select Your Payment Method</h3>
    <div class="pay-icons">
        <img id="apay" src="assets/icons/apay_icon.png" alt="Apple Pay" onclick="selectPay('apay')">
        <img id="gpay" src="assets/icons/gpay_icon.png" alt="Google Pay" onclick="selectPay('gpay')">
    </div>
    <button onclick="closePay()" class="button" style="margin-top:30px;">Confirm Selection</button>
</div>

<script>
const ecoWrap=document.getElementById('ecoAmountWrap');
const ecoInput=document.getElementById('ecopoints_amount');
const epApplied=document.getElementById('epApplied');
const totalFinal=document.getElementById('totalFinal');
// PHP variables passed into JavaScript for calculations
const subtotal=<?= json_encode($subtotal); ?>;
const shipping=<?= json_encode($shipping); ?>;
const userPoints=<?= json_encode($user_ecopoints); ?>;

// Event listener for the EcoPoints radio buttons
document.querySelectorAll('input[name="use_ecopoints"]').forEach(r=>{
    r.addEventListener('change',()=>{
        if(r.value==='yes'){
            // Show the points input field and trigger the calculation
            ecoWrap.style.display='block';
            ecoInput.dispatchEvent(new Event('input'));
        }
        else{
            // Hide the points input and reset total display
            ecoWrap.style.display='none';
            epApplied.textContent="R0.00";
            totalFinal.textContent="R"+(subtotal+shipping).toFixed(2);
        }
    });
});

// Event listener for changes in the EcoPoints input field
ecoInput.addEventListener('input',()=>{
    const v=parseInt(ecoInput.value||0);
    const msg=document.getElementById('ecoMsg');
    
    // Check if entered points exceed available points
    if(v>userPoints){
        msg.textContent="You do not have these points";
        msg.style.color='red';
    }
    else{
        msg.textContent="EcoPoints Applied";
        msg.style.color='green';
    }
    
    // Determine the actual points to use (cannot exceed available points)
    const appliedPoints=Math.max(0,Math.min(v,userPoints));
    // Calculate the rand value of the applied points
    const appliedRand=appliedPoints/100.0;
    
    // Update the EcoPoints Applied row
    epApplied.textContent="R"+appliedRand.toFixed(2);
    
    // Calculate the final total after discount and shipping
    const tot=Math.max(0,subtotal-appliedRand)+shipping;
    totalFinal.textContent="R"+tot.toFixed(2);
});

// Logic to open and close the payment method popup
const overlay=document.getElementById('overlay');
const popup=document.getElementById('payPopup');
document.getElementById('choosePaymentBtn').addEventListener('click',()=>{
    overlay.style.display='block';
    popup.style.display='block';
});
function closePay(){
    overlay.style.display='none';
    popup.style.display='none';
}
// Function to visually select a payment method icon
function selectPay(id){
    document.querySelectorAll('.pay-icons img').forEach(i=>i.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}
</script>
</body>
</html>