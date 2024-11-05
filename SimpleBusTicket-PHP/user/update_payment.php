<?php
    require '../assets/partials/_functions.php';
    $conn = db_connect();    

    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    if(isset($data['booking_id']) && isset($data['status'])) {
        $booking_id = $data['booking_id'];
        $status = $data['status'];
        $transaction_id = $data['transaction_id'];

        // Update booking status
        $sql = "UPDATE bookings SET 
                payment_status = '$status',
                transaction_id = '$transaction_id',
                payment_date = CURRENT_TIMESTAMP 
                WHERE booking_id = '$booking_id'";

        if(mysqli_query($conn, $sql)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => mysqli_error($conn)]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Missing required data']);
    }
?> 