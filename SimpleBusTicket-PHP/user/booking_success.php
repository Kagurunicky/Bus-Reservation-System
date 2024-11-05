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
        $sql = "SELECT b.*, r.*, c.* 
                FROM bookings b 
                JOIN routes r ON b.route_id = r.route_id 
                JOIN customers c ON b.customer_id = c.customer_id 
                WHERE b.booking_id = '$booking_id'";
        $result = mysqli_query($conn, $sql);
        $booking = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Success - KBTBS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php require 'user_header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                        <h2 class="mt-3">Booking Successful!</h2>
                        <p class="lead">Your ticket has been booked successfully.</p>
                        
                        <div class="alert alert-info mt-4">
                            <h5>Booking Details</h5>
                            <p><strong>Booking ID:</strong> <?php echo $booking['booking_id']; ?></p>
                            <p><strong>Route:</strong> <?php echo $booking['route_cities']; ?></p>
                            <p><strong>Date:</strong> <?php echo $booking['route_dep_date']; ?></p>
                            <p><strong>Time:</strong> <?php echo $booking['route_dep_time']; ?></p>
                            <p><strong>Seats:</strong> <?php echo $booking['booked_seat']; ?></p>
                            <p><strong>Amount Paid:</strong> KSH <?php echo $booking['booked_amount']; ?></p>
                        </div>

                        <div class="mt-4">
                            <a href="../assets/partials/_download.php?pnr=<?php echo $booking['booking_id']; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-download"></i> Download Ticket
                            </a>
                            <a href="my_bookings.php" class="btn btn-primary">
                                <i class="fas fa-list"></i> View All Bookings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    }
    else {
        header("location: book_ticket.php");
        exit();
    }
?> 