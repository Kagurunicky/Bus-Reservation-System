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

    // Fetch routes and buses directly
    $routes = mysqli_query($conn, "SELECT * FROM routes");
    $buses = mysqli_query($conn, "SELECT * FROM buses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Ticket - KBTBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
    <style>
        #seatsDiagram {
            margin: 20px auto;
        }
        #seatsDiagram td {
            padding: 10px;
            text-align: center;
            border: 2px solid #4CAF50;
            border-radius: 5px;
            cursor: pointer;
        }
        .space {
            border: none !important;
            background: none !important;
        }
        .selected {
            background-color: #4CAF50;
            color: white;
        }
        .notAvailable {
            background-color: #ff6b6b;
            cursor: not-allowed;
            color: white;
        }
    </style>
</head>
<body>
    <?php require 'user_header.php'; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>Book Your Ticket</h4>
                    </div>
                    <div class="card-body">
                        <!-- Route Selection -->
                        <div class="mb-3">
                            <h5>Available Routes</h5>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Route</th>
                                            <th>Bus Number</th>
                                            <th>Departure Date</th>
                                            <th>Departure Time</th>
                                            <th>Cost</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($route = mysqli_fetch_assoc($routes)) { ?>
                                            <tr>
                                                <td><?php echo $route['route_cities']; ?></td>
                                                <td><?php echo $route['bus_no']; ?></td>
                                                <td><?php echo $route['route_dep_date']; ?></td>
                                                <td><?php echo $route['route_dep_time']; ?></td>
                                                <td>KSH <?php echo $route['route_step_cost']; ?></td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm select-route" 
                                                            data-route-id="<?php echo $route['route_id']; ?>"
                                                            data-bus-no="<?php echo $route['bus_no']; ?>"
                                                            data-cost="<?php echo $route['route_step_cost']; ?>">
                                                        Select Route
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Seat Selection -->
                        <div id="seatSelection" style="display: none;">
                            <h5>Select Your Seat</h5>
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
                                    <td id="seat-13" data-name="13">13</td>
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

                        <!-- Booking Form -->
                        <form id="bookingForm" action="process_booking.php" method="POST" style="display: none;">
                            <input type="hidden" name="route_id" id="selectedRouteId">
                            <input type="hidden" name="selected_seat" id="selectedSeat">
                            <input type="hidden" name="total_amount" id="totalAmount">

                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">First Name</label>
                                        <input type="text" class="form-control" name="firstName" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Last Name</label>
                                        <input type="text" class="form-control" name="lastName" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Payment Method</label>
                                        <select class="form-select" name="payment_method" required>
                                            <option value="mpesa">M-Pesa</option>
                                            <option value="bank">Bank Transfer</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <p><strong>Selected Seat:</strong> <span id="seatDisplay">None</span></p>
                                <p><strong>Total Amount:</strong> KSH <span id="amountDisplay">0</span></p>
                            </div>

                            <button type="submit" class="btn btn-primary">Proceed to Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Handle route selection
        document.querySelectorAll('.select-route').forEach(button => {
            button.addEventListener('click', async function() {
                const routeId = this.dataset.routeId;
                const busNo = this.dataset.busNo;
                const cost = this.dataset.cost;

                // Show seat selection
                document.getElementById('seatSelection').style.display = 'block';
                document.getElementById('selectedRouteId').value = routeId;

                // Reset seats
                document.querySelectorAll('#seatsDiagram td').forEach(td => {
                    if(td.className !== 'space') {
                        td.className = '';
                    }
                });

                // Get booked seats
                try {
                    const response = await fetch(`get_booked_seats.php?bus_no=${busNo}`);
                    const data = await response.json();
                    if(data.booked_seats) {
                        data.booked_seats.split(',').forEach(seatNo => {
                            const seat = document.getElementById(`seat-${seatNo}`);
                            if(seat) seat.className = 'notAvailable';
                        });
                    }
                } catch(error) {
                    console.error('Error:', error);
                }

                // Show booking form
                document.getElementById('bookingForm').style.display = 'block';
            });
        });

        // Handle seat selection
        let selectedSeat = null;
        document.getElementById('seatsDiagram').addEventListener('click', function(e) {
            if(!e.target.matches('td') || e.target.className === 'space' || e.target.className === 'notAvailable') 
                return;

            const seatNo = e.target.dataset.name;
            
            // Unselect previously selected seat
            if(selectedSeat) {
                document.querySelector(`td[data-name="${selectedSeat}"]`).className = '';
            }

            // Select new seat
            if(selectedSeat !== seatNo) {
                e.target.className = 'selected';
                selectedSeat = seatNo;
            } else {
                selectedSeat = null;
            }

            // Update form
            document.getElementById('selectedSeat').value = selectedSeat;
            document.getElementById('seatDisplay').textContent = selectedSeat || 'None';
            const cost = document.querySelector('.select-route[data-route-id="' + document.getElementById('selectedRouteId').value + '"]').dataset.cost;
            document.getElementById('totalAmount').value = selectedSeat ? cost : 0;
            document.getElementById('amountDisplay').textContent = selectedSeat ? cost : 0;
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>