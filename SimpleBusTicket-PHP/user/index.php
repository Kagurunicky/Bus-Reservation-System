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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - KBTBS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php require 'user_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="jumbotron">
                    <h1 class="display-4">Welcome to KBTBS</h1>
                    <p class="lead">Book your bus tickets easily and conveniently.</p>
                    <hr class="my-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <i class="fas fa-ticket-alt fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">Book Tickets</h5>
                                    <p class="card-text">Book your bus tickets for any route.</p>
                                    <a href="book_ticket.php" class="btn btn-primary">Book Now</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <i class="fas fa-history fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">Booking History</h5>
                                    <p class="card-text">View your booking history.</p>
                                    <a href="dashboard.php" class="btn btn-primary">View History</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-body text-center">
                                    <i class="fas fa-user fa-3x mb-3 text-primary"></i>
                                    <h5 class="card-title">Profile</h5>
                                    <p class="card-text">Manage your profile settings.</p>
                                    <a href="profile.php" class="btn btn-primary">View Profile</a>
                                </div>
                            </div>
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