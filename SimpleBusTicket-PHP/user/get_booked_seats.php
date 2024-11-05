<?php
    require '../assets/partials/_functions.php';
    $conn = db_connect();    

    if(!$conn) 
        die("Connection Failed");

    if(isset($_GET['bus_no'])) {
        $bus_no = $_GET['bus_no'];
        
        $sql = "SELECT seat_booked FROM seats WHERE bus_no = '$bus_no'";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);

        echo json_encode([
            'booked_seats' => $row['seat_booked'] ?? ''
        ]);
    }
?> 