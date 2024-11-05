<!-- Show these admin pages only when the admin is logged in -->
 <!--this is the routes and it contains the bus no and the price for each route -->
<?php  require '../assets/partials/_admin-check.php';   ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routes</title>
        <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- CSS -->
    <?php 
        require '../assets/styles/admin.php';
        require '../assets/styles/admin-options.php';
        $page="route";
    ?>
</head>
<body>
    <!-- Requiring the admin header files -->
    <?php require '../assets/partials/_admin-header.php';?>

    <!-- Add, Edit and Delete Routes -->
    <?php
        /*
            1. Check if an admin is logged in
            2. Check if the request method is POST
        */
        if($loggedIn && $_SERVER["REQUEST_METHOD"] == "POST")
        {
            if(isset($_POST["submit"]))
            {
                /*
                    ADDING ROUTES
                 Check if the $_POST key 'submit' exists
                */
                // Should be validated client-side
                $viaCities = strtoupper($_POST["viaCities"]);
                $cost = $_POST["stepCost"];
                $deptime = $_POST["dep_time"];
                $depdate = $_POST["dep_date"];
                $busno = $_POST["busno"];
                $route_exists = exist_routes($conn,$viaCities,$depdate, $deptime);
                $route_added = false;
        
                if(!$route_exists)
                {
                    // Route is unique, proceed
                    $sql = "INSERT INTO `routes` (`route_cities`,
                     `bus_no`, 
                     `route_dep_date`,
                     `route_dep_time`, `route_step_cost`, `route_created`) VALUES ('$viaCities','$busno', '$depdate','$deptime', '$cost', current_timestamp());";
                    $result = mysqli_query($conn, $sql);
                    
                    // Gives back the Auto Increment id
                    $autoInc_id = mysqli_insert_id($conn);
                    // If the id exists then, 
                    if($autoInc_id)
                    {
                        $code = rand(1,99999);
                        // Generates the unique userid
                        $route_id = "RT-".$code.$autoInc_id;
                        
                        $query = "UPDATE `routes` SET `route_id` = '$route_id' WHERE `routes`.`id` = $autoInc_id;";
                        $queryResult = mysqli_query($conn, $query);
                        if(!$queryResult)
                            echo "Not Working";
                    }
                    
                    if($result)
                    {
                        $route_added = true;
                        // The bus is now assigned, updating uses table
                        bus_assign($conn, $busno);
                    }
                }
    
                if($route_added)
                {
                    // Show success alert
                    echo '<div class="my-0 alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Successful!</strong> Route Added
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                else{
                    
                    // Show error alert
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Route already exists
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
            }
            if(isset($_POST["edit"]))
            {
                // EDIT ROUTES
                $viaCities = strtoupper($_POST["viaCities"]);
                $cost = $_POST["stepCost"];
                $id = $_POST["id"];
                $deptime = $_POST["dep_time"];
                $depdate = $_POST["dep_date"];
                $busno = $_POST["busno"];
                $oldBusNo = $_POST["old-busno"];

                $id_if_route_exists = exist_routes($conn,$viaCities,$depdate,$deptime);
           
                if(!$id_if_route_exists || $id == $id_if_route_exists)
                {
                    $updateSql = "UPDATE `routes` SET
                    `route_cities` = '$viaCities',
                    `bus_no`='$busno',
                    `route_dep_date` = '$depdate',
                    `route_dep_time` = '$deptime',
                    `route_step_cost` = 'KSHcost' WHERE `routes`.`id` = '$id';";
            
                    $updateResult = mysqli_query($conn, $updateSql);
                    $rowsAffected = mysqli_affected_rows($conn);
                    
                    $messageStatus = "danger";
                    $messageInfo = "";
                    $messageHeading = "Error!";
    
                    if(!$rowsAffected)
                    {
                        $messageInfo = "No Edits Administered!";
                    }
    
                    elseif($updateResult)
                    {
                        // To assign the new bus, and free the old one - this should only reun when the bus no is edited.
                        if($oldBusNo != $busno)
                        {
                            bus_assign($conn,$busno);
                            bus_free($conn, $oldBusNo);
                        }
                        // Show success alert
                        $messageStatus = "success";
                        $messageHeading = "Successfull!";
                        $messageInfo = "Route details Edited";
                    }
                    else{
                        // Show error alert
                        $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                    }
                    
                    // MESSAGE
                    echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                    <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                else 
                {
                    // If route details already exists
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Route details already exists
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }

            }
            if(isset($_POST["delete"]))
            {
                // DELETE ROUTES
                $id = $_POST["id"];
                // Get the bus_no from route_id
                $busno_toFree = busno_from_routeid($conn, $id);
                // Delete the route with id => id
                $deleteSql = "DELETE FROM `routes` WHERE `routes`.`id` = $id";
                $deleteResult = mysqli_query($conn, $deleteSql);
                $rowsAffected = mysqli_affected_rows($conn);
                $messageStatus = "danger";
                $messageInfo = "";
                $messageHeading = "Error!";

                if(!$rowsAffected)
                {
                    $messageInfo = "Record Doesnt Exist";
                }

                elseif($deleteResult)
                {   
                    // echo $num;
                    // Show success alert
                    $messageStatus = "success";
                    $messageInfo = "Route Details deleted";
                    $messageHeading = "Successfull!";
                    // Free the bus assigned
                    bus_free($conn, $busno_toFree);
                }
                else{
                    // Show error alert
                    $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                }
                // Message
                echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
        ?>    
        <?php
            $resultSql = "SELECT * FROM `routes` ORDER BY route_created DESC";
                            
            $resultSqlResult = mysqli_query($conn, $resultSql);
            if(!mysqli_num_rows($resultSqlResult)){ ?>
                <!-- Routes are not present -->
                <div class="container mt-4">
                    <div id="noRoutes" class="alert alert-dark " role="alert">
                        <h1 class="alert-heading">No Routes Found!!</h1>
                        <p class="fw-light">Be the first person to add one!</p>
                        <hr>
                        <div id="addRouteAlert" class="alert alert-success" role="alert">
                                Click on <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">ADD <i class="fas fa-plus"></i></button> to add a route!
                        </div>
                    </div>
                </div>
            <?php }
            else { ?>
                <!-- Routes Are present -->
                <section id="route">
                    <div id="head">
                        <h4>Route Status</h4>
                    </div>
                    <div id="route-results">
                        <div>
                            <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">Add Route Details <i class="fas fa-plus"></i></button>
                        </div>
                        <table class="table table-hover table-bordered">
                            <thead>
                                <th>ID</th>
                                <th>Via Cities</th>
                                <th>Bus</th>
                                <th>Departure Date</th>
                                <th>Departure Time</th>
                                <th>Cost</th>
                                <th>Actions</th>
                            </thead>
                            <?php
                                while($row = mysqli_fetch_assoc($resultSqlResult))
                                {
                                        // echo "<pre>";
                                        // var_export($row);
                                        // echo "</pre>";
                                    $id = $row["id"];
                                    $route_id = $row["route_id"];
                                    $route_cities = $row["route_cities"];
                                    $route_dep_time = $row["route_dep_time"];
                                    $route_dep_date = $row["route_dep_date"];
                                    $route_step_cost = $row["route_step_cost"];
                                    $bus_no = $row["bus_no"];
                                        ?>
                                    <tr>
                                        <td>
                                            <?php 
                                                echo $route_id;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo $route_cities;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo $bus_no;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo $route_dep_date;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo $route_dep_time;
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                                echo 'KSH'.$route_step_cost;?>
                                        </td>
                                        <td>
                                            <button class="button edit-button " data-link="<?php echo $_SERVER['REQUEST_URI']; ?>" data-id="<?php 
                                                echo $id;?>" data-cities="<?php 
                                                echo $route_cities;?>" data-cost="<?php 
                                                echo $route_step_cost;?>" data-date="<?php 
                                                echo $route_dep_date;
                                            ?>" data-time="<?php 
                                            echo $route_dep_time;
                                            ?>" data-busno="<?php 
                                            echo $bus_no;
                                            ?>"
                                            >Edit</button>
                                            <button class="button delete-button" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="<?php 
                                                echo $id;?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php 
                                }
                            ?>
                        </table>
                    </div>
                    </section>
                <?php  }
            ?>
            </div>
    </main>
            <?php
                $busSql = "Select * from buses where bus_assigned=0";
                $resultBusSql = mysqli_query($conn, $busSql);
                $arr = array();
                while($row = mysqli_fetch_assoc($resultBusSql))
                    $arr[] = $row;
                $busJson = json_encode($arr);
            ?>
            <!-- Add Route Modal -->
            <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add A Route</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addRouteForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
                            <div class="mb-3">
                                    <label for="viaCities" class="form-label">Via Cities</label>
                                <input type="text" class="form-control" id="viaCities" name="viaCities" placeholder="Comma Separated List" required>
                                <span id="error">

                                </span>
                            </div>
                            <input type="hidden" id="busJson" name="busJson" value='<?php echo $busJson; ?>'>
                            <div class="mb-3">
                                <label for="busno" class="form-label">Bus Number</label>
                                <!-- Search Functionality -->
                                <div class="searchBus">
                                    <input type="text" class="form-control  busnoInput" id="busno" name="busno" required>
                                    <div class="sugg">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="stepCost" class="form-label">Cost</label>
                                <input type="number" class="form-control" id="stepCost" name="stepCost" required>
                            </div>
                            <div class="mb-3">
                                <label for="date" class="form-label">Departure Date</label>
                                <input type="date" name="dep_date" id="date" min="<?php 
                                date_default_timezone_set("Asia/Kolkata");
                                echo date("Y-m-d");?>" value="
                                <?php 
                                echo date("Y-m-d");
                                ?>
                                " required>
                            </div>
                            <div class="mb-3">
                                <label for="time" class="form-label">Departure Time</label>
                                <input type="time" name="dep_time" id="time" min="
                                <?php
                                    echo date("H:i");
                                ?>
                                " required>
                            </div>
                            <button type="submit" class="btn btn-success" name="submit">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <!-- Add Anything -->
                    </div>
                    </div>
                </div>
        </div>
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-circle"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h2 class="text-center pb-4">
                    Are you sure?
                </h2>
                <p>
                    Do you really want to delete this route? <strong>This process cannot be undone.</strong>
                </p>
                <!-- Needed to pass id -->
                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="delete-form"  method="POST">
                    <input id="delete-id" type="hidden" name="id">
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="delete-form" name="delete" class="btn btn-danger">Delete</button>
            </div>
            </div>
        </div>
        </div>
    <!-- External JS -->
    <script src="../assets/scripts/admin_routes.js"></script>
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
</html> 
<!-- this is the booking module -->
 <!-- Show these admin pages only when the admin is logged in -->
<?php  require '../assets/partials/_admin-check.php';   ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings</title>
        <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <!-- External CSS -->
    <?php
        require '../assets/styles/admin.php';
        require '../assets/styles/admin-options.php';
        $page="booking";
    ?>
</head>
<body>
    <!-- Requiring the admin header files -->
    <?php require '../assets/partials/_admin-header.php';?>
<!-- Add, Edit and Delete Bookings -->
<?php
        /*
            1. Check if an admin is logged in
            2. Check if the request method is POST
        */
        if($loggedIn && $_SERVER["REQUEST_METHOD"] == "POST")
        {
            if(isset($_POST["submit"]))
            {
                /*
                    ADDING Bookings
                 Check if the $_POST key 'submit' exists
                */
                // Should be validated client-side
                // echo "<pre>";
                // var_export($_POST);
                // echo "</pre>";
                // die;
                $customer_id = $_POST["cid"];
                $customer_name = $_POST["cname"];
                $customer_phone = $_POST["cphone"];
                $route_id = $_POST["route_id"];
                $route_source = $_POST["sourceSearch"];
                $route_destination = $_POST["destinationSearch"];
                $route = $route_source . " &rarr; " . $route_destination;
                $booked_seat = $_POST["seatInput"];
                $amount = $_POST["bookAmount"];
                // $dep_timing = $_POST["dep_timing"];

                $booking_exists = exist_booking($conn,$customer_id,$route_id);
                $booking_added = false;
        
                if(!$booking_exists)
                {
                    // Route is unique, proceed
                    $sql = "INSERT INTO `bookings` (`customer_id`, `route_id`, `customer_route`, `booked_amount`, `booked_seat`, `booking_created`) VALUES ('$customer_id', '$route_id','$route', '$amount', '$booked_seat', current_timestamp());";

                    $result = mysqli_query($conn, $sql);
                    // Gives back the Auto Increment id
                    $autoInc_id = mysqli_insert_id($conn);
                    // If the id exists then, 
                    if($autoInc_id)
                    {
                        $key = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
                        $code = "";
                        for($i = 0; $i < 5; ++$i)
                            $code .= $key[rand(0,strlen($key) - 1)];
                        
                        // Generates the unique bookingid
                        $booking_id = $code.$autoInc_id;
                        
                        $query = "UPDATE `bookings` SET `booking_id` = '$booking_id' WHERE `bookings`.`id` = $autoInc_id;";
                        $queryResult = mysqli_query($conn, $query);

                        if(!$queryResult)
                            echo "Not Working";
                    }

                    if($result)
                        $booking_added = true;
                }
    
                if($booking_added)
                {
                    // Show success alert
                    echo '<div class="my-0 alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Successful!</strong> Booking Added
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';

                    // Update the Seats table
                    $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
                    $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");
                    if($seats)
                    {
                        $seats .= "," . $booked_seat;
                    }
                    else 
                        $seats = $booked_seat;

                    $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
                    mysqli_query($conn, $updateSeatSql);
                }
                else{
                    // Show error alert
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Booking already exists
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
            }
            if(isset($_POST["edit"]))
            {
                // EDIT BOOKING
                // echo "<pre>";
                // var_export($_POST);
                // echo "</pre>";die;
                $cname = $_POST["cname"];
                $cphone = $_POST["cphone"];
                $id = $_POST["id"];
                $customer_id = $_POST["customer_id"];
                $id_if_customer_exists = exist_customers($conn,$cname,$cphone);

                if(!$id_if_customer_exists || $customer_id == $id_if_customer_exists)
                {
                    $updateSql = "UPDATE `customers` SET
                    `customer_name` = '$cname',
                    `customer_phone` = '$cphone' WHERE `customers`.`customer_id` = '$customer_id';";

                    $updateResult = mysqli_query($conn, $updateSql);
                    $rowsAffected = mysqli_affected_rows($conn);
    
                    $messageStatus = "danger";
                    $messageInfo = "";
                    $messageHeading = "Error!";
    
                    if(!$rowsAffected)
                    {
                        $messageInfo = "No Edits Administered!";
                    }
    
                    elseif($updateResult)
                    {
                        // Show success alert
                        $messageStatus = "success";
                        $messageHeading = "Successfull!";
                        $messageInfo = "Customer details Edited";
                    }
                    else{
                        // Show error alert
                        $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                    }
    
                    // MESSAGE
                    echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                    <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }
                else{
                    // If customer details already exists
                    echo '<div class="my-0 alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong> Customer already exists
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
                }

            }
            if(isset($_POST["delete"]))
            {
                // DELETE BOOKING
                $id = $_POST["id"];
                $route_id = $_POST["route_id"];
                // Delete the booking with id => id
                $deleteSql = "DELETE FROM `bookings` WHERE `bookings`.`id` = $id";

                $deleteResult = mysqli_query($conn, $deleteSql);
                $rowsAffected = mysqli_affected_rows($conn);
                $messageStatus = "danger";
                $messageInfo = "";
                $messageHeading = "Error!";

                if(!$rowsAffected)
                {
                    $messageInfo = "Record Doesn't Exist";
                }

                elseif($deleteResult)
                {   
                    $messageStatus = "success";
                    $messageInfo = "Booking Details deleted";
                    $messageHeading = "Successfull!";

                    // Update the Seats table
                    $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
                    $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");

                    // Extract the seat no. that needs to be deleted
                    $booked_seat = $_POST["booked_seat"];

                    $seats = explode(",", $seats);
                    $idx = array_search($booked_seat, $seats);
                    array_splice($seats,$idx,1);
                    $seats = implode(",", $seats);

                    $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
                    mysqli_query($conn, $updateSeatSql);
                }
                else{

                    $messageInfo = "Your request could not be processed due to technical Issues from our part. We regret the inconvenience caused";
                }

                // Message
                echo '<div class="my-0 alert alert-'.$messageStatus.' alert-dismissible fade show" role="alert">
                <strong>'.$messageHeading.'</strong> '.$messageInfo.'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>';
            }
        }
        ?>
        <?php
            $resultSql = "SELECT * FROM `bookings` ORDER BY booking_created DESC";
                            
            $resultSqlResult = mysqli_query($conn, $resultSql);

            if(!mysqli_num_rows($resultSqlResult)){ ?>
                <!-- Bookings are not present -->
                <div class="container mt-4">
                    <div id="noCustomers" class="alert alert-dark " role="alert">
                        <h1 class="alert-heading">No Bookings Found!!</h1>
                        <p class="fw-light">Be the first person to add one!</p>
                        <hr>
                        <div id="addCustomerAlert" class="alert alert-success" role="alert">
                                Click on <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">ADD <i class="fas fa-plus"></i></button> to add a booking!
                        </div>
                    </div>
                </div>
            <?php }
            else { ?>   
            <section id="booking">
                <div id="head">
                    <h4>Booking Status</h4>
                </div>
                <div id="booking-results">
                    <div>
                        <button id="add-button" class="button btn-sm"type="button"data-bs-toggle="modal" data-bs-target="#addModal">Add Bookings<i class="fas fa-plus"></i></button>
                    </div>
                    <table class="table table-hover table-bordered">
                        <thead>
                            <th>PNR</th>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Bus</th>
                            <th>Route</th>
                            <th>Seat</th>
                            <th>Amount</th>
                            <th>Departure</th>
                            <th>Booked</th>
                            <th>Actions</th>
                        </thead>
                        <?php
                            while($row = mysqli_fetch_assoc($resultSqlResult))
                            {
                                    // echo "<pre>";
                                    // var_export($row);
                                    // echo "</pre>";
                                $id = $row["id"];
                                $customer_id = $row["customer_id"];
                                $route_id = $row["route_id"];

                                $pnr = $row["booking_id"];

                                $customer_name = get_from_table($conn, "customers","customer_id", $customer_id, "customer_name");
                                
                                $customer_phone = get_from_table($conn,"customers","customer_id", $customer_id, "customer_phone");

                                $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");

                                $route = $row["customer_route"];

                                $booked_seat = $row["booked_seat"];
                                
                                $booked_amount = $row["booked_amount"];

                                $dep_date = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_date");

                                $dep_time = get_from_table($conn, "routes", "route_id", $route_id, "route_dep_time");

                                $booked_timing = $row["booking_created"];
                        ?>
                        <tr>
                            <td>
                                <?php 
                                    echo $pnr;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $customer_name;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $customer_phone;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $bus_no;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $route;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $booked_seat;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo 'KSH'.$booked_amount;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $dep_date . " , ". $dep_time;
                                ?>
                            </td>
                            <td>
                                <?php 
                                    echo $booked_timing;
                                ?>
                            </td>
                            <td>
                            <button class="button btn-sm edit-button" data-link="<?php echo $_SERVER['REQUEST_URI']; ?>" data-customerid="<?php 
                                                echo $customer_id;?>" data-id="<?php 
                                                echo $id;?>" data-name="<?php 
                                                echo $customer_name;?>" data-phone="<?php 
                                                echo $customer_phone;?>" >Edit</button>
                                <button class="button delete-button btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                                data-id="<?php 
                                                echo $id;?>" data-bookedseat="<?php 
                                                echo $booked_seat;
                                            ?>" data-routeid="<?php 
                                            echo $route_id;
                                        ?>"> Delete</button>
                            </td>
                        </tr>
                        <?php 
                        }
                    ?>
                    </table>
                </div>
            </section>
            <?php } ?> 
        </div>
    </main>
    <!-- Requiring _getJSON.php-->
    <!-- Will have access to variables 
        1. routeJson
        2. customerJson
        3. seatJson
        4. busJson
        5. adminJson
        6. bookingJSON
    -->
    <?php require '../assets/partials/_getJSON.php';?>
    
    <!-- All Modals Here -->
    <!-- Add Booking Modal -->
    <div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Make Bookings</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addBookingForm" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="POST">
                            <!-- Passing Route JSON -->
                            <input type="hidden" id="routeJson" name="routeJson" value='<?php echo $routeJson; ?>'>
                            <!-- Passing Customer JSON -->
                            <input type="hidden" id="customerJson" name="customerJson" value='<?php echo $customerJson; ?>'>
                            <!-- Passing Seat JSON -->
                            <input type="hidden" id="seatJson" name="seatJson" value='<?php echo $seatJson; ?>'>

                            <div class="mb-3">
                                <label for="cid" class="form-label">Customer ID</label>
                                <!-- Search Functionality -->
                                <div class="searchQuery">
                                    <input type="text" class="form-control searchInput" id="cid" name="cid">
                                    <div class="sugg">
                                        
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="cname" class="form-label">Customer Name</label>
                                <input type="text" class="form-control" id="cname" name="cname" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="cphone" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="cphone" name="cphone" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="routeSearch" class="form-label">Route</label>
                                <!-- Search Functionality -->
                                <div class="searchQuery">
                                    <input type="text" class="form-control searchInput" id="routeSearch" name="routeSearch">
                                    <div class="sugg">
                                    </div>
                                </div>
                            </div>
                            <!-- Send the route_id -->
                            <input type="hidden" name="route_id" id="route_id">
                            <!-- Send the departure timing too -->
                            <input type="hidden" name="dep_timing" id="dep_timing">

                          
                            <!-- Seats Diagram -->
                            <div class="mb-3">
                                <table id="seatsDiagram">
                                <tr>
                                    <td id="seat-1" data-name="1">1</td>
                                    <td id="seat-2" data-name="2">2</td>
                                    <td id="seat-3" data-name="3">3</td>
                                    <td id="seat-4" data-name="4">4</td>
                                    <td id="seat-5" data-name="5">5</td>
                                    <td id="seat-6" data-name="6">6</td>
                                    <td id="seat-7" data-name="7">7</td>
                                    <td id="seat-8" data-name="8">8</td>
                                    <td id="seat-9" data-name="9">9</td>
                                    <td id="seat-10" data-name="10">10</td>
                                </tr>
                                <tr>
                                    <td id="seat-11" data-name="11">11</td>
                                    <td id="seat-12" data-name="12">12</td>
                                    <td id="seat-131" data-name="13">13</td>
                                    <td id="seat-14" data-name="14">14</td>
                                    <td id="seat-15" data-name="15">15</td>
                                    <td id="seat-16" data-name="16">16</td>
                                    <td id="seat-17" data-name="17">17</td>
                                    <td id="seat-18" data-name="18">18</td>
                                    <td id="seat-19" data-name="19">19</td>
                                    <td id="seat-20" data-name="20">20</td>
                                </tr>
                                <tr>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                        <td class="space">&nbsp;</td>
                                </tr>
                                <tr>
                                    <td id="seat-21" data-name="21">21</td>
                                    <td id="seat-22" data-name="22">22</td>
                                    <td id="seat-23" data-name="23">23</td>
                                    <td id="seat-24" data-name="24">24</td>
                                    <td id="seat-25" data-name="25">25</td>
                                    <td id="seat-26" data-name="26">26</td>
                                    <td id="seat-27" data-name="27">27</td>
                                    <td class="space">&nbsp;</td>
                                    <td id="seat-28" data-name="28">28</td>
                                    <td id="seat-29" data-name="29">29</td>
                                </tr>
                                <tr>
                                    <td id="seat-30" data-name="30">30</td>
                                    <td id="seat-31" data-name="31">31</td>
                                    <td id="seat-32" data-name="32">32</td>
                                    <td id="seat-33" data-name="33">33</td>
                                    <td id="seat-34" data-name="34">34</td>
                                    <td id="seat-35" data-name="35">35</td>
                                    <td id="seat-36" data-name="36">36</td>
                                    <td class="space">&nbsp;</td>
                                    <td id="seat-37" data-name="37">37</td>
                                    <td id="seat-38" data-name="38">38</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="row g-3 align-items-center mb-3">
                                <div class="col-auto">
                                    <label for="seatInput" class="col-form-label">Seat Number</label>
                                </div>
                                <div class="col-auto">
                                    <input type="text" id="seatInput" class="form-control" name="seatInput" readonly>
                                </div>
                                <div class="col-auto">
                                    <span id="seatInfo" class="form-text">
                                    Select from the above figure, Maximum 1 seat.
                                    </span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="bookAmount" class="form-label">Total Amount</label>
                                <input type="text" class="form-control" id="bookAmount" name="bookAmount" readonly>
                            </div>
                            <button type="submit" class="btn btn-success" name="submit">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <!-- Add Anything -->
                    </div>
                    </div>
                </div>
        </div>
        <!-- Delete Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"><i class="fas fa-exclamation-circle"></i></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h2 class="text-center pb-4">
                    Are you sure?
                </h2>
                <p>
                    Do you really want to delete this booking? <strong>This process cannot be undone.</strong>
                </p>
                <!-- Needed to pass id -->
                <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" id="delete-form"  method="POST">
                    <input id="delete-id" type="hidden" name="id">
                    <input id="delete-booked-seat" type="hidden" name="booked_seat">
                    <input id="delete-route-id" type="hidden" name="route_id">
                </form>
            </div>
            <div class="modal-footer d-flex justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="delete-form" name="delete" class="btn btn-danger">Delete</button>
            </div>
            </div>
        </div>
    </div>
    <script src="../assets/scripts/admin_booking.js"></script>
    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</body>
</html>
<!--This handles the booking-->
<?php 
    require '_functions.php';
    $conn = db_connect();    

    if(!$conn) 
        die("Connection Failed");

    if(!$_SERVER["REQUEST_METHOD"] == "POST" || !isset($_POST["book"]))
    {
        header("location: index.php");
        exit;
    }

    // echo "<pre>";
    // var_export($_POST);
    // echo "</pre>";
    // die;

    $customer_name = ucfirst($_POST["firstName"]) . " " . ucfirst($_POST["lastName"]);
    $customer_phone = $_POST["phone"];
    $customer_seat = $_POST["seat_selected"];
    $route_id = $_POST["route_id"];
    $booked_amount = $_POST["booked_amount"];
    $customer_route = $_POST["source"] . " &rarr; " . $_POST["destination"];

    $id_customer_exists = exist_customers($conn, $customer_name, $customer_phone);
    $customer_added = false;

    if(!$id_customer_exists)
    {
        // Route is unique, proceed
        $sql = "INSERT INTO `customers` (`customer_name`, `customer_phone`, `customer_created`) VALUES ('$customer_name', '$customer_phone', current_timestamp());";

        $result = mysqli_query($conn, $sql);

        // Gives back the Auto Increment id
        $autoInc_id = mysqli_insert_id($conn);
        // If the id exists then, 
        if($autoInc_id)
        {
            $code = rand(1,99999);
            // Generates the unique userid
            $customer_id = "CUST-". $code . $autoInc_id;
                        
            $query = "UPDATE `customers` SET `customer_id` = '$customer_id' WHERE `customers`.`id` = $autoInc_id;";
            $queryResult = mysqli_query($conn, $query);

            if(!$queryResult)
                echo "Not Working";
        }

        if($result)
            $customer_added = true;
    }

    if($id_customer_exists)
        $customer_id = $id_customer_exists;
    
    // Now Book the seat
    $booking_exists = exist_booking($conn,$customer_id,$route_id);
    $booking_added = false;
    $booking_id = false;
    
    if(!$booking_exists)
    {
        // Route is unique, proceed
        $sql = "INSERT INTO `bookings` (`customer_id`, `route_id`, `customer_route`, `booked_amount`, `booked_seat`, `booking_created`) VALUES ('$customer_id', '$route_id','$customer_route', '$booked_amount', '$customer_seat', current_timestamp());";
        echo $sql;

        $result = mysqli_query($conn, $sql);
        // Gives back the Auto Increment id
        $autoInc_id = mysqli_insert_id($conn);
        // If the id exists then, 
        if($autoInc_id)
        {
            $key = "1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $code = "";
            for($i = 0; $i < 5; ++$i)
                $code .= $key[rand(0,strlen($key) - 1)];
                        
            // Generates the unique bookingid
            $booking_id = $code.$autoInc_id;
                        
            $query = "UPDATE `bookings` SET `booking_id` = '$booking_id' WHERE `bookings`.`id` = $autoInc_id;";
            
            $queryResult = mysqli_query($conn, $query);

            if(!$queryResult)
                echo "Not Working";
        }

        if($result)
            $booking_added = true;
    }
    
    // Update the Seats table
    if($booking_added)
    {
        $bus_no = get_from_table($conn, "routes", "route_id", $route_id, "bus_no");
        $seats = get_from_table($conn, "seats", "bus_no", $bus_no, "seat_booked");

        if($seats)
        {
            $seats .= "," . $customer_seat;
        }
        else 
            $seats = $customer_seat;

        $updateSeatSql = "UPDATE `seats` SET `seat_booked` = '$seats' WHERE `seats`.`bus_no` = '$bus_no';";
        mysqli_query($conn, $updateSeatSql);
    }


    header("location: /index.php?booking_added=$booking_added&pnr=$booking_id");
    exit();
?>