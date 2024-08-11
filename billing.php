<?php
session_start();

require_once 'config.php';

$conn = getDbConnection();
$session_id = session_id();

$cart_query = "SELECT id FROM carts WHERE session_id = '$session_id'";
$cart_result = $conn->query($cart_query);

if ($cart_result->num_rows > 0) {
    $cart_row = $cart_result->fetch_assoc();
    $cart_id = $cart_row['id'];

    $cart_items_query = "
        SELECT products.name, products.price, cart_items.quantity 
        FROM cart_items 
        JOIN products ON cart_items.product_id = products.id 
        WHERE cart_items.cart_id = $cart_id";
    $cart_items_result = $conn->query($cart_items_query);
} else {
    $cart_items_result = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoSphere - Billing</title>
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

        form {
            display: grid;
            gap: 1rem;
        }

        label {
            font-weight: bold;
        }

        input, textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 5px;
        }

        .btn {
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .btn:disabled {
            background-color: #9ca3af;
            cursor: not-allowed;
        }

        .order-summary {
            background-color: #f0f5f9;
            border-radius: 5px;
            padding: 1rem;
            margin-top: 1rem;
        }
    </style>
    <script>
        function enablePlaceOrderButton() {
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const address = document.getElementById('address').value;

            const placeOrderButton = document.getElementById('placeOrderButton');

            if (name && email && address) {
                placeOrderButton.disabled = false;
            } else {
                placeOrderButton.disabled = true;
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>EcoSphere - Billing</h1>
        </div>
    </header>
    <nav>
        <a href="index.php">Home</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="cart.php">Cart</a>
    </nav>
    <div class="container">
        <h2>Billing Details</h2>
        <form action="order_confirmation.php" method="POST">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" oninput="enablePlaceOrderButton()" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" oninput="enablePlaceOrderButton()" required>

            <label for="address">Address:</label>
            <textarea id="address" name="address" oninput="enablePlaceOrderButton()" required></textarea>

            <h3>Order Summary</h3>
            <div class="order-summary">
                <?php
                if ($cart_items_result && $cart_items_result->num_rows > 0) {
                    $total = 0;
                    while ($row = $cart_items_result->fetch_assoc()) {
                        $total += $row['price'] * $row['quantity'];
                        ?>
                        <p><?php echo htmlspecialchars($row['name']); ?> x <?php echo $row['quantity']; ?> - ₹<?php echo number_format($row['price'] * $row['quantity'], 2); ?></p>
                        <?php
                    }
                    echo "<h3>Total: ₹" . number_format($total, 2) . "</h3>";
                } else {
                    echo "<p>Your cart is empty.</p>";
                }
                ?>
            </div>
            <button type="submit" id="placeOrderButton" class="btn" disabled>Place Order</button>
        </form>
    </div>
</body>
</html>
<?php
$conn->close();
?>