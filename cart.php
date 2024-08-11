<?php
session_start();
require_once 'config.php';

$conn = getDbConnection();
$session_id = session_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove_from_cart'])) {
        $cart_item_id = intval($_POST['cart_item_id']);
        $remove_query = "DELETE FROM cart_items WHERE id = $cart_item_id";
        $conn->query($remove_query);
    }
}

$cart_query = "SELECT id FROM carts WHERE session_id = '$session_id'";
$cart_result = $conn->query($cart_query);

if ($cart_result->num_rows > 0) {
    $cart_row = $cart_result->fetch_assoc();
    $cart_id = $cart_row['id'];
    $cart_items_query = "
        SELECT cart_items.id AS cart_item_id, products.name, products.price, cart_items.quantity 
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
    <title>EcoSphere - Cart</title>
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
            color: #fff;
            padding: 1rem;
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
            padding: 0.5rem;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            padding: 0.5rem 1rem;
        }
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .cart-item {
            background-color: #f0f5f9;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            background-color: #2563eb;
            color: #fff;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #1d4ed8;
        }
        .btn-remove {
            background-color: #ef4444;
        }
        .btn-remove:hover {
            background-color: #dc2626;
        }
        .total {
            font-size: 1.2rem;
            font-weight: bold;
            text-align: right;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>EcoSphere - Cart</h1>
        </div>
    </header>
    <nav>
        <a href="index.php">Products</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="cart.php">Cart</a>
    </nav>
    <div class="container">
        <h2>Your Cart</h2>
        <?php
        if ($cart_items_result && $cart_items_result->num_rows > 0) {
            $total = 0;
            while ($row = $cart_items_result->fetch_assoc()) {
                $total += $row['price'] * $row['quantity'];
                ?>
                <div class="cart-item">
                    <div>
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>Price: ₹<?php echo number_format($row['price'], 2); ?></p>
                        <p>Quantity: <?php echo $row['quantity']; ?></p>
                    </div>
                    <form action="cart.php" method="POST">
                        <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                        <button type="submit" name="remove_from_cart" class="btn btn-remove">Remove</button>
                    </form>
                </div>
                <?php
            }
            echo "<div class='total'>Total: ₹" . number_format($total, 2) . "</div>";
            echo '<a href="billing.php" class="btn" style="display: inline-block; margin-top: 1rem;">Proceed to Checkout</a>';
        } else {
            echo "<p>Your cart is empty.</p>";
        }
        ?>
    </div>
</body>
</html>
<?php
$conn->close();
?>