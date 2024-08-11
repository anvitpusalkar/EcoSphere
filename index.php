<?php
session_start(); 

require_once 'config.php';

$conn = getDbConnection();
$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM products";
if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $query .= " WHERE name LIKE '%$search%' OR description LIKE '%$search%'";
}
$result = $conn->query($query);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $session_id = session_id();

    $cart_query = "SELECT id FROM carts WHERE session_id = '$session_id'";
    $cart_result = $conn->query($cart_query);

    if ($cart_result->num_rows == 0) {
        $conn->query("INSERT INTO carts (session_id) VALUES ('$session_id')");
        $cart_id = $conn->insert_id;
    } else {
        $cart_row = $cart_result->fetch_assoc();
        $cart_id = $cart_row['id'];
    }

    $cart_item_query = "SELECT id FROM cart_items WHERE cart_id = $cart_id AND product_id = $product_id";
    $cart_item_result = $conn->query($cart_item_query);

    if ($cart_item_result->num_rows == 0) {
        $conn->query("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES ($cart_id, $product_id, 1)");
    } else {
        $conn->query("UPDATE cart_items SET quantity = quantity + 1 WHERE cart_id = $cart_id AND product_id = $product_id");
    }

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ecosphere.in</title>
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
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-bar {
            display: flex;
        }

        .search-bar input {
            padding: 0.75rem;
            border: 1px solid #ced4da;
            border-radius: 5px 0 0 5px;
            width: 300px;
        }

        .search-bar button {
            padding: 0.75rem 1.25rem;
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-bar button:hover {
            background-color: #1d4ed8;
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
            max-width: 1200px;
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

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-card img {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .product-card h3 {
            margin: 0.5rem 0;
            color: #1e3a8a;
        }

        .product-card p {
            margin: 0.5rem 0;
            font-weight: bold;
            color: #2563eb;
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
            margin-top: 1rem;
        }

        .btn:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <h1>EcoSphere</h1>
            <form class="search-bar" action="index.php" method="GET">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
            </form>
        </div>
    </header>
    <nav>
        <a href="index.php">Products</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
        <a href="cart.php">Cart</a>
    	<?php if(isLoggedIn()): ?>
        	<a href="logout.php">Logout</a>
    	<?php else: ?>
        	<a href="login.php">Login</a>
    	<?php endif; ?>
    </nav>
    <div class="container">
        <h2>Exciting Deals!</h2>
        <div class="product-grid">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['image_url']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>₹<?php echo number_format($row['price'], 2); ?></p>
                        <form action="index.php" method="POST">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <button type="submit" name="add_to_cart" class="btn">Add to Cart</button>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No products found.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>