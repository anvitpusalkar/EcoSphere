<?php
session_start();

require_once 'config.php';
require_once 'send_email.php';

$conn = getDbConnection();
$session_id = session_id();

$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];

$cart_query = "SELECT id FROM carts WHERE session_id = '$session_id'";
$cart_result = $conn->query($cart_query);

$orderSummary = '';
$total = 0;

if ($cart_result->num_rows > 0) {
    $cart_row = $cart_result->fetch_assoc();
    $cart_id = $cart_row['id'];

    $cart_items_query = "
        SELECT products.name, products.price, cart_items.quantity 
        FROM cart_items 
        JOIN products ON cart_items.product_id = products.id 
        WHERE cart_items.cart_id = $cart_id";
    $cart_items_result = $conn->query($cart_items_query);

    while ($row = $cart_items_result->fetch_assoc()) {
        $subtotal = $row['price'] * $row['quantity'];
        $total += $subtotal;
        $orderSummary .= "<p>{$row['name']} x {$row['quantity']} - ₹" . number_format($subtotal, 2) . "</p>";
    }

    $conn->query("DELETE FROM cart_items WHERE cart_id = $cart_id");
}

$emailSent = sendOrderConfirmationEmail($email, $name, $orderSummary, number_format($total, 2));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoSphere - Order Confirmation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f5f9;
            color: #333;
        }

        header {
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 1.5rem;
            text-align: center;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        nav {
            background-color: #2563eb;
            padding: 1rem;
            text-align: center;
        }

        nav a {
            color: #ffffff;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            display: inline-block;
            transition: background-color 0.3s;
        }

        nav a:hover {
            background-color: #1d4ed8;
            border-radius: 5px;
        }

        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #1e3a8a;
        }

        .btn {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .order-summary {
            background-color: #f0f5f9;
            border-radius: 5px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .success-message {
            background-color: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }

        .error-message {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>EcoSphere - Order Confirmation</h1>
        </div>
    </header>
    <nav>
        <a href="index.php">Products</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="cart.php">Cart</a>
    </nav>
    <div class="container">
        <h2>Thank You for Your Order, <?php echo htmlspecialchars($name); ?>!</h2>
        <?php if ($emailSent): ?>
            <div class="success-message">
                <p>An order confirmation email has been sent to: <?php echo htmlspecialchars($email); ?></p>
            </div>
        <?php else: ?>
            <div class="error-message">
                <p>There was an issue sending the confirmation email. Please contact customer support.</p>
            </div>
        <?php endif; ?>
        <p>Your order will be shipped to: <?php echo htmlspecialchars($address); ?></p>

        <h3>Order Summary</h3>
        <div class="order-summary">
            <?php echo $orderSummary; ?>
            <h3>Total: ₹<?php echo number_format($total, 2); ?></h3>
        </div>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index.php" class="btn">Back to Home</a>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>