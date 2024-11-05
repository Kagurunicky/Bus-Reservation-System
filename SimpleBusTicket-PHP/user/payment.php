<?php
    session_start();
    if(!isset($_SESSION["user"])) {
        header("location: login.php");
        exit();
    }

    require '../assets/partials/_functions.php';
    $conn = db_connect();    

    if(!$conn) 
        die("Connection Failed");

    if(isset($_GET['booking_id'])) {
        $booking_id = $_GET['booking_id'];
        
        // Get booking details
        $sql = "SELECT b.*, c.*, r.* 
                FROM bookings b 
                JOIN customers c ON b.customer_id = c.customer_id 
                JOIN routes r ON b.route_id = r.route_id 
                WHERE b.booking_id = '$booking_id'";
        $result = mysqli_query($conn, $sql);
        $booking = mysqli_fetch_assoc($result);

        // Format phone number to required format (254XXXXXXXXX)
        $phone = $booking['customer_phone'];
        // Remove any spaces or special characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Convert to international format if needed
        if(strlen($phone) == 10 && substr($phone, 0, 1) == "0") {
            $phone = "254" . substr($phone, 1);
        } elseif(strlen($phone) == 9) {
            $phone = "254" . $phone;
        }
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - KBTBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://unpkg.com/intasend-inlinejs-sdk@3.0.4/build/intasend-inline.js"></script>
    <style>
        .payment-card {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 5px;
            background-color: white;
        }
        .booking-summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        body {
            background-color: #f0f2f5;
        }
        .payment-status {
            display: none;
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php require 'user_header.php'; ?>

    <div class="container mt-4">
        <div class="payment-card">
            <h3 class="text-center mb-4">Complete Payment</h3>
            
            <?php if(isset($booking) && $booking) { ?>
                <div class="booking-summary">
                    <h5>Booking Summary</h5>
                    <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['booking_id']); ?></p>
                    <p><strong>Route:</strong> <?php echo htmlspecialchars($booking['route_cities']); ?></p>
                    <p><strong>Date:</strong> <?php echo htmlspecialchars($booking['route_dep_date']); ?></p>
                    <p><strong>Seat:</strong> <?php echo htmlspecialchars($booking['booked_seat']); ?></p>
                    <p><strong>Amount:</strong> KSH <?php echo htmlspecialchars($booking['booked_amount']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($phone); ?></p>
                </div>

                <div class="text-center">
                    <button class="intaSendPayButton btn btn-primary btn-lg"
                            data-amount="<?php echo htmlspecialchars($booking['booked_amount']); ?>"
                            data-currency="KES"
                            data-phone="<?php echo htmlspecialchars($phone); ?>"
                            data-email=""
                            data-first_name="<?php echo htmlspecialchars(explode(' ', $booking['customer_name'])[0]); ?>"
                            data-last_name="<?php echo htmlspecialchars(explode(' ', $booking['customer_name'])[1]); ?>"
                            data-country="KE"
                    >
                        Pay KSH <?php echo htmlspecialchars($booking['booked_amount']); ?> via M-Pesa
                    </button>
                </div>

                <div id="paymentStatus" class="payment-status alert">
                    <p class="mb-0" id="statusMessage"></p>
                </div>
            <?php } else { ?>
                <div class="alert alert-danger">
                    Booking information not found. Please try again or contact support.
                </div>
            <?php } ?>
        </div>
    </div>

    <script>
        // Initialize IntaSend
        const checkout = new window.IntaSend({
            publicAPIKey: "ISPubKey_test_868d79a9-4089-4569-9e05-87277177a03f", // Changed to test key
            live: false // Set to false for testing
        });

        // Add event listeners
        checkout.on("COMPLETE", (results) => {
            console.log("Payment completed:", results);
            showStatus('success', 'Payment completed successfully! Redirecting to ticket...');
            updatePaymentStatus(results.id, 'completed');
        });

        checkout.on("FAILED", (results) => {
            console.log("Payment failed:", results);
            showStatus('danger', 'Payment failed. Please try again.');
        });

        checkout.on("IN-PROGRESS", (results) => {
            console.log("Payment in progress:", results);
            showStatus('info', 'Payment in progress. Please check your phone and enter M-Pesa PIN.');
        });

        function showStatus(type, message) {
            const statusDiv = document.getElementById('paymentStatus');
            const statusMessage = document.getElementById('statusMessage');
            statusDiv.style.display = 'block';
            statusDiv.className = `payment-status alert alert-${type}`;
            statusMessage.textContent = message;
        }

        function updatePaymentStatus(transactionId, status) {
            fetch('update_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    booking_id: '<?php echo $booking_id; ?>',
                    transaction_id: transactionId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    setTimeout(() => {
                        window.location.href = 'ticket.php?booking_id=<?php echo $booking_id; ?>';
                    }, 2000);
                } else {
                    showStatus('danger', 'Error updating payment status. Please contact support.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showStatus('danger', 'Error updating payment status. Please contact support.');
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    }
?> 