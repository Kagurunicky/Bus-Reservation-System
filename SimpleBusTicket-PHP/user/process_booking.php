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
        $selected_seat = $_POST['selected_seat'];
        $total_amount = $_POST['total_amount'];
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $phone = $_POST['phone'];

        // Create customer name
        $customer_name = $firstName . " " . $lastName;

        // Check if customer exists
        $customer_id = exist_customers($conn, $customer_name, $phone);
        
        if(!$customer_id) {
            // Create new customer
            $sql = "INSERT INTO customers (customer_name, customer_phone) VALUES ('$customer_name', '$phone')";
            if(mysqli_query($conn, $sql)) {
                $autoInc_id = mysqli_insert_id($conn);
                $customer_id = "CUST-" . $autoInc_id;
                
                // Update customer_id
                $sql = "UPDATE customers SET customer_id = '$customer_id' WHERE id = $autoInc_id";
                mysqli_query($conn, $sql);
            }
        }

        // Get route details
        $sql = "SELECT * FROM routes WHERE route_id = '$route_id'";
        $result = mysqli_query($conn, $sql);
        $route = mysqli_fetch_assoc($result);
        $customer_route = $route['route_cities'];
        $bus_no = $route['bus_no'];

        // Create booking
        $booking_id = "BK" . time();
        $sql = "INSERT INTO bookings (booking_id, customer_id, route_id, customer_route, booked_amount, booked_seat) 
                VALUES ('$booking_id', '$customer_id', '$route_id', '$customer_route', $total_amount, '$selected_seat')";

        if(mysqli_query($conn, $sql)) {
            // Update seats table
            $sql = "SELECT seat_booked FROM seats WHERE bus_no = '$bus_no'";
            $result = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                $booked_seats = $row['seat_booked'];
                $new_booked_seats = $booked_seats ? $booked_seats . ',' . $selected_seat : $selected_seat;
                
                $sql = "UPDATE seats SET seat_booked = '$new_booked_seats' WHERE bus_no = '$bus_no'";
            } else {
                $sql = "INSERT INTO seats (bus_no, seat_booked) VALUES ('$bus_no', '$selected_seat')";
            }
            
            if(mysqli_query($conn, $sql)) {
                // Set success message and redirect to view ticket
                $_SESSION['success'] = "Booking successful! You can now download or print your ticket.";
                header("Location: view_ticket.php?booking_id=" . $booking_id);
                exit();
            }
        }

        // If something goes wrong
        $_SESSION['error'] = "Booking failed. Please try again.";
        header("Location: book_ticket.php");
        exit();
    }
?> 