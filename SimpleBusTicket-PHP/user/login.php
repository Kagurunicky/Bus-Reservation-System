<?php
    session_start();
    require '../assets/partials/_functions.php';
    $conn = db_connect();    

    if(!$conn) 
        die("Connection Failed");

    if($_SERVER["REQUEST_METHOD"] == "POST")
    {
        $username = $_POST["username"];
        $password = $_POST["password"];

        // Verify login
        $sql = "SELECT * FROM users WHERE user_name='$username'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 1)
        {
            $row = mysqli_fetch_assoc($result);
            if(password_verify($password, $row['user_password']))
            {
                // Start session and store user data
                $_SESSION["user_id"] = $row['user_id'];
                $_SESSION["user"] = $username;
                $_SESSION["user_fullname"] = $row['user_fullname'];
                
                // Redirect to dashboard
                header("location: dashboard.php");
                exit();
            }
        }
        $login_error = "Invalid username or password";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KBTBS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            max-width: 400px;
            margin: 100px auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #007bff;
            color: white;
            text-align: center;
            border-radius: 10px 10px 0 0 !important;
            padding: 20px;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            border-radius: 5px;
            padding: 10px;
        }
        .btn-login {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h3>User Login</h3>
                </div>
                <div class="card-body">
                    <?php
                        if(isset($login_error)) {
                            echo '<div class="alert alert-danger">' . $login_error . '</div>';
                        }
                        if(isset($_GET["signup"]) && $_GET["signup"] == "success") {
                            echo '<div class="alert alert-success">Registration successful! Please login.</div>';
                        }
                    ?>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-login">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
                        <a href="../index.php">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>