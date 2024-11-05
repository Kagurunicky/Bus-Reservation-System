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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Ticket - KBTBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <style>
        .ticket {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            background-color: white;
        }
        .ticket-header {
            text-align: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .ticket-content {
            margin-bottom: 20px;
        }
        .ticket-footer {
            text-align: center;
            border-top: 2px solid #ddd;
            padding-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            .ticket {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php 
            if(isset($_SESSION['success'])) {
                echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                        ' . $_SESSION['success'] . '
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                      </div>';
                unset($_SESSION['success']);
            }
        ?>
        <div class="ticket" id="ticket">
            <div class="ticket-header">
                <h2>KBTBS Bus Ticket</h2>
                <p>Your journey, our priority</p>
            </div>
            
            <div class="ticket-content">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Passenger Details</h5>
                        <p><strong>Name:</strong> <?php echo $booking['customer_name']; ?></p>
                        <p><strong>Phone:</strong> <?php echo $booking['customer_phone']; ?></p>
                        <p><strong>Booking ID:</strong> <?php echo $booking['booking_id']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h5>Journey Details</h5>
                        <p><strong>Route:</strong> <?php echo $booking['route_cities']; ?></p>
                        <p><strong>Date:</strong> <?php echo $booking['route_dep_date']; ?></p>
                        <p><strong>Time:</strong> <?php echo $booking['route_dep_time']; ?></p>
                        <p><strong>Seat No:</strong> <?php echo $booking['booked_seat']; ?></p>
                        <p><strong>Amount:</strong> KSH <?php echo $booking['booked_amount']; ?></p>
                    </div>
                </div>
            </div>

            <div class="ticket-footer">
            <p>Thank you for choosing KBTBS</p>
                        <p>Please carry this ticket for Boarding.</p>
                        <p>Please arrive at least 30 minutes before departure time.</p>
                        <p>NB:NO TICKET NO BOARDING .Terms and conditons apply</p>
            </div>
        </div>

        <div class="text-center mt-4 mb-4 no-print">
            <button onclick="window.print()" class="btn btn-primary">
                <i class="fas fa-print"></i> Print Ticket
            </button>
            <a href="download_ticket.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-success">
                <i class="fas fa-download"></i> Download PDF
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    }
?> 