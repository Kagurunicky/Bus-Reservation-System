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

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $route_id = $_POST['route_id'];
        $seats = $_POST['seats'];
        $amount = $_POST['amount'];
        $transaction_id = isset($_POST['transaction_id']) ? $_POST['transaction_id'] : $_POST['reference_no'];

        // Get user details
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT * FROM users WHERE user_id='$user_id'";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        // Get route details
        $sql = "SELECT * FROM routes WHERE route_id='$route_id'";
        $result = mysqli_query($conn, $sql);
        $route = mysqli_fetch_assoc($result);

        // Create customer record if not exists
        $customer_id = exist_customers($conn, $user['user_fullname'], '');
        if(!$customer_id) {
            $sql = "INSERT INTO customers (customer_name) VALUES ('".$user['user_fullname']."')";
            mysqli_query($conn, $sql);
            $customer_id = "CUST-" . mysqli_insert_id($conn);
        }

        // Create booking
        $booking_id = "BK" . time();
        $sql = "INSERT INTO bookings (booking_id, customer_id, route_id, booked_amount, booked_seat, customer_route) 
                VALUES ('$booking_id', '$customer_id', '$route_id', $amount, '$seats', '".$route['route_cities']."')";
        
        if(mysqli_query($conn, $sql)) {
            // Update seats
            $sql = "UPDATE seats SET seat_booked = CONCAT(IFNULL(seat_booked,''), ',$seats') 
                    WHERE bus_no = '".$route['bus_no']."'";
            mysqli_query($conn, $sql);

            // Redirect to success page
            header("location: booking_success.php?booking_id=" . $booking_id);
            exit();
        }
        else {
            header("location: book_ticket.php?error=1");
            exit();
        }
    }
?> 