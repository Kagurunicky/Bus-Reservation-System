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

        if($booking) {
            // Set headers for file download
            header('Content-Type: text/html');
            header('Content-Disposition: attachment; filename="KBTBS_ticket_'.$booking_id.'.html"');

            // Create ticket HTML content
            $ticket = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Bus Ticket - '.$booking_id.'</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        line-height: 1.6;
                        margin: 40px;
                    }
                    .ticket {
                        border: 2px solid #000;
                        padding: 20px;
                        max-width: 800px;
                        margin: 0 auto;
                    }
                    .ticket-header {
                        text-align: center;
                        border-bottom: 2px solid #000;
                        padding-bottom: 10px;
                        margin-bottom: 20px;
                    }
                    .ticket-content {
                        margin-bottom: 20px;
                    }
                    .info-row {
                        margin: 10px 0;
                    }
                    .label {
                        font-weight: bold;
                    }
                    .ticket-footer {
                        text-align: center;
                        border-top: 2px solid #000;
                        padding-top: 10px;
                        font-size: 14px;
                    }
                </style>
            </head>
            <body>
                <div class="ticket">
                    <div class="ticket-header">
                        <h1>KBTBS Bus Ticket</h1>
                        <p>Your journey, our priority</p>
                    </div>
                    
                    <div class="ticket-content">
                        <div class="info-row">
                            <span class="label">Booking ID:</span> '.$booking['booking_id'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Passenger Name:</span> '.$booking['customer_name'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Phone:</span> '.$booking['customer_phone'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Route:</span> '.$booking['route_cities'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Date:</span> '.$booking['route_dep_date'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Time:</span> '.$booking['route_dep_time'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Seat Number:</span> '.$booking['booked_seat'].'
                        </div>
                        <div class="info-row">
                            <span class="label">Amount Paid:</span> KSH '.$booking['booked_amount'].'
                        </div>
                    </div>

                    <div class="ticket-footer">
                        <p>Thank you for choosing KBTBS</p>
                        <p>Please carry this ticket for Boarding.</p>
                        <p>Please arrive at least 30 minutes before departure time.</p>
                        <p>NB:NO TICKET NO BOARDING .Terms and conditons apply</p>

                    </div>
                </div>
            </body>
            </html>';

            // Output the ticket
            echo $ticket;
        } else {
            die("Booking not found");
        }
    } else {
        die("No booking ID provided");
    }
?>