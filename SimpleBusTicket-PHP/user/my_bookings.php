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

    // Get user's bookings
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT b.*, r.*, c.* 
            FROM bookings b 
            JOIN routes r ON b.route_id = r.route_id 
            JOIN customers c ON b.customer_id = c.customer_id 
            WHERE c.customer_name = (
                SELECT user_fullname FROM users WHERE user_id = $user_id
            )
            ORDER BY b.booking_created DESC";
            
    $result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - KBTBS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php require 'user_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">My Bookings</h4>
                    </div>
                    <div class="card-body">
                        <?php if(mysqli_num_rows($result) == 0) { ?>
                            <div class="alert alert-info">
                                <h5>No Bookings Found</h5>
                                <p>You haven't made any bookings yet.</p>
                                <a href="book_ticket.php" class="btn btn-primary">
                                    <i class="fas fa-ticket-alt"></i> Book a Ticket
                                </a>
                            </div>
                        <?php } else { ?>
                            <div class="table-responsive">
                                <table id="bookingsTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Route</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Seats</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = mysqli_fetch_assoc($result)) { ?>
                                            <tr>
                                                <td><?php echo $row['booking_id']; ?></td>
                                                <td><?php echo $row['route_cities']; ?></td>
                                                <td><?php echo $row['route_dep_date']; ?></td>
                                                <td><?php echo $row['route_dep_time']; ?></td>
                                                <td><?php echo $row['booked_seat']; ?></td>
                                                <td>KSH <?php echo $row['booked_amount']; ?></td>
                                                <td>
                                                    <?php if(strtotime($row['route_dep_date']) < strtotime('today')) { ?>
                                                        <span class="badge bg-secondary">Completed</span>
                                                    <?php } else { ?>
                                                        <span class="badge bg-success">Active</span>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <a href="../assets/partials/_download.php?pnr=<?php echo $row['booking_id']; ?>" 
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <?php if(strtotime($row['route_dep_date']) > strtotime('today')) { ?>
                                                        <button class="btn btn-sm btn-danger" 
                                                                onclick="cancelBooking('<?php echo $row['booking_id']; ?>')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#bookingsTable').DataTable();
        });

        function cancelBooking(bookingId) {
            if(confirm('Are you sure you want to cancel this booking?')) {
                window.location.href = 'cancel_booking.php?id=' + bookingId;
            }
        }
    </script>
</body>
</html> 