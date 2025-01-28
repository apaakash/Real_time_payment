<?php
include 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <title>Home</title>
</head>
<body>
    <header>
        <h1>Welcome to Our Website</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="success.php">Dashboard</a>
                <a href="product.php">products</a>
                <a href="orders.php">orders</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="register.php">Register</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?php if (isset($_SESSION['user_id'])): ?>
            <h2>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            <p>Explore the dashboard to see your personalized content and manage your profile.</p>
            <a href="success.php" class="btn">Go to Dashboard</a>
        <?php else: ?>
            <h2>Hello, Visitor!</h2>
            <p>Join us to access exclusive features and enjoy a personalized experience.</p>
            <div class="actions">
                <a href="register.php" class="btn">Register</a>
                <a href="login.php" class="btn">Login</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
