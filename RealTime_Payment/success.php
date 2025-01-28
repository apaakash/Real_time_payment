<?php
include 'db.php';

// Redirect to login page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <title>Success</title>
</head>
<body>
    <main>
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>You are successfully logged in.</p>
        <a href="index.php">Home page</a>
        <a href="logout.php">Logout</a>
    </main>
</body>
</html>
