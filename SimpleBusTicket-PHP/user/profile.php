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

    $user_id = $_SESSION['user_id'];
    
    // Handle profile update
    if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
        $firstName = $_POST['firstName'];
        $lastName = $_POST['lastName'];
        $fullName = $firstName . " " . $lastName;
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];

        // Verify current password
        $sql = "SELECT user_password FROM users WHERE user_id = $user_id";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);

        if(password_verify($currentPassword, $user['user_password'])) {
            if($newPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $sql = "UPDATE users SET user_fullname = '$fullName', user_password = '$hashedPassword' 
                        WHERE user_id = $user_id";
            } else {
                $sql = "UPDATE users SET user_fullname = '$fullName' WHERE user_id = $user_id";
            }

            if(mysqli_query($conn, $sql)) {
                $_SESSION['user_fullname'] = $fullName;
                $success = true;
            }
        } else {
            $error = "Current password is incorrect";
        }
    }

    // Get user details
    $sql = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);
    
    // Split full name into first and last name
    $nameParts = explode(" ", $user['user_fullname']);
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - KBTBS</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d8cfbe84b9.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php require 'user_header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">My Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success)) { ?>
                            <div class="alert alert-success">Profile updated successfully!</div>
                        <?php } ?>
                        <?php if(isset($error)) { ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php } ?>

                        <form method="POST" action="">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control" name="firstName" 
                                           value="<?php echo $firstName; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control" name="lastName" 
                                           value="<?php echo $lastName; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?php echo $user['user_name']; ?>" 
                                       readonly disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="currentPassword" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" name="newPassword">
                            </div>

                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 