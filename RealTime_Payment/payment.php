<?php
include "db.php";

// Get the order_id from the URL
$order_id = $_GET['order_id'] ?? null;

// Check if order_id is provided and valid
if (!$order_id || !is_numeric($order_id)) {
    echo "<p>Invalid or missing order ID. Please go back to the <a href='orders.php'>Orders page</a>.</p>";
    exit();
}

// Fetch order details from the database
$sql = "SELECT * FROM orders WHERE id = $order_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "<p>Order not found. Please go back to the <a href='orders.php'>Orders page</a>.</p>";
    exit();
}

// Fetch the order data
$order = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Bill</title>
    <style>
        .center{
            display: flex;
            justify-content: center;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body>
    <h2 class="center">Order Details</h2>
    <main class="center">
        
        <table border="1" cellpadding="10" cellspacing="0">
            <tr>
                <th>Order ID</th>
                <td><?php echo $order['id']; ?></td>
            </tr>
            <tr>
                <th>Name</th>
                <td><?php echo htmlspecialchars($order['name']); ?></td>
            </tr>
            <tr>
                <th>Address</th>
                <td><?php echo htmlspecialchars($order['address']); ?></td>
            </tr>
            <tr>
                <th>Email</th>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><?php echo htmlspecialchars($order['phone']); ?></td>
            </tr>
            <tr>
                <th>Products</th>
                <td><?php echo htmlspecialchars($order['product_names']); ?></td>
            </tr>
            <tr>
                <th>Quantity</th>
                <td><?php echo $order['quantity']; ?></td>
            </tr>
            <tr>
                <th>Grand Total</th>
                <td id="grand_total">₹<?php echo number_format($order['grand_total'], 2); ?></td>
            </tr>
            
        </table>

        
    </main>
    <!-- Payment Button -->
        <div style="margin-top: 20px;" class="center">
            <button id="pay-now" style="padding: 10px 20px; background-color: #28a745; color: white; border: none; cursor: pointer;">Pay Now</button>
        </div>

    <script>
    $('#pay-now').click(function(e) {
        var amount = <?php echo $order['grand_total'] * 100; ?>; // Convert to paisa
        var name = '<?php echo htmlspecialchars($order['name']); ?>';
        var address = '<?php echo htmlspecialchars($order['address']); ?>';
        var email = '<?php echo htmlspecialchars($order['email']); ?>';
        var phone = '<?php echo htmlspecialchars($order['phone']); ?>';
        var product_names = '<?php echo htmlspecialchars($order['product_names']); ?>';
        var quantity = <?php echo $order['quantity']; ?>;
        var order_id = <?php echo $order['id']; ?>;

        if (!name || !address || !email || !phone) {
            alert('Please fill all the fields.');
            return;
        }

        var options = {
            "key": "rzp_test_oFL88BWKa4IHEK", // Replace with your Razorpay Key ID
            "amount": amount,
            "currency": "INR",
            "name": "Datastore",
            "description": "Payment for your purchase",
            "image": "https://via.placeholder.com/150", // Replace with your logo URL
            "prefill": {
                "name": name,
                "email": email,
                "contact": phone
            },
            "theme": {
                "color": "#F37254"
            },
            "handler": function(response) {
                // AJAX call to process the payment
                $.ajax({
                    url: 'charge.php',
                    type: 'POST',
                    data: {
                        razorpay_payment_id: response.razorpay_payment_id,
                        amount: amount,
                        name: name,
                        address: address,
                        email: email,
                        phone: phone,
                        product_names: product_names,
                        quantity: quantity,
                        order_id: order_id
                    },
                    success: function() {
                        // Redirect to the homepage after successful payment
                        alert('Payment successful!');
                        window.location.href = 'product.php'; // Change 'index.php' to your homepage file
                    },
                    error: function() {
                        alert('Payment failed. Please try again.');
                    }
                });
            }
        };

        var rzp1 = new Razorpay(options);
        rzp1.open();
        e.preventDefault();
    });
</script>

</body>
</html>
