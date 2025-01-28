<?php
include 'db.php'; // Include your database connection file

// Handle Update Quantity
if (isset($_POST['update_update_btn'])) {
    $update_value = intval($_POST['update_quantity']);
    $update_id = intval($_POST['update_quantity_id']);

    // Update quantity in the database
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE product_id = ?");
    $stmt->bind_param("ii", $update_value, $update_id);
    if ($stmt->execute()) {
        $_SESSION['cart'][$update_id]['quantity'] = $update_value; // Sync with session cart
    }
    $stmt->close();
    header('Location: cart.php');
    exit();
}

// Handle Remove Item
if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);

    // Remove item from the database
    $stmt = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
    $stmt->bind_param("i", $remove_id);
    $stmt->execute();
    $stmt->close();

    // Remove item from the session cart
    unset($_SESSION['cart'][$remove_id]);

    header('Location: cart.php');
    exit();
}

// Handle Delete All Items
if (isset($_GET['delete_all'])) {
    // Clear cart in the database
    $conn->query("DELETE FROM cart");

    // Clear session cart
    unset($_SESSION['cart']);

    header('Location: cart.php');
    exit();
}

// Fetch cart items from the database
$result = $conn->query("SELECT * FROM cart");
$cart_items = $result->fetch_all(MYSQLI_ASSOC);

// Sync session cart with the database if available
if (!empty($cart_items)) {
    $_SESSION['cart'] = [];
    foreach ($cart_items as $item) {
        $_SESSION['cart'][$item['product_id']] = [
            'name' => $item['name'],
            'price' => $item['price'],
            'quantity' => $item['quantity']
        ];
    }
}

// Calculate grand total
$grand_total = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $grand_total += $item['price'] * $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <title>Your Cart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #333;
            color: #fff;
            padding: 15px;
            text-align: center;
        }

        header nav a {
            color: #fff;
            margin: 0 15px;
            text-decoration: none;
        }

        main {
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        table th {
            background-color: #f4f4f4;
        }

        form input[type="number"] {
            width: 60px;
            text-align: center;
        }

        .btn {
            padding: 8px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 0;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <header>
        <h1>Your Cart</h1>
        <nav>
            <a href="product.php">Products</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <main>
        <?php if (!empty($_SESSION['cart'])): ?>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $item_id => $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="update_quantity_id" value="<?php echo $item_id; ?>">
                                    <input type="number" name="update_quantity" value="<?php echo $item['quantity']; ?>" min="1" required>
                                    <button type="submit" name="update_update_btn" class="btn">Update</button>
                                </form>
                            </td>
                            <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this item?');">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3"><strong>Grand Total</strong></td>
                        <td colspan="2"><strong>$<?php echo number_format($grand_total, 2); ?></strong></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-center">
                            <a href="cart.php?delete_all" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete all items?');">Delete All</a>
                        </td>
                    </tr>
                </tfoot>
            </table>
            <form method="post">
                <button type="submit" class="btn"><a href="orders.php">Proceed to Payment</a></button>
            </form>
        <?php else: ?>
            <p>Your cart is empty. <a href="product.php" class="btn">Go back to products</a>.</p>
        <?php endif; ?>
    </main>
</body>

</html>