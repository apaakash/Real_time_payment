<?php
require 'vendor/autoload.php';

use Razorpay\Api\Api;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

// Replace with your Razorpay Key ID and Key Secret
$keyId = 'rzp_test_oFL88BWKa4IHEK';
$keySecret = '3882JZruONDSLmVc7eG9scyM';

$api = new Api($keyId, $keySecret);

// Check if necessary POST data is set
if (!isset($_POST['razorpay_payment_id'], $_POST['amount'], $_POST['name'], $_POST['address'], $_POST['email'], $_POST['phone'])) {
    $error = "Payment ID or required details are missing!";
    echo $error;
    file_put_contents('payment.log', date('Y-m-d H:i:s') . " - Error: " . $error . "\n", FILE_APPEND);
    exit;
}

// Get the payment details
$razorpayPaymentId = $_POST['razorpay_payment_id'];
$amount = $_POST['amount']; // Amount in paisa
$name = $_POST['name'];
$address = $_POST['address'];
$email = $_POST['email'];
$phone = $_POST['phone'];

try {
    // Fetch the payment object
    $payment = $api->payment->fetch($razorpayPaymentId);

    // Capture the payment
    if ($payment->status === 'authorized') {
        $payment->capture(['amount' => $amount]);

        // Payment successfully captured
        $success = "Payment successful!";
        echo $success;
        file_put_contents('payment.log', date('Y-m-d H:i:s') . " - Success: Payment successful: " . $razorpayPaymentId . "\n", FILE_APPEND);

        // Send email to user with order details
        $mail = new PHPMailer(true);
        try {
            //Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Use Gmail's SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'demowork10001@gmail.com'; // Replace with your email
            $mail->Password = 'ahzkmvqzvvmhklok'; // Replace with your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            //Recipients
            $mail->setFrom('demowork10001@gmail.com', 'E-commerce Website');
            $mail->addAddress($email, $name); // Add user's email address here

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Order Confirmation - Payment Successful';
            $mail->Body = "
                <h2>Thank you for your purchase, $name!</h2>
                <p>Your payment has been successfully processed. Here are your order details:</p>
                <ul>
                    <li><strong>Order ID:</strong> $razorpayPaymentId</li>
                    <li><strong>Amount:</strong> ₹" . number_format($amount / 100, 2) . "</li>
                    <li><strong>Address:</strong> $address</li>
                    <li><strong>Phone:</strong> $phone</li>
                    <li><strong>Email:</strong> $email</li>
                    <li><strong>Products:</strong> $product_names</li>
                    <li><strong>Quantity:</strong> $quantity</li>
                    <li><strong>Order ID:</strong> $order_id</li>
                </ul>
                <p>Thank you for shopping with us!</p>";

            $mail->send();
            file_put_contents('payment.log', date('Y-m-d H:i:s') . " - Success: Email sent to user\n", FILE_APPEND);
        } catch (Exception $e) {
            file_put_contents('payment.log', date('Y-m-d H:i:s') . " - Error: Email could not be sent. Mailer Error: {$mail->ErrorInfo}\n", FILE_APPEND);
        }

        // Clear cart after payment
        if (isset($_SESSION['cart'])) {
            unset($_SESSION['cart']); // Clear all cart items
        }
    } else {
        throw new Exception("Payment not authorized.");
    }
} catch (\Exception $e) {
    // Payment failed
    $error = "Payment failed: " . $e->getMessage();
    echo $error;
    file_put_contents('payment.log', date('Y-m-d H:i:s') . " - Error: " . $error . "\n", FILE_APPEND);
}
