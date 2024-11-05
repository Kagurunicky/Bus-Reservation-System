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

    $user_id = $_SESSION['user_id'];
    $page = 'dashboard';

    // Get user's booking statistics
    $sql = "SELECT 
            COUNT(*) as total_bookings,
            SUM(booked_amount) as total_spent,
            COUNT(CASE WHEN route_dep_date >= CURDATE() THEN 1 END) as upcoming_trips,
            COUNT(CASE WHEN route_dep_date < CURDATE() THEN 1 END) as completed_trips
            FROM bookings b 
            JOIN routes r ON b.route_id = r.route_id 
            JOIN customers c ON b.customer_id = c.customer_id 
            WHERE c.customer_name = (
                SELECT user_fullname FROM users WHERE user_id = $user_id
            )";
    
    $result = mysqli_query($conn, $sql);
    $stats = mysqli_fetch_assoc($result);

    // Get all user bookings
    $sql = "SELECT b.*, r.*, c.* 
            FROM bookings b 
            JOIN routes r ON b.route_id = r.route_id 
            JOIN customers c ON b.customer_id = c.customer_id 
            WHERE c.customer_name = (
                SELECT user_fullname FROM users WHERE user_id = $user_id
            )
            ORDER BY b.booking_created DESC";
    
    $bookings = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - KBTBS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        #sidebar {
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
        }
        #main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .booking-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .booking-card:hover {
            transform: translateY(-5px);
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <?php require 'user_sidebar.php'; ?>

    <div id="main-content">
        <div class="container-fluid">
            <h2 class="mb-4">Dashboard</h2>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card dashboard-card bg-primary text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Bookings</h5>
                            <h2><?php echo $stats['total_bookings']; ?></h2>
                            <i class="fas fa-ticket-alt fa-2x position-absolute end-0 bottom-0 mb-3 me-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card bg-success text-white">
                        <div class="card-body">
                            <h5 class="card-title">Total Spent</h5>
                            <h2>KSH <?php echo number_format($stats['total_spent']); ?></h2>
                            <i class="fas fa-money-bill-wave fa-2x position-absolute end-0 bottom-0 mb-3 me-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card bg-info text-white">
                        <div class="card-body">
                            <h5 class="card-title">Upcoming Trips</h5>
                            <h2><?php echo $stats['upcoming_trips']; ?></h2>
                            <i class="fas fa-calendar-alt fa-2x position-absolute end-0 bottom-0 mb-3 me-3"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card bg-warning text-white">
                        <div class="card-body">
                            <h5 class="card-title">Completed Trips</h5>
                            <h2><?php echo $stats['completed_trips']; ?></h2>
                            <i class="fas fa-check-circle fa-2x position-absolute end-0 bottom-0 mb-3 me-3"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bookings Section -->
            <div class="row mt-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">My Bookings</h5>
                            <a href="book_ticket.php" class="btn btn-light btn-sm">
                                <i class="fas fa-plus"></i> New Booking
                            </a>
                        </div>
                        <div class="card-body">
                            <?php if(mysqli_num_rows($bookings) > 0) { ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Booking ID</th>
                                                <th>Route</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Seat</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($booking = mysqli_fetch_assoc($bookings)) { ?>
                                                <tr>
                                                    <td><?php echo $booking['booking_id']; ?></td>
                                                    <td><?php echo $booking['route_cities']; ?></td>
                                                    <td><?php echo date('d M Y', strtotime($booking['route_dep_date'])); ?></td>
                                                    <td><?php echo $booking['route_dep_time']; ?></td>
                                                    <td><?php echo $booking['booked_seat']; ?></td>
                                                    <td>KSH <?php echo $booking['booked_amount']; ?></td>
                                                    <td>
                                                        <?php if(strtotime($booking['route_dep_date']) < strtotime('today')) { ?>
                                                            <span class="badge bg-secondary">Completed</span>
                                                        <?php } else { ?>
                                                            <span class="badge bg-success">Upcoming</span>
                                                        <?php } ?>
                                                    </td>
                                                    <td>
                                                        <!-- View Ticket Button -->
                                                        <button onclick="viewTicket('<?php echo $booking['booking_id']; ?>')" 
                                                                class="btn btn-info btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        
                                                        <!-- Download Button -->
                                                        <a href="download_ticket.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                                                           class="btn btn-success btn-sm">
                                                            <i class="fas fa-download"></i>
                                                        </a>

                                                        <!-- Print Button -->
                                                        <button onclick="printTicket('<?php echo $booking['booking_id']; ?>')"
                                                                class="btn btn-primary btn-sm">
                                                            <i class="fas fa-print"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="text-center py-4">
                                    <h4>No bookings found</h4>
                                    <p>You haven't made any bookings yet.</p>
                                    <a href="book_ticket.php" class="btn btn-primary">
                                        <i class="fas fa-ticket-alt"></i> Book Now
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Modal -->
    <div class="modal fade" id="ticketModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ticket Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="ticketContent">
                    <!-- Ticket content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewTicket(bookingId) {
            // Load ticket content into modal
            fetch('view_ticket.php?booking_id=' + bookingId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('ticketContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('ticketModal')).show();
                });
        }

        function printTicket(bookingId) {
            // Open ticket in new window for printing
            window.open('view_ticket.php?booking_id=' + bookingId + '&print=true', '_blank');
        }
    </script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>