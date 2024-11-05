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

    if(isset($_GET['id'])) {
        $booking_id = $_GET['id'];
        
        // Get booking details first
        $sql = "SELECT b.*, r.bus_no 
                FROM bookings b 
                JOIN routes r ON b.route_id = r.route_id 
                WHERE b.booking_id = '$booking_id'";
        $result = mysqli_query($conn, $sql);
        $booking = mysqli_fetch_assoc($result);

        if($booking) {
            // Remove the booked seat from seats table
            $bus_no = $booking['bus_no'];
            $booked_seat = $booking['booked_seat'];

            $sql = "SELECT seat_booked FROM seats WHERE bus_no = '$bus_no'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);
            
            $seats = explode(',', $row['seat_booked']);
            $seats = array_diff($seats, [$booked_seat]);
            $new_seats = implode(',', $seats);

            // Update seats table
            $sql = "UPDATE seats SET seat_booked = '$new_seats' WHERE bus_no = '$bus_no'";
            mysqli_query($conn, $sql);

            // Delete the booking
            $sql = "DELETE FROM bookings WHERE booking_id = '$booking_id'";
            if(mysqli_query($conn, $sql)) {
                header("location: my_bookings.php?msg=cancelled");
                exit();
            }
        }
    }
    
    header("location: my_bookings.php?error=1");
    exit();
?> 