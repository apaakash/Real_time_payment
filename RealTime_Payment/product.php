<?php
include 'db.php'; // Ensure the session is started

// Initialize the cart session if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Sync session with the database cart table
function syncCartWithDatabase($conn)
{
    $_SESSION['cart'] = [];
    $cart_query = $conn->query("SELECT * FROM cart");
    while ($row = $cart_query->fetch_assoc()) {
        $_SESSION['cart'][$row['product_id']] = [
            'name' => $row['name'],
            'price' => $row['price'],
            'quantity' => $row['quantity']
        ];
    }
}

// Call the sync function
syncCartWithDatabase($conn);

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];

    // Check if the product already exists in the cart
    if (!array_key_exists($product_id, $_SESSION['cart'])) {
        // Fetch product details
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1
            ];

            // Insert product into the cart table
            $stmt_insert = $conn->prepare("INSERT INTO cart (product_id, name, price, quantity) VALUES (?, ?, ?, ?)
                                        ON DUPLICATE KEY UPDATE quantity = quantity + 1");
            $stmt_insert->bind_param("isdi", $product_id, $product['name'], $product['price'], $_SESSION['cart'][$product_id]['quantity']);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    } else {
        // If the product is already in the cart, increase the quantity in the session
        $_SESSION['cart'][$product_id]['quantity'] += 1;

        // Update the quantity in the cart table
        $stmt_update = $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE product_id = ?");
        $stmt_update->bind_param("i", $product_id);
        $stmt_update->execute();
        $stmt_update->close();
    }

    header('Location: product.php'); // Redirect to avoid form resubmission
    exit();
}

// Fetch all products
$query = "SELECT * FROM products";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <title>Products</title>
</head>

<body>
    <header>
        <h1>Our Products</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="cart.php">Cart (<?php echo array_sum(array_column($_SESSION['cart'], 'quantity')); ?>)</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($product = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                        <p><?php echo htmlspecialchars($product['description']); ?></p>
                        <p><strong>$<?php echo number_format($product['price'], 2); ?></strong></p>
                        <form method="post" action="">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit">Add to Cart</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products available.</p>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>