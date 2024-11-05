<?php
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
?>

<div id="sidebar" class="bg-dark text-white">
    <div class="p-4">
        <div class="text-center mb-4">
            <img src="../assets/img/user-avatar.png" class="rounded-circle" width="100" alt="User Avatar">
            <h5 class="mt-2"><?php echo $user['user_fullname']; ?></h5>
            <p class="text-muted">@<?php echo $user['user_name']; ?></p>
        </div>
        <hr>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php if($page=='dashboard') echo 'active'; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php if($page=='book_ticket') echo 'active'; ?>" href="book_ticket.php">
                    <i class="fas fa-ticket-alt"></i> Book Ticket
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php if($page=='my_bookings') echo 'active'; ?>" href="view_ticket.php">
                    <i class="fas fa-list"></i> My Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white <?php if($page=='profile') echo 'active'; ?>" href="profile.php">
                    <i class="fas fa-user"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="../assets/partials/_logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div> 